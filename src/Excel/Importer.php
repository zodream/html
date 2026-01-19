<?php
declare(strict_types=1);
namespace Zodream\Html\Excel;

use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Zodream\Infrastructure\Contracts\ArrayAble;

abstract class Importer implements ArrayAble {

    protected array $items = [];

    public function model(array $row): mixed {
        return $row;
    }

    /**
     * 标题行在第几行
     * @return int
     */
    public function headingRow(): int {
        return 1;
    }

    /**
     * 第几张表
     * @return int
     */
    public function sheetIndex(): int {
        return 0;
    }

    public function import(mixed $filePath, string $readerType = 'Xlsx'): void {
        $reader = $this->openFile($filePath, $readerType);
        $this->readSheet($reader);
    }

    protected function openFile(mixed $filePath, string $readerType = 'Xlsx'): Spreadsheet {
        $factory = IOFactory::createReader($readerType);
        $factory->setReadDataOnly(true);
        return $factory->load((string)$filePath);
    }

    protected function readSheet(Spreadsheet $reader): void {
        $sheet = $reader->getSheet($this->sheetIndex());
        $headerRow = $this->headingRow();
        $headers = [];
        if ($headerRow > 0) {
            $headers = $this->readRow($sheet, $headerRow);
        }
        $this->readRows($sheet, function (array $row) use ($headers) {
            $model = $this->model($this->combine($headers, $row));
            if (empty($model)) {
                return;
            }
            $this->items[] = $model;
        }, $headerRow + 1);
    }

    protected function readRows(Worksheet $sheet, callable $cb,
                              int $start = 1, int $end = 0, int $columnLength = 0): void {
        $count = $this->getMaxRow($sheet);
        if ($end < 1 || $count < $end) {
            $end = $count;
        }
        $count = $this->getMaxColumn($sheet);
        if ($columnLength < 1 || $columnLength < $count) {
            $columnLength = $count;
        }
        if ($columnLength === 0) {
            return;
        }
        for (; $start <= $end; $start ++) {
            call_user_func($cb, $this->readRow($sheet, $start, $columnLength));
        }
    }

    protected function readRow(Worksheet $sheet, int $row, int $columnLength = 0): array {
        if ($row <= 0) {
            return [];
        }
        if ($columnLength < 1) {
            $columnLength = $this->getMaxColumn($sheet);
        }
        $data = [];
        for ($i = 1; $i <= $columnLength; $i ++) {
            $cellName = Coordinate::stringFromColumnIndex($i);
            $columnName = $cellName.$row;
            $cell = $sheet->getCell($columnName);
            $data[] = $this->formatCell($cell, $row, $i, $columnName);
        }
        return $data;
    }

    protected function getMaxRow(Worksheet $sheet): int {
        return $sheet->getHighestDataRow();
    }

    protected function getMaxColumn(Worksheet $sheet): int {
        return Coordinate::columnIndexFromString($sheet->getHighestDataColumn());
    }

    protected function combine(array $keys, array $values): array {
        $keyCount = count($keys);
        if ($keyCount === 0) {
            return $values;
        }
        $valueCount = count($values);
        if ($keyCount === $valueCount) {
            return array_combine($keys, $values);
        }
        $data = [];
        for ($i = 0; $i < $keyCount; $i++) {
            $data[$keys[$i]] = $i >= $valueCount ? $values[$i] : '';
        }
        return $data;
    }

    /**
     * @param Cell|null $cell
     * @param int $row 从1开始
     * @param int $column 从1开始
     * @param string $name A1这种
     * @return string|null
     */
    protected function formatCell(Cell|null $cell, int $row, int $column, string $name): mixed {
        if (empty($cell)) {
            return null;
        }
        return $cell->getFormattedValue();
    }

    public function toArray(): array {
        return $this->items;
    }
}