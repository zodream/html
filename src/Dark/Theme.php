<?php
declare(strict_types=1);
namespace Zodream\Html\Dark;

use Zodream\Helpers\Arr;
use Zodream\Html\Input;
use Zodream\Html\InputHelper;
use Zodream\Html\Page;
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
                                    ?string $icon = '',
                                    ?array $children = [],
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

    /**
     * 显示页面提示
     * @param string $content
     * @param string $header
     * @return string
     */
    public static function tooltip(string $content, string $header = ''): string {
        if ($header === '') {
            $header = __('Operating tips');
        }
        $html = '';
        foreach (explode("\n", $content) as $item) {
            $item = trim($item);
            if ($item === '') {
                continue;
            }
            $html .= sprintf('<li>%s</li>', $item);
        }
        if (empty($html)) {
            return '';
        }
        return <<<HTML
<div class="page-tooltip-bar"><p class="tooltip-header">{$header}</p><ul>{$html}</ul><span class="tooltip-toggle"></span></div>
HTML;
    }

    /**
     * 空页面提示
     * @param mixed $toggle
     * @return string
     * @throws \Exception
     */
    public static function emptyTooltip(mixed $toggle): string {
        $isPage = $toggle instanceof Page;
        if (($isPage && !$toggle->isEmpty()) || (!$isPage && !empty($toggle))) {
            return '';
        }
        return sprintf('<div class="page-empty-tip">%s</div>', __('Nothing is here.'));
    }

    /**
     * 生成树状结构树
     * @param mixed $level
     * @return string
     */
    public static function treeLevel(mixed $level): string {
        if (empty($level) || $level < 1) {
            return '';
        }
        return sprintf('<span>ￂ%s</span>', str_repeat('ｰ', intval($level) - 1));
    }

    public static function text(string $name, mixed $value = '', string $label = '',
                                string $placeholder = '', bool $required = false): Input {
        return Input::text($name, $label, $required)->value($value)->placeholder($placeholder);
    }

    public static function email(string $name, mixed $value = '', string $label = '',
                                 string $placeholder = '', bool $required = false): Input {
        return Input::email($name, $label, $required)->value($value)->placeholder($placeholder);
    }

    public static function password(string $name, string $label = '',
                                    string $placeholder = '', bool $required = false): Input {
        return Input::password($name, $label, $required)->placeholder($placeholder);
    }

    public static function radio(string $name, array $data, mixed $selected = null, string $label = ''): Input {
        return Input::radio($name, $label, InputHelper::formatItems($data))->value($selected);
    }

    public static function checkbox(string $name, array|string|null $data, mixed $selected = null,
                                    string $label = ''): Input {
        if (!is_array($data)) {
            return static::switch($name, $selected, $label);
        }
        return Input::checkbox($name, $label, InputHelper::formatItems($data))->value($selected);
    }

    public static function switch(string $name, mixed $value = 0, string $label = ''): Input {
        return Input::switch($name, $label)->value($value);
    }

    public static function select(string $name, array $data,
                                  null|int|string $selected = null, string $label = '',
                                  bool $required = false): Input {
        return Input::select($name, $label, InputHelper::formatItems($data), $required)->value($selected);
    }



    public static function file(string $name, mixed $value = '', string $label = '',
                                string $placeholder = '', bool $required = false): Input {
        return Input::file($name, $label, $required)->value($value)->placeholder($placeholder);
    }

    public static function textarea(string $name, mixed $value = '', string $label = '',
                                    string $placeholder = '', bool $required = false): Input {
        return Input::textarea($name, $label, $required)->value($value)
            ->placeholder($placeholder);
    }

}