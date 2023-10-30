<?php
declare(strict_types=1);
namespace Zodream\Html\Bootstrap;
/**
 * Created by PhpStorm.
 * User: zx648
 * Date: 2016/4/30
 * Time: 9:57
 */
use Zodream\Html\Widget;
use Zodream\Service\Middleware\CSRFMiddleware;


class FormWidget extends Widget {
    protected array $default = array(
        'data' => array(),
        'fields' => array(),
        'class' => 'form-horizontal'
    );
    
    protected function run(): string {
        $this->set('action', url()->to($this->get('action')));
        $content = '';
        $data = (array)$this->get('data');
        foreach ($this->get('fields', array()) as $key => $value) {
            // 支持在表单中加其他代码
            if (is_string($value)) {
                $content .= $value;
                continue;
            }
            // 需要传值的匿名方法
            if ($value instanceof \Closure) {
                $content .= $value(array_key_exists($key, $data) ? $data[$key] : null);
                continue;
            }
            if (!is_integer($key)) {
                $value['name'] = $key;
            }
            // 当value 值已存在时表示不要自动填充值
            if (array_key_exists($key, $data) && !array_key_exists('value', $value)) {
                $value['value'] = $data[$key];
            }
            $content .= FieldWidget::show($value);
        }
        $options = $this->get('id,class form-horizontal,role form,action,method POST');
        $options['method'] = strtoupper($options['method']);
        if (!in_array($options['method'], ['GET', 'POST'])) {
            $content .= Html::input('hidden', [
                'name' => '_method',
                'value' => $options['method']
            ]);
            $options['method'] = 'POST';
        }
        if ($options['method'] != 'GET') {
            $content .= $this->csrf();;
        }
        return Html::tag('form', 
            $content,
            $options
        );
    }

    public static function begin(array $data = array(), array $option = array()) {
        $instance = new static;
        $option['data'] = $data;
        $instance->set($option);
        return $instance;
    }
    
    public function csrf() {
        return $this->hidden(CSRFMiddleware::FORM_KEY, array(
            'value' => session()->token()
        ));
    }

    /**
     * 加入原生HTML代码
     * @param string $content
     * @return $this
     */
    public function html(string $content) {
        if (func_num_args() == 1) {
            $this->_data['fields'][] = $content;
            return $this;
        }
        $this->__attributes['fields'][$content] = func_get_arg(1);
        return $this;
    }
    
    public function hidden(string $name, array $option = array()) {
        return $this->input($name, __FUNCTION__, $option);
    }

    public function text(string $name, array $option = array()) {
        return $this->input($name, __FUNCTION__, $option);
    }

    public function input(string $name, string $type = 'text', array $option = array()) {
        if (!in_array($type, array('radio', 'checkbox'))) {
            $this->__attributes['fields'][$name] = array(
                'type' => $type,
                'option' => $option
            );
            return $this;
        }
        if (!isset($this->_data['fields'][$name])) {
            $this->__attributes['fields'][$name] = array(
                'type' => $type,
                'label' => $option['label'] ?? null,
                'groups' => array()
            );
        }
        $this->__attributes['fields'][$name]['groups'][] = $option;
        return $this;
    }

    public function textArea(string $name, array $option = array()) {
        $this->__attributes['fields'][$name] = array(
            'type' => 'textarea',
            'option' => $option
        );
        return $this;
    }

    public function email(string $name, array $option = array()) {
        return $this->input($name, __FUNCTION__, $option);
    }

    public function number(string $name, array $option = array()) {
        return $this->input($name, __FUNCTION__, $option);
    }

    public function password(string $name, array $option = array()) {
        return $this->input($name, __FUNCTION__, $option);
    }

    /**
     * label 做总标签 text 做标签 value 做值
     * @param string $name
     * @param array $option
     * @return FormWidget
     */
    public function checkbox(string $name, array $option = array()) {
        return $this->input($name, __FUNCTION__, $option);
    }


    /**
     * label 做总标签 text 做标签 value 做值
     * @param string $name
     * @param array $option
     * @return FormWidget
     */
    public function radio(string $name, array $option = array()) {
        return $this->input($name, __FUNCTION__, $option);
    }

    public function select(string $name, array $items, array $option = array()) {
        $this->__attributes['fields'][$name] = array(
            'type' => 'select',
            'items' => $items,
            'option' => $option
        );
        return $this;
    }

    /**
     * 列表框
     * @param string $name 名称
     * @param array $items 列表项
     * @param string $size 显示个数
     * @param bool $allowMultiple 是否允许多选
     * @param array $option
     * @return FormWidget
     */
    public function listBox(string $name, array $items, string|int $size = 10,
                            bool $allowMultiple = false, array $option = array()) {
        $option['size'] = $size;
        $option['multiple'] = $allowMultiple;
        return $this->select($name, $items, $option);
    }

    
    public function button(string $value = 'Submit', string $type = 'submit', array $option = array()) {
        $option['type'] = $type;
        $option['value'] = $value;
        $this->__attributes['fields'][] = array(
            'type' => 'button',
            'option' => $option,
        );
        return $this;
    }


    /**
     * @return string|static
     * @throws \Exception
     */
    public function end() {
        return $this->show($this->get());
    }

    public function __toString(): string {
        return $this->show($this->get());
    }

    public function __call($name, $arguments) {
        return $this->input($arguments[0], $name, $arguments[1] ?? array());
    }
}