<?php
namespace Zodream\Html\Dark;

use Zodream\Helpers\Str;
use Zodream\Infrastructure\Support\Html;
use Zodream\Html\Form as BaseForm;

/**
 * Class Input
 * @package Zodream\Html\Dark
 * @method Input type($type, $name, $value)
 * @method Input text($name, $value)
 * @method Input url($name, $value)
 * @method Input email($name, $value)
 * @method Input password($name, $value)
 * @method Input textarea($name, $value)
 * @method Input file($name, $value)
 * @method Input id($id)
 * @method Input name($name)
 * @method Input value($value)
 * @method Input label($label)
 * @method Input items($items)
 * @method Input tip($tip)
 * @method Input options($options)
 * @method Input placeholder($placeholder)
 * @method Input required($required)
 * @method Input class($class)
 */
class Input {
    protected $id;

    protected $label;

    protected $tip;

    protected $type = 'text';

    protected $value;

    protected $options = [];

    protected $items;

    protected $boxClass;

    public function __construct($name = null, $value = null) {
        if (!empty($name)) {
            $this->setName($name);
        }
        if (!empty($value)) {
            $this->value = $value;
        }
    }

    public function setName($name) {
        $this->options['name'] = $name;
        if (empty($id)) {
            $this->id = str_replace(['[', ']', '#', '.'], ['_', '', '', ''], $name);
        }
        if (empty($this->label)) {
            $this->label = Str::studly($name);
        }
        return $this;
    }

    /**
     * @param mixed $label
     * @return Input
     * @throws \Exception
     */
    public function setLabel($label) {
        $this->label = $label;
        if (!isset($this->options['placeholder'])) {
            $this->options['placeholder'] = sprintf('%s %s', __('Please input'), $label);
        }
        return $this;
    }

    /**
     * @param mixed $boxClass
     * @return Input
     */
    public function setBoxClass($boxClass) {
        $this->boxClass = $boxClass;
        return $this;
    }

    /**
     * @param mixed $items
     * @return Input
     */
    public function setItems($items){
        if (!is_array($items)) {
            $this->items = $items;
            return $this;
        }
        if (isset($items[0]) && !is_numeric($items[0]) && !is_string($items[0])) {
            $items = self::getColumnsSource(...$items);
        }
        $this->items = $items;
        return $this;
    }

    /**
     * @param mixed $type
     * @param null $name
     * @param null $value
     * @return Input
     */
    public function setType($type, $name = null, $value = null) {
        $this->type = $type;
        if (!empty($name)) {
            $this->setName($name);
        }
        if (!empty($value)) {
            $this->value = $value;
        }
        return $this;
    }

    public function html() {
        $input = $this->encodeInput($this->type);
        $tip = $this->encodeTip();
        return <<<HTML
<div class="input-group">
    <label for="{$this->id}">{$this->label}</label>
    <div class="{$this->boxClass}">
        {$input}{$tip}
    </div>
</div>
HTML;

    }

    protected function encodeInput($type) {
        $options = $this->options;
        $options['class'] = sprintf('form-control %s', isset($options['class']) ? $options['class'] : '');
        if (!isset($options['id'])) {
            $options['id'] = $this->id;
        }
        $method = 'encode'.Str::studly($type);
        if (method_exists($this, $method)) {
            return $this->$method($options);
        }
        return BaseForm::input($type, $options['name'], $this->value, $options);
    }

    protected function encodeTextarea($options) {
        return Html::tag($this->type, $this->value, $options);
    }

    protected function encodeRadio($options) {
        $html =  '';
        $i = 0;
        foreach ($this->items as $key => $item) {
            $checked = $key == $this->value ? 'checked' : null;
            $id = $this->id.($i++);
            $html .= <<<HTML
<span class="radio-label">
    <input type="radio" id="{$id}" name="{$options['name']}" value="{$key}" {$checked}>
    <label for="{$id}">{$item}</label>
</span>
HTML;
        }
        return $html;
    }

    protected function encodeCheckbox($options) {
        if (!is_array($this->items)) {
            $this->boxClass = 'check-toggle';
            $id = $this->id.'_1';
            $value = empty($this->items) ? 1 : $this->items;
            $checked = $this->value == $value ? 'checked' : null;
            return <<<HTML
<input type="checkbox" id="{$id}" name="{$options['name']}" value="{$value}" {$checked}>
<label for="{$id}"></label>
HTML;
        }
        $html =  '';
        $i = 0;
        foreach ($this->items as $key => $item) {
            $checked = (!is_array($this->value) && $this->value == $key)
            || (is_array($this->value) && in_array($key, $this->value)) ? 'checked' : null;
            $id = $this->id.($i++);
            $html .= <<<HTML
<span class="check-label">
    <input type="checkbox" id="{$id}" name="{$options['name']}" value="{$key}" {$checked}>
    <label for="{$id}">{$item}</label>
</span>
HTML;
        }
        return $html;
    }

    protected function encodeSelect($options) {
        $html =  '';
        foreach ($this->items as $key => $item) {
            $html .= Html::tag('option', $item, array(
                'value' => $key,
                'selected' => $this->value == $key
            ));
        }
        return Html::tag(
            'select', $html, [
            'name' => $options['name'],
            'id' => $this->id,
            'required' => isset($options['required']) && $options['required']
        ]);
    }

    protected function encodeFile($options) {
        $this->setBoxClass('file-input');
        $upload = __('Upload');
        $preview =  __('Preview');
        return BaseForm::input('text', $options['name'], $this->value, $options). <<<HTML
<button type="button" data-type="upload">{$upload}</button>
<button type="button" data-type="preview">{$preview}</button>
HTML;
    }

    protected function encodeTip() {
        if (empty($this->tip)) {
            return '';
        }
        return '<div class="input-tip">'.$this->tip.'</div>';
    }

    public function __toString() {
        return $this->html();
    }


    public function __call($name, $arguments) {
        if (in_array($name, ['text', 'email', 'url', 'password', 'file', 'radio', 'checkbox', 'select', 'textarea'])) {
            return $this->setType($name, ...$arguments);
        }
        $method = 'set'.Str::studly($name);
        if (method_exists($this, $method)) {
            return $this->$method(...$arguments);
        }
        if (empty($arguments[0])) {
            return $this;
        }
        if (in_array($name, ['id', 'type', 'label', 'tip', 'items', 'value', 'options'])) {
            $this->$name = $arguments[0];
            return $this;
        }
        $this->options[$name] = $arguments[0];
        return $this;
    }

    public static function __callStatic($name, $arguments) {
        return (new static())->$name(...$arguments);
    }

    /**
     * 转化为键值对数组
     * @param array $data
     * @param string $value
     * @param string $key
     * @param array $prepend
     * @return array|mixed
     */
    protected static function getColumnsSource($data, $value = 'name', $key = 'id', array $prepend = []) {
        if (is_array($value)) {
            list($prepend, $value, $key) = [$value, 'name', 'id'];
        } elseif (is_array($key)) {
            list($prepend, $key) = [$key, 'id'];
        }
        if (empty($data)) {
            return $prepend;
        }
        foreach ($data as $item) {
            // 支持值作为键值
            if (is_numeric($item) || is_string($item)) {
                $prepend[$item] = $item;
                continue;
            }
            $prepend[$item[$key]] = $item[$value];
        }
        return $prepend;
    }
}