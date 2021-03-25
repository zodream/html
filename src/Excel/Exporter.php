<?php
declare(strict_types=1);
namespace Zodream\Html\Excel;


use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Zodream\Database\Contracts\SqlBuilder;
use Zodream\Helpers\Arr;
use IteratorAggregate;
use Zodream\Infrastructure\Contracts\Response\ExportObject;

class Exporter implements ExportObject {

    protected string $type = 'Xlsx';

    public function __construct(
        protected $title = '',
        protected $header = [],
        protected $data = []) {
    }

    /**
     * @return string
     */
    public function getName(): string {
        return $this->title .'.'. $this->getType();
    }

    /**
     * @return string
     */
    public function getType(): string {
        return strtolower($this->type);
    }

    public function send()
    {
        $factory = IOFactory::createWriter($this->writer(), $this->type);
        $factory->save('php://output');
    }

    public function export($file, string $writerType = 'Xlsx') {
        $this->type = $writerType;
        $factory = IOFactory::createWriter($this->writer(), $writerType);
        $factory->save($file);
    }

    protected function writer() {
        $writer = new Spreadsheet();
        $sheet = $writer->setActiveSheetIndex(0);
        $row = 1;
        $row += $this->writeHeading($sheet, $row);
        $row += $this->writeItems($sheet, $row);
        $this->writeFooter($sheet, $row);
        return $writer;
    }

    protected function writeFooter(Worksheet $sheet, int $row) {

    }

    protected function eachItem(callable $cb) {
        if (is_array($this->data) || $this->data instanceof IteratorAggregate) {
            foreach ($this->data as $item) {
                call_user_func($cb, $item);
            }
            return;
        }
        if ($this->data instanceof SqlBuilder) {
            $this->data->each($cb);
        }
    }

    protected function writeItems(Worksheet $sheet, int $row): int {
        $i = $row;
        $headerKeys = empty($this->header) ? [] : array_keys($this->header);
        $this->eachItem(function ($item) use (&$i, &$headerKeys, $sheet) {
            $data = Arr::toArray($item);
            if (empty($data)) {
                return;
            }
            if (empty($headerKeys)) {
                $headerKeys = array_keys($data);
            }
            $j = 1;
            foreach ($headerKeys as $key) {
                $cellName = Coordinate::stringFromColumnIndex($j);
                $this->writeCell($sheet,
                    array_key_exists($key, $data) ? $data[$key] : '',
                    $i, $j, $cellName.$i);
                $j ++;
            }
            $i ++;
        });
        return $i - $row;
    }

    protected function writeCell(Worksheet $sheet, $value, int $row, int $column, string $name) {
        $sheet->setCellValueExplicit($name,
            $value, DataType::TYPE_STRING);
    }

    protected function writeHeading(Worksheet $sheet, int $row = 1): int {
        if (empty($this->header)) {
            return 0;
        }
        $i = 1;
        foreach ($this->header as $key => $title) {
            $cellName = Coordinate::stringFromColumnIndex($i);
            $this->writeCell($sheet, $title, $row, $i, $cellName.$row);
            $i ++;
        }
        return 1;
    }
}