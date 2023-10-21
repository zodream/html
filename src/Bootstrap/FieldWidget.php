<?php
declare(strict_types=1);
namespace Zodream\Html\Bootstrap;
/**
 * Created by PhpStorm.
 * User: zx648
 * Date: 2016/4/30
 * Time: 11:12
 */
use Zodream\Html\Widget;

class FieldWidget extends Widget {
    protected array $default = array(
        'type' => 'text',
        'value' => '',
        'label' => '',
        'name' => '',
        'template' => ''
    );

    protected function run(): string {
        $option = $this->get('option');
        if (!empty($option)) {
            $this->set($option);
        }
        $type = $this->get('type');
        if ($type === 'hidden') {
            return $this->hidden();
        }
        
        if (in_array($type, array('button', 'submit', 'reset'))) {
            return $this->button();
        }
        
        if (empty($this->get('template'))) {
            $this->set('template', '
<div class="form-group">
	<label for="input_{name}" class="col-md-2 control-label">{label}</label>
	<div class="col-md-10">
		{input}
	</div>
</div>'
            );
        }
        
        if ($type === 'textarea') {
            return $this->textArea();
        }
        
        if ($type === 'radio') {
            return $this->radio();
        }
        if ($type === 'checkbox') {
            return $this->checkBox();
        }
        if ($type == 'select') {
            return $this->select();
        }
        return $this->input($type);
    }
    
    public function hidden(): string {
        return Html::tag('input', '', array(
            'type' => 'hidden',
            'name' => $this->get('name'),
            'value' => $this->get('value')
        ));
    }

    public function radio(): string {
        return $this->specialInput();
    }

    protected function specialInput($type = 'radio', $name = '{name}'): string {
        $content = '';
        foreach ($this->get('groups', array()) as $key => $value) {
            if (!is_integer($key)) {
                $val = $key;
            } elseif (is_array($value)) {
                $val = $value['value'] ?? $key;
            } else {
                $val = $value;
            }
            $label = (is_string($value) ? $value : isset($value['text'])) ? $value['text'] : $val;
            $content .= Html::tag('label', Html::tag(
                    'input',
                    '',
                    array(
                        'type' => $type,
                        'name' => $name,
                        'value' => $val,
                        'checked' => $val == $this->get('value')
                    )
                ). $label, array(
                'class' => 'checkbox-inline'
            ));
        }
        return $this->replace($content);
    }

    public function checkBox(): string {
        return $this->specialInput('checkbox', $this->get('name').'[]');
    }

    public function select(): string {
        $content = '';
        foreach ($this->get('items', array()) as $key => $value) {
            $content .= Html::tag('option', $value, array(
                'value' => $key,
                'selected' => $key == $this->get('value')
            ));
        }
        return $this->replace(Html::tag(
            'select',
            $content, array(
            'name' => '{name}',
            'id' => 'input_{name}',
            'class' => $this->get('class', 'form-control'),
            'size' => $this->get('size', 1),
            'multiple' => $this->get('multiple', false)
        )));
    }

    public function text(): string {
        return $this->input(__FUNCTION__);
    }

    public function password(): string {
        return $this->input(__FUNCTION__);
    }

    public function email(): string {
        return $this->input(__FUNCTION__);
    }

    public function number(): string {
        return $this->input(__FUNCTION__);
    }

    public function button(): string {
        $this->set('template', '
<div class="form-group">
	<div class="col-md-10 col-md-offset-2">
		{input}
	</div>
</div>');
        return $this->replace(Html::tag('button', '{value}', array(
            'type' => $this->get('type'),
            'class' => $this->get('class', 'btn btn-primary')
        )));
    }

    public function input(string $type = 'text'): string {
        $option = $this->get('required,placeholder,class form-control');
        return $this->replace(Html::tag('input', '', array_merge(array(
            'type' => $type,
            'name' => '{name}',
            'id' => 'input_{name}',
            'value' => '{value}'
        ), $option)));
    }

    public function textArea(): string {
        $option = $this->get('required,placeholder,class form-control,rows 3,cols');
        return $this->replace(Html::tag('textarea', '{value}', array_merge(array(
            'name' => '{name}',
            'id' => 'input_{name}'
        ), $option)));
    }



    protected function replace(string $inputTemplate): string {
        $input = str_replace(array(
            '{name}',
            '{value}'
        ), array(
            $this->get('name'),
            $this->get('value')
        ), $inputTemplate);
        $label = $this->get('label');
        if (empty($label)) {
            return $input;
        }
        return str_replace(array(
            '{name}',
            '{input}',
            '{label}'
        ), array(
            $this->get('name'),
            $input,
            $this->get('label')
        ), $this->get('template'));
    }
}