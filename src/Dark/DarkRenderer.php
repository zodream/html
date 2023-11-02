<?php
declare(strict_types=1);
namespace Zodream\Html\Dark;

use Zodream\Helpers\Arr;
use Zodream\Helpers\Str;
use Zodream\Html\Form as BaseForm;
use Zodream\Html\IHtmlRenderer;
use Zodream\Html\InputHelper;
use Zodream\Infrastructure\Contracts\ArrayAble;
use Zodream\Infrastructure\Support\Html;

class DarkRenderer implements IHtmlRenderer {

    public function render(array|ArrayAble $data): string {
        return '';
    }

    public function renderInput(array|ArrayAble $data): string {
        if ($data instanceof ArrayAble) {
            $data = $data->toArray();
        }
        if (empty($data['id'])) {
            $data['id'] = str_replace(['[', ']', '#', '.'], ['_', '', '', ''],
                sprintf('%s_%s', $data['name'], Str::quickRandom(3)));
        }
        $data['class'] = sprintf('form-control %s', $data['class'] ?? '');
        $data['type'] = $data['type'] ?? 'text';
        if (empty($data['placeholder'])) {
            $data['placeholder'] = sprintf('%s %s', __('Please input'), strip_tags($data['label']));
        }
        if (!empty($data['items']) && is_array($data['items']) &&
            !empty($data['items'][0]) && is_array($data['items'][0])) {
            $data['items'] = InputHelper::getColumnsSource($data['items'],
                isset($data['items'][0]['name']) ? 'name' : 'title',
                isset($data['items'][0]['id']) ? 'id' : 'value');
        }
        $method = 'encode'.Str::studly($data['type']);
        if (method_exists($this, $method)) {
            return $this->$method($data);
        }
        return $this->renderInputRow(
            BaseForm::input($data['type'], $data['name'], $data['value'] ?? '', $data),
            $data);
    }

    protected function renderInputRow(string $input, array $data): string {
        $tip = $this->encodeTip($data);
        $cls = !empty($data['boxClass']) ? sprintf(' class="%s"', $data['boxClass']) : '';
        $after = $data['after'] ?? '';
        return <<<HTML
<div class="input-group">
    <label for="{$data['id']}">{$data['label']}</label>
    <div{$cls}>
        {$input}{$after}{$tip}
    </div>
</div>
HTML;
    }

    protected function encodeTextarea(array $options): string {
        return $this->renderInputRow(
            Html::tag('textarea', $options['value'] ?? '', $options),
            $options
        );
    }

    protected function encodeSwitch(array $options): string {
        $options['boxClass'] = 'check-toggle';
        $checked = Str::toBool($options['value'] ?? false);
        $value = $checked ? 1 : 0;
        $checkedAttr = $checked ? 'checked' : null;
        return $this->renderInputRow(
            <<<HTML
<input type="checkbox" id="{$options['id']}" {$checkedAttr} onchange="$(this).next().next().val(this.checked ? 1 : 0);">
<label for="{$options['id']}"></label>
<input type="hidden" name="{$options['name']}" value="{$value}"/>
HTML,
            $options
        );
    }

    protected function encodeRadio(array $options): string {
        $html =  '';
        $i = 0;
        $value = $options['value'] ?? '';
        if (empty($options['items'])) {
            $options['items'] = [];
        }
        if (!isset($options['items'][$value])) {
            $value = key($options['items']);
        }
        foreach ($options['items'] as $key => $item) {
            $checked = $key == $value ? 'checked' : null;
            $id = $options['id'].($i++);
            $html .= <<<HTML
<span class="radio-label">
    <input type="radio" id="{$id}" name="{$options['name']}" value="{$key}" {$checked}>
    <label for="{$id}">{$item}</label>
</span>
HTML;
        }
        return $this->renderInputRow($html, $options);
    }

    protected function encodeCheckbox(array $options): string {
        if (!isset($options['items'])) {
            $options['items'] = null;
        }
        if (!is_array($options['items'])) {
            $options['boxClass'] = 'check-toggle';
            $id = $options['id'].'_1';
            $value = empty($options['items']) ? 1 : $options['items'];
            $checked = $options['value'] == $value ? 'checked' : null;
            return $this->renderInputRow(
                <<<HTML
<input type="checkbox" id="{$id}" name="{$options['name']}" value="{$value}" {$checked}>
<label for="{$id}"></label>
HTML, $options
            );
        }
        $html =  '';
        $i = 0;
        $value = $options['value'] ?? '';
        foreach ($options['items'] as $key => $item) {
            $checked = (!is_array($value) && $value == $key)
            || (is_array($value) && in_array($key, $value)) ? 'checked' : null;
            $id = $options['id'].($i++);
            $html .= <<<HTML
<span class="check-label">
    <input type="checkbox" id="{$id}" name="{$options['name']}" value="{$key}" {$checked}>
    <label for="{$id}">{$item}</label>
</span>
HTML;
        }
        return $this->renderInputRow($html, $options);
    }

    protected function encodeSelect(array $options): string {
        $html =  '';
        $value = $options['value'] ?? '';
        foreach ($options['items'] as $key => $item) {
            $html .= Html::tag('option', $item, array(
                'value' => $key,
                'selected' => $value == $key
            ));
        }
        return $this->renderInputRow(
            Html::tag(
                'select', $html, [
                'name' => $options['name'],
                'id' => $options['id'],
                'required' => isset($options['required']) && $options['required'],
                'class' => 'form-control'
            ]), $options
        );
    }

    protected function encodeFile(array $options): string {
        $options['boxClass'] = 'file-input';
        $upload = __('Upload');
        $preview =  __('Preview');
        $allow = !empty($options['allow']) ? sprintf(' data-allow="%s"', $options['allow']) : '';
        unset($options['allow']);
        $html = <<<HTML
<button type="button" class="btn btn-default" data-type="upload"{$allow}>{$upload}</button>

HTML;
        if (str_contains($allow, 'image/')) {
            $html .= <<<HTML
<button type="button" class="btn btn-info"  data-type="preview">{$preview}</button>
HTML;
        }
        if (isset($options['dialog'])) {
            $options['dialog'] = $options['dialog'] === 'multiple' ? 'multiple' : 'single';
            $online =  __('Online');
            $html .= <<<HTML

<button type="button" class="btn btn-success" data-type="images" data-mode="{$options['dialog']}">{$online}</button>
HTML;
        }
        return $this->renderInputRow(
            BaseForm::input('text', $options['name'], $options['value'], $options). $html,
            $options
        );
    }

    protected function encodeTip(array $data): string {
        if (empty($data['tip'])) {
            return '';
        }
        return sprintf('<div class="tooltip">%s</div>', \Zodream\Helpers\Html::fromText($data['tip'], false));
    }
}