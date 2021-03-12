<?php
declare(strict_types=1);
namespace Zodream\Html\Dark;

use Zodream\Database\Model\Model;
use Zodream\Helpers\Arr;
use Zodream\Helpers\Json;
use Zodream\Helpers\Str;
use Zodream\Html\Form as BaseForm;

class Form {
    /**
     * @var Model
     */
    protected static $model;

    public static function getModelValue(string $name) {
        if (!(static::$model instanceof Model)) {
            return null;
        }
        if (strpos($name, '.') <= 0) {
            return static::$model->getAttributeValue($name);
        }
        $args = explode('.', $name);
        $main = array_shift($args);
        $val = static::$model->getAttributeValue($main);
        if (empty($val)) {
            return null;
        }
        return Arr::getChildByArray($args, is_array($val) ? $val : Json::decode($val));
    }

    public static function getModelLabel(string $name) {
        return static::$model instanceof Model ?
            static::$model->getLabel($name) : Str::studly($name);
    }

    public static function open($model, $uri = null, array $option = []) {
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

    /**
     * @param string $name
     * @param bool $required
     * @param string $placeholder
     * @return Input
     */
    public static function text(string $name, bool $required = false, string $placeholder = '') {
        return Theme::text(static::formatName($name), static::getModelValue($name), static::getModelLabel($name), $placeholder, $required);
    }

    /**
     * @param string $name
     * @param bool $required
     * @param string $placeholder
     * @param string $label
     * @param bool $toggle 是否显示
     * @return null|string
     */
    public static function password(string $name, bool $required = false, string $placeholder = '', string $label = '', bool $toggle = true) {
        if (!$toggle) {
            return null;
        }
        return Theme::password(static::formatName($name), $label ?: static::getModelLabel($name), $placeholder, $required);
    }

    /**
     * @param string $name
     * @param bool $required
     * @param string $placeholder
     * @return Input
     */
    public static function email(string $name, bool $required = false, string $placeholder = '') {
        return Theme::email(static::formatName($name), static::getModelValue($name), static::getModelLabel($name), $placeholder, $required);
    }

    /**
     * @param $name
     * @param $data
     * @return Input
     */
    public static function radio(string $name, $data) {
        return Theme::radio(static::formatName($name), $data, static::getModelValue($name), static::getModelLabel($name));
    }
    /**
     * @param $name
     * @param $data
     * @return Input
     */
    public static function checkbox(string $name, $data = null) {
        return Theme::checkbox(static::formatName($name), $data, static::getModelValue($name), static::getModelLabel($name));
    }

    /**
     * @param $name
     * @param array $data
     * @param bool $required
     * @return Input
     */
    public static function select(string $name, array $data, bool $required = false) {
        return Theme::select(static::formatName($name), $data, static::getModelValue($name), static::getModelLabel($name), $required);
    }

    /**
     * @param string $name
     * @param bool $required
     * @param string $placeholder
     * @return Input
     */
    public static function file(string $name, bool $required = false, string $placeholder = '') {
        return Theme::file(static::formatName($name), static::getModelValue($name), static::getModelLabel($name), $placeholder, $required);
    }

    /**
     * @param $name
     * @param bool $required
     * @param null $placeholder
     * @return Input
     */
    public static function textarea(string $name, bool $required = false, string $placeholder = '') {
        return Theme::textarea(static::formatName($name), static::getModelValue($name), static::getModelLabel($name), $placeholder, $required);
    }

    public static function switch(string $name) {
        return Theme::switch(static::formatName($name), intval(static::getModelValue($name)), static::getModelLabel($name));
    }


    /**
     * @param string $pk 是否隐藏输出主键
     * @return string|static
     */
    public static function close(string $pk = '') {
        $html = '';
        if (!empty($pk) && !empty(static::$model)) {
            $html = BaseForm::hidden($pk, static::getModelValue($pk));
        }
        static::$model = null;
        return $html.BaseForm::close();
    }

    protected static function formatName(string $name): string {
        if (strpos($name, '.') < 0) {
            return $name;
        }
        $args = explode('.', $name);
        $items = [];
        foreach ($args as $i => $arg) {
            if ($i < 1) {
                $items[] = $arg;
                continue;
            }
            $items[] = sprintf('[%s]', $arg);
        }
        return implode('', $items);
    }
}