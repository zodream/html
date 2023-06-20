<?php
declare(strict_types=1);
namespace Zodream\Html\Dark;

use Zodream\Helpers\Arr;
use Zodream\Helpers\Str;
use Zodream\Html\Form;
use Zodream\Infrastructure\Support\Html;

class Theme {

    public static function menu(array $data, array $option = []): string {
        $html = '';
        foreach ($data as $item) {
            if (empty($item)) {
                continue;
            }
            $html .= static::menuItem(...self::getMenuItem($item));
        }
        return Html::ul($html, $option);
    }

    public static function menuItem(?string $label, mixed $url = 'javascript:;',
                                    string $icon = '',
                                    array $children = [],
                                    bool $expand = false,
                                    bool $active = false,
                                    bool $toggle = true, array $options = []): string {
        if ($toggle === false) {
            return '';
        }
        $text = empty($icon) ? '' : sprintf('<i class="%s menu-icon"></i>', $icon);
        $text = Html::a(sprintf('%s<span class="menu-name">%s</span>%s', $text, $label,
            !empty($children) ? '<i class="menu-icon-arrow"></i>' : ''
        ), $url === false ? 'javascript:;' : $url);
        $class = $active ? 'active' : null;
        if (!empty($children)) {
            $text .= static::menu($children, ['class' => 'menu-children']);
            $class = $expand ? 'expand' : null;
        }
        $options['class'] = trim(sprintf('%s %s menu-item',
            $options['class'] ?? '', $class));
        return Html::li($text, $options);
    }

    protected static function getMenuItem(mixed $data): array {
        if (!is_array($data)) {
            return [$data, 'javascript:;', '', [], false, false, true];
        }
        if (!Arr::isAssoc($data)) {
            return array_replace([
                null,
                'javascript:;',
                '',
                [],
                false,
                false,
                true
            ], $data);
        }
        return [
            $data['label'],
            $data['url'] ?? 'javascript:;',
            $data['icon'] ?? '',
            isset($data['children']) && is_array($data['children']) ? $data['children'] : [],
            isset($data['expand']) && $data['expand'],
            isset($data['active']) && $data['active'],
            !isset($data['toggle']) || $data['toggle'],
            isset($data['class']) ? ['class' => $data['class']] : []
        ];
    }


    public static function text(string $name, mixed $value = '', string $label = '',
                                string $placeholder = '', bool $required = false): Input {
        return Input::text($name, $value)->label($label)->placeholder($placeholder)->required($required);
    }

    public static function email(string $name, mixed $value = '', string $label = '',
                                 string $placeholder = '', bool $required = false): Input {
        return Input::email($name, $value)->label($label)->placeholder($placeholder)->required($required);
    }

    public static function password(string $name, string $label = '',
                                    string $placeholder = '', bool $required = false): Input {
        return Input::password($name)->label($label)->placeholder($placeholder)->required($required);
    }

    public static function radio(string $name, array $data, mixed $selected = null, string $label = ''): Input {
        return Input::radio($name, $selected)->label($label)->items($data);
    }

    public static function checkbox(string $name, ?array $data, mixed $selected = null, string $label = ''): Input {
        return Input::checkbox($name, $selected)->label($label)->items($data);
    }

    public static function switch(string $name, mixed $value = 0, string $label = ''): Input {
        return Input::switch($name, $value)->label($label);
    }

    public static function select(string $name, array $data,
                                  null|int|string $selected = null, string $label = '', bool $required = false): Input {
        return Input::select($name, $selected)->label($label)->items($data)->required($required);
    }



    public static function file(string $name, mixed $value = '', string $label = '',
                                string $placeholder = '', bool $required = false): Input {
        return Input::file($name, $value)->label($label)->placeholder($placeholder)->required($required);
    }

    public static function textarea(string $name, mixed $value = '', string $label = '',
                                    string $placeholder = '', bool $required = false): Input {
        return Input::textarea($name, $value)->label($label)->placeholder($placeholder)->required($required);
    }

}