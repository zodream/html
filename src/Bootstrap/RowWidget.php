<?php
declare(strict_types=1);
namespace Zodream\Html\Bootstrap;
/**
 * Created by PhpStorm.
 * User: zx648
 * Date: 2016/6/3
 * Time: 21:08
 */
use Zodream\Html\Widget;

class RowWidget extends Widget {

    protected array $default = array(
        'size' => ['md'],
        'columns' => [
            //'2' => ''
        ]
    );

    protected function run(): string {
        $content = null;
        $size = $this->get('size');
        foreach ($this->get('columns', array()) as $key => $item) {
            $content .= Html::col($item, $key, $size);
        }
        return Html::row($content);
    }
}