<?php
namespace Zodream\Html\Dark;


use Zodream\Database\Model\Model;
use Zodream\Html\Form as BaseForm;

class Form {
    /**
     * @var Model
     */
    protected static $model;

    public static function open($model, $uri = null, $option = []) {
        if (!$model instanceof Model && is_string($model)) {
            list($uri, $model) = [$model, null];
        }
        static::$model = $model;
        return BaseForm::open($uri, 'POST', array_merge([
            'data-type' => 'ajax',
            'class' => 'form-table',
            'role' => "form"
        ], $option));
    }

    public static function text($name, $required = false, $placeholder = null) {
        return Theme::text($name, static::$model->get($name), static::$model->getLabel($name), $placeholder, $required);
    }

    /**
     * @param $name
     * @param bool $required
     * @param null $placeholder
     * @param null $label
     * @param bool $toggle 是否显示
     * @return null|string
     */
    public static function password($name, $required = false, $placeholder = null, $label = null, $toggle = true) {
        if (!$toggle) {
            return null;
        }
        return Theme::password($name, '', $label ?: static::$model->getLabel($name), $placeholder, $required);
    }


    public static function email($name, $required = false, $placeholder = null) {
        return Theme::email($name, static::$model->get($name), static::$model->getLabel($name), $placeholder, $required);
    }

    public static function radio($name, array $data) {
        return Theme::radio($name, $data, static::$model->get($name), static::$model->getLabel($name));
    }

    public static function checkbox($name, array $data) {
        return Theme::checkbox($name, $data, static::$model->get($name), static::$model->getLabel($name));
    }

    public static function select($name, array $data, $required = false) {
        return Theme::select($name, $data, static::$model->get($name), static::$model->getLabel($name), $required);
    }

    public static function file($name, $required = false, $placeholder = null) {
        return Theme::file($name, static::$model->get($name), static::$model->getLabel($name), $placeholder, $required);
    }

    public static function textarea($name, $required = false, $placeholder = null) {
        return Theme::textarea($name, static::$model->get($name), static::$model->getLabel($name), $placeholder, $required);
    }


    /**
     * @param bool $pk 是否隐藏输出主键
     * @return string|static
     */
    public static function close($pk = false) {
        $html = '';
        if (!empty($pk) && !empty(static::$model)) {
            $html = BaseForm::hidden($pk, static::$model->get($pk));
        }
        static::$model = null;
        return $html.BaseForm::close();
    }
}