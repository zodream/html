<?php
namespace Zodream\Html\Dark;

use Zodream\Helpers\Arr;
use Zodream\Helpers\Str;
use Zodream\Html\Form;
use Zodream\Infrastructure\Support\Html;

class Theme {

    public static function menu(array $data) {
        $html = '';
        foreach ($data as $item) {
            $html .= static::menuItem(...self::getMenuItem($item));
        }
        return Html::ul($html);
    }

    public static function menuItem($label, $url = 'javascript:;', $icon = '',
                                    $children = [], $expand = false, $active = false) {
        $text = empty($icon) ? '' : sprintf('<i class="%s"></i>', $icon);
        $text = Html::a(sprintf('%s<span>%s</span>', $text, $label), $url === false ? 'javascript:;' : $url);
        $class = $active ? 'active' : null;
        if (!empty($children)) {
            $text .= static::menu($children);
            $class = $expand ? 'expand' : null;
        }
        return Html::li($text, compact('class'));
    }

    protected static function getMenuItem($data) {
        if (!is_array($data)) {
            return [$data, 'javascript:;', '', [], false, false];
        }
        if (!Arr::isAssoc($data)) {
            return array_replace([
                null,
                'javascript:;',
                '',
                [],
                false,
                false
            ], $data);
        }
        return [
            $data['label'],
            isset($data['url']) ? $data['url'] : 'javascript:;',
            isset($data['icon']) ? $data['icon'] : '',
            isset($data['children']) && !is_array($data['children']) ? $data['children'] : [],
            isset($data['expand']) && $data['expand'],
            isset($data['active']) && $data['active'],
        ];
    }


    public static function text($name, $value = '', $label = null,
                                $placeholder = null, $required = false) {
        return Input::text($name, $value)->label($label)->placeholder($placeholder)->required($required);
    }

    public static function email($name, $value = '', $label = null,
                                $placeholder = null, $required = false) {
        return Input::email($name, $value)->label($label)->placeholder($placeholder)->required($required);
    }

    public static function password($name, $label = null,
                                 $placeholder = null, $required = false) {
        return Input::password($name)->label($label)->placeholder($placeholder)->required($required);
    }

    public static function radio($name, $data, $selected = null, $label = null) {
        return Input::radio($name, $selected)->label($label)->items($data);
    }

    public static function checkbox($name, $data, $selected = null, $label = null) {
        return Input::checkbox($name, $selected)->label($label)->items($data);
    }

    public static function select($name, array $data, $selected = null, $label = null, $required = false) {
        return Input::select($name, $selected)->label($label)->items($data)->required($required);
    }



    public static function file($name, $value = '', $label = null,
                                 $placeholder = null, $required = false) {
        return Input::file($name, $value)->label($label)->placeholder($placeholder)->required($required);
    }

    public static function textarea($name, $value = '', $label = null,
                                    $placeholder = null, $required = false) {
        return Input::textarea($name, $value)->label($label)->placeholder($placeholder)->required($required);
    }

}