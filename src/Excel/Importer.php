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

    public function model(array $row) {
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

    public function import($filePath, string $readerType = 'Xlsx') {
        $factory = IOFactory::createReader($readerType);
        $factory->setReadDataOnly(true);
        $reader = $factory->load((string)$filePath);
        $this->eachSheet($reader);
    }

    protected function eachSheet(Spreadsheet $reader) {
        $sheet = $reader->getSheet($this->sheetIndex());
        $this->eachRow($sheet, function (array $row) {
            $model = $this->model($row);
            if (empty($model)) {
                return;
            }
            $this->items[] = $model;
        }, $this->headingRow() + 1);
    }

    protected function eachRow(Worksheet $sheet, callable $cb,
                              $start = 1, $end = 0, $columnLength = 0) {
        $count = $sheet->getHighestRow();
        if ($end < 1 || $count < $end) {
            $end = $count;
        }
        $count = $sheet->getHighestColumn();
        if ($columnLength < 1 || $columnLength < $count) {
            $columnLength = $count;
        }
        for (; $start <= $end; $start ++) {
            $row = [];
            for ($i = 1; $i <= $columnLength; $i ++) {
                $cellName = Coordinate::stringFromColumnIndex($i);
                $columnName = $cellName.$start;
                $cell = $sheet->getCell($columnName);
                $row[] = $this->formatCell($cell, $start, $i, $columnName);
            }
            call_user_func($cb, $row);
        }
    }

    /**
     * @param Cell|null $cell
     * @param int $row 从1开始
     * @param int $column 从1开始
     * @param string $name A1这种
     * @return string|null
     */
    protected function formatCell(?Cell $cell, int $row, int $column, string $name) {
        if (empty($cell)) {
            return null;
        }
        return $cell->getFormattedValue();
    }

    public function toArray() {
        return $this->items;
    }
}