<?php
declare(strict_types=1);
namespace Zodream\Html;


use Zodream\Infrastructure\Contracts\Response\ExportObject;

class ShortCut implements ExportObject {
    /**
     * Excel constructor.
     * @param string $title
     * @param string $url
     */
    public function __construct(
        protected string $title,
        protected string $url) {
    }


    /**
     * 导出假的excel文件
     * 
     */
    public function send() {
        echo '[InternetShortcut] 
URL=',$this->url,'
IDList=
[{000214A0-0000-0000-C000-000000000046}]
Prop3=19,2';
    }

    /**
     * @return string
     */
    public function getName(): string {
        return $this->title .'.url';
    }

    /**
     * @return string
     */
    public function getType(): string {
        return 'exe';
    }
}