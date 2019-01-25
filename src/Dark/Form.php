<?php
namespace Zodream\Html\Dark;


use Zodream\Database\Model\Model;
use Zodream\Helpers\Str;
use Zodream\Html\Form as BaseForm;

class Form {
    /**
     * @var Model
     */
    protected static $model;

    public static function getModelValue($name) {
        return static::$model instanceof Model ?
            static::$model->getAttributeValue($name) : null;
    }

    public static function getModelLabel($name) {
        return static::$model instanceof Model ?
            static::$model->getLabel($name) : Str::studly($name);
    }

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
        return Theme::text($name, static::getModelValue($name), static::getModelLabel($name), $placeholder, $required);
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
        return Theme::password($name, $label ?: static::getModelLabel($name), $placeholder, $required);
    }


    public static function email($name, $required = false, $placeholder = null) {
        return Theme::email($name, static::getModelValue($name), static::getModelLabel($name), $placeholder, $required);
    }

    public static function radio($name, $data) {
        return Theme::radio($name, $data, static::getModelValue($name), static::getModelLabel($name));
    }

    public static function checkbox($name, $data = null) {
        return Theme::checkbox($name, $data, static::getModelValue($name), static::getModelLabel($name));
    }

    public static function select($name, array $data, $required = false) {
        return Theme::select($name, $data, static::getModelValue($name), static::getModelLabel($name), $required);
    }

    public static function file($name, $required = false, $placeholder = null) {
        return Theme::file($name, static::getModelValue($name), static::getModelLabel($name), $placeholder, $required);
    }

    public static function textarea($name, $required = false, $placeholder = null) {
        return Theme::textarea($name, static::getModelValue($name), static::getModelLabel($name), $placeholder, $required);
    }


    /**
     * @param bool $pk 是否隐藏输出主键
     * @return string|static
     */
    public static function close($pk = false) {
        $html = '';
        if (!empty($pk) && !empty(static::$model)) {
            $html = BaseForm::hidden($pk, static::getModelValue($pk));
        }
        static::$model = null;
        return $html.BaseForm::close();
    }
}