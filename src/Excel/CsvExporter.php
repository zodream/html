<?php
declare(strict_types=1);
namespace Zodream\Html\Excel;


use IteratorAggregate;
use Zodream\Infrastructure\Contracts\Response\ExportObject;

class CsvExporter implements ExportObject {

    public string $charset = 'utf-8';
    
    protected array $firstRow;

    /**
     * Excel constructor.
     * @param $title     string
     * @param $firstRow  array
     *          如：array('name'=>'名字', 'title' => '标题') 键名与后面的数组$data的子元素键名关联
     * @param array|IteratorAggregate $data array
     */
    public function __construct(
        protected string $title = '', array $firstRow = [],
        protected array|IteratorAggregate $data = []) {
        if (empty($firstRow) && !empty($data)) {
            $firstRow = array_keys($data[0]);
        }
        $this->firstRow = $firstRow;
    }

    /**
     * 输出切换编码
     * @param string $output
     * @return string
     */
    public function excelExportIconv(string $output): string {
        return (string)iconv($this->charset, 'GBK', $output);
    }

    /**
     * 导出假的excel文件
     * 
     */
    public function send() {
        if (!empty($this->title)) {
            echo $this->excelExportIconv($this->title) . "\t\n";
        }
        //输出第一行内容
        foreach ($this->firstRow as $first) {
            echo $this->excelExportIconv($first) . "\t";
        }
        echo "\n";

        if (empty($this->data) || !is_array($this->data)) {
            return;
        }
        foreach ($this->data as $item) {
            foreach ($this->firstRow as $key => $val) {
                if (isset($item[$key])) {
                    echo $this->excelExportIconv($item[$key]) . "\t";
                } else {
                    echo $this->excelExportIconv('') . "\t";
                }
            }
            echo "\n";
        }
    }

    /**
     * @return string
     */
    public function getName(): string {
        return $this->title .'.xls';
    }

    /**
     * @return string
     */
    public function getType(): string {
        return 'xls';
    }
}