<?php
declare(strict_types=1);
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
 * @method Input password($name)
 * @method Input textarea($name, $value)
 * @method Input file($name, $value)
 * @method Input switch($name, $value)
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
    protected string $id = '';

    protected string $label = '';

    protected string $tip = '';

    protected string $after = '';

    protected string $type = 'text';

    protected array $options = [];

    protected mixed $items = null;

    protected string $boxClass = '';

    public function __construct(
        ?string $name = null,
        protected mixed $value = null) {
        if (!empty($name)) {
            $this->setName($name);
        }
    }

    public function setName(string $name) {
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
    public function setLabel(string $label) {
        $this->label = $label;
        if (!isset($this->options['placeholder'])) {
            $this->options['placeholder'] = sprintf('%s %s', __('Please input'), strip_tags($label));
        }
        return $this;
    }

    /**
     * @param mixed $boxClass
     * @return Input
     */
    public function setBoxClass(string $boxClass) {
        $this->boxClass = $boxClass;
        return $this;
    }

    /**
     * @param mixed $items
     * @return Input
     */
    public function setItems(mixed $items){
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
    public function setType(string $type, ?string $name = null, mixed $value = null) {
        $this->type = $type;
        if (!empty($name)) {
            $this->setName($name);
        }
        if (!is_null($value)) {
            $this->value = $value;
        }
        return $this;
    }

    public function html(): string {
        $input = $this->encodeInput($this->type);
        $tip = $this->encodeTip();
        return <<<HTML
<div class="input-group">
    <label for="{$this->id}">{$this->label}</label>
    <div class="{$this->boxClass}">
        {$input}{$this->after}{$tip}
    </div>
</div>
HTML;

    }

    protected function encodeInput(string $type): string {
        $options = $this->options;
        $options['class'] = sprintf('form-control %s', $options['class'] ?? '');
        if (!isset($options['id'])) {
            $options['id'] = $this->id;
        }
        $method = 'encode'.Str::studly($type);
        if (method_exists($this, $method)) {
            return $this->$method($options);
        }
        return BaseForm::input($type, $options['name'], $this->value, $options);
    }

    protected function encodeTextarea(array $options): string {
        return Html::tag($this->type, $this->value, $options);
    }

    protected function encodeSwitch(array $options): string {
        $this->boxClass = 'check-toggle';
        $id = $this->id;
        $checked = (is_numeric($this->value) && $this->value == 1) ||
            (is_bool($this->value) && $this->value) || $this->value === 'true';
        $checkedAttr = $checked ? 'checked' : null;
        return <<<HTML
<input type="checkbox" id="{$id}" {$checkedAttr} onchange="$(this).next().next().val(this.checked ? 1 : 0);">
<label for="{$id}"></label>
<input type="hidden" name="{$options['name']}" value="{$this->value}"/>
HTML;
    }

    protected function encodeRadio(array $options): string {
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

    protected function encodeCheckbox(array $options): string {
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

    protected function encodeSelect(array $options): string {
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

    protected function encodeFile(array $options): string {
        $this->setBoxClass('file-input');
        $upload = __('Upload');
        $preview =  __('Preview');
        $allow = isset($options['allow']) ? sprintf(' data-allow="%s"', $options['allow']) : '';
        unset($options['allow']);
        $html = <<<HTML
<button type="button" data-type="upload"{$allow}>{$upload}</button>
<button type="button" data-type="preview">{$preview}</button>
HTML;
        if (isset($options['dialog'])) {
            $options['dialog'] = $options['dialog'] === 'multiple' ? 'multiple' : 'single';
            $online =  __('Online');
            $html .= <<<HTML

<button type="button" data-type="images" data-mode="{$options['dialog']}">{$online}</button>
HTML;
        }
        return BaseForm::input('text', $options['name'], $this->value, $options). $html;
    }

    protected function encodeTip(): string {
        if (empty($this->tip)) {
            return '';
        }
        return '<div class="input-tip">'.$this->tip.'</div>';
    }

    public function __toString(): string {
        return $this->html();
    }


    public function __call(string $name, array $arguments): Input {
        if (in_array($name, ['text', 'email', 'url', 'password', 'file', 'radio', 'checkbox', 'select', 'textarea', 'switch'])) {
            return $this->setType($name, ...$arguments);
        }
        $method = 'set'.Str::studly($name);
        if (method_exists($this, $method)) {
            return $this->$method(...$arguments);
        }
        if (empty($arguments)) {
            return $this;
        }
        if (in_array($name, ['id', 'type', 'label', 'tip', 'items', 'value', 'after'])) {
            $this->$name = $arguments[0];
            return $this;
        }
        if ($name === 'options') {
            $this->options = array_merge($this->options, $arguments[0]);
            return $this;
        }
        $this->options[$name] = $arguments[0];
        return $this;
    }

    public static function __callStatic(string $name, array $arguments): Input {
        return (new static())->$name(...$arguments);
    }

    /**
     * 转化为键值对数组
     * @param array $data
     * @param string|array $value
     * @param string|array $key
     * @param array $prepend
     * @return array|mixed
     */
    protected static function getColumnsSource(array $data, string|array $value = 'name',
                                               string|array $key = 'id', array $prepend = []): mixed {
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