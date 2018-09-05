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
        $text = Html::a(sprintf('%s<span>%s</span>', $text, $label), $url);
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
        return self::inputType(__FUNCTION__, $name, $value, $label, $placeholder, $required);
    }

    public static function email($name, $value = '', $label = null,
                                $placeholder = null, $required = false) {
        return self::inputType(__FUNCTION__, $name, $value, $label, $placeholder, $required);
    }

    public static function password($name, $value = '', $label = null,
                                 $placeholder = null, $required = false) {
        return self::inputType(__FUNCTION__, $name, $value, $label, $placeholder, $required);
    }

    public static function radio($name, array $data, $selected = null, $label = null) {
        if (empty($label)) {
            $label = Str::studly($name);
        }

        $html =  '';
        foreach ($data as $key => $item) {
            $checked = $key == $selected ? 'checked' : null;
            $html .= <<<HTML
<label>
    <input value="{$key}" name="{$name}" type="radio" {$checked}> {$item}
</label>
HTML;
        }
        return self::input($label, $html, null);
    }

    public static function checkbox($name, array $data, $selected = null, $label = null) {
        if (empty($label)) {
            $label = Str::studly($name);
        }
        $html =  '';
        foreach ($data as $key => $item) {
            $checked = (!is_array($selected) && $selected == $key)
            || (is_array($selected) && in_array($key, $selected)) ? 'checked' : null;
            $html .= <<<HTML
<label>
    <input value="{$key}" name="{$name}" type="checkbox" {$checked}> {$item}
</label>
HTML;
        }
        return self::input($label, $html, null);
    }

    public static function select($name, array $data, $selected = null, $label = null, $required = false) {
        if (empty($label)) {
            $label = Str::studly($name);
        }
        $html =  '';
        if (isset($data[0]) && !is_numeric($data[0]) && !is_string($data[0])) {
            $data = self::getColumnsSource(...$data);
        }
        foreach ($data as $key => $item) {
            $html .= Html::tag('option', $item, array(
                'value' => $key,
                'selected' => $selected == $key
            ));
        }
        return self::input($label, Html::tag(
            'select', $html, [
                'name' => $name,
                'id' => $name,
                'required' => $required
        ]), $name);
    }

    protected static function getColumnsSource($data, $value = 'name', $key = 'id', array $prepend = []) {
        if (is_array($value)) {
            list($prepend, $value, $key) = [$value, 'name', 'id'];
        } elseif (is_array($key)) {
            list($prepend, $key) = [$key, 'id'];
        }
        if (empty($data)) {
            return $prepend;
        }
        $prepend = [];
        foreach ($data as $item) {
            $prepend[$item[$key]] = $item[$value];
        }
        return $prepend;
    }

    public static function file($name, $value = '', $label = null,
                                 $placeholder = null, $required = false) {
        list($label, $id, $input) = self::renderInput('text', $name, $value, $label, $placeholder, $required);
        $upload = __('Upload');
        $preview =  __('Preview');
        $input .= <<<HTML
<button type="button" data-type="upload">{$upload}</button>
<button type="button" data-type="preview">{$preview}</button>
HTML;
        return self::input($label, $input, $id, 'file-input');
    }

    public static function textarea($name, $value = '', $label = null,
                                    $placeholder = null, $required = false) {
        return self::inputType(__FUNCTION__, $name, $value, $label, $placeholder, $required);
    }

    public static function inputType($type, $name, $value = '', $label = null,
                                $placeholder = null, $required = false) {
        list($label, $id, $input) = self::renderInput($type, $name, $value, $label, $placeholder, $required);
        return self::input($label, $input, $id);
    }



    public static function input($label, $input, $id, $boxClass = null) {
        return <<<HTML
<div class="input-group">
    <label for="{$id}">{$label}</label>
    <div class="{$boxClass}">
        {$input}
    </div>
</div>
HTML;
    }

    /**
     * @param $type
     * @param $name
     * @param $value
     * @param $label
     * @param $placeholder
     * @param $required
     * @return array
     * @throws \Exception
     */
    public static function renderInput($type, $name, $value, $label, $placeholder, $required): array {
        if (empty($label)) {
            $label = Str::studly($name);
        }
        if (empty($placeholder)) {
            $placeholder = sprintf('%s %s', __('Please input'), $label);
        }
        $class = 'form-control';
        $id = $name;
        if (!is_bool($required)) {
            $value = empty($value) ? $required : $value;
            $required = true;
        }
        $input = $type == 'textarea' ? Html::tag($type, $value, compact('nam', 'placeholder', 'id', 'required', 'class'))
            : Form::input($type, $name, $value, compact('placeholder', 'id', 'required', 'class'));
        return array($label, $id, $input);
    }


}