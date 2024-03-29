<?php
declare(strict_types=1);
namespace Zodream\Html\Bootstrap;
/**
 * Created by PhpStorm.
 * User: zx648
 * Date: 2016/6/3
 * Time: 21:04
 */
use Zodream\Html\Widget;
use Zodream\Infrastructure\Base\MagicObject;
use Zodream\Helpers\Str;

class DetailWidget extends Widget {

    protected array $default = array(
        'data' => null,
        'items' => array(
            //'id' => 'ID'
        ),
        'int' => false       //是否是以数字作为数组的键
    );
    
    protected function run(): string {
        $data = $this->get('data');
        if ($data instanceof MagicObject) {
            $data = $data->toArray();
        }
        $args = $this->get('items');
        if (empty($args) && empty($data)) {
            return '';
        }
        if (empty($args)) {
            $args = array_keys($data);
        }
        $isInt = $this->get('int');
        $content = '';
        foreach ($args as $key => $arg) {
            list($value, $tag) = Str::explode($arg, ':', 2);
            if (is_integer($key) && !$isInt) {
                $key = $value;
            }
            $content .= '<tr><td>'.$value.'</td><td>'.
                (array_key_exists($key, $data) ? $this->formatOne($data[$key], $tag) : null).'</td></tr>';
        }
        return Html::tag('table', $content, array(
            'class' => 'table table-hover'
        ));
    }
}