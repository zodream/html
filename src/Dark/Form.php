<?php
namespace Zodream\Html\Dark;


use Zodream\Database\Model\Model;
use Zodream\Html\Form as BaseForm;

class Form {
    /**
     * @var Model
     */
    protected static $model;

    public static function open(Model $model, $uri, $option = []) {
        static::$model = $model;
        return BaseForm::open($uri, 'POST', array_merge([
            'data-type' => 'ajax',
            'class' => 'form-table',
            'role' => "form"
        ], $option));
    }

    public static function text($name) {
        return Theme::text($name, static::$model->get($name), static::$model->getLabel($name));
    }

    public static function password($name) {
        return Theme::password($name, '', static::$model->getLabel($name));
    }


    public static function email($name) {
        return Theme::email($name, static::$model->get($name), static::$model->getLabel($name));
    }

    public static function radio($name, array $data) {
        return Theme::radio($name, $data, static::$model->get($name), static::$model->getLabel($name));
    }

    public static function checkbox($name, array $data) {
        return Theme::checkbox($name, $data, static::$model->get($name), static::$model->getLabel($name));
    }

    public static function select($name, array $data) {
        return Theme::select($name, $data, static::$model->get($name), static::$model->getLabel($name));
    }

    public static function file($name) {
        return Theme::file($name, static::$model->get($name), static::$model->getLabel($name));
    }

    public static function textarea($name) {
        return Theme::textarea($name, static::$model->get($name), static::$model->getLabel($name));
    }


    /**
     * @return string|static
     * @throws \Exception
     */
    public static function close() {
        static::$model = null;
        return BaseForm::close();
    }
}