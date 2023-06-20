<?php
declare(strict_types=1);
namespace Zodream\Html;

use Zodream\Helpers\Arr;
use Zodream\Infrastructure\Support\Html;

class Form {

    public static function open(mixed $action = null, string $method = 'POST', array $options = []) {
        $method = strtoupper($method);
        $spoofedMethods = ['DELETE', 'PATCH', 'PUT'];
        if (isset($options['files']) && $options['files']) {
            $options['enctype'] = 'multipart/form-data';
        }
        $reserved = ['method', 'url', 'route', 'action', 'files'];
        $attributes['method'] = $method != 'GET' ? 'POST' : $method;
        $attributes['action'] = (string)url()->toRealUri($action);
        $attributes['accept-charset'] = 'UTF-8';
        $attributes = array_merge(
            $attributes, Arr::except($options, $reserved)
        );
        $html = '<form '.Html::renderTagAttributes($attributes).'>';
        if (in_array($method, $spoofedMethods)) {
            $html .= self::hidden('_method', $method);
        }
        if ($method != 'GET') {
            $html .= self::token();
        }
        return $html;
    }

    public static function token(): string {
        $token = VerifyCsrfToken::get();
        return self::hidden('_token', $token);
    }

    public static function close(): string {
        return '</form>';
    }


    public static function input($type, $name, $value = null, $options = []) {
        if (!empty($name)) {
            $options['name'] = $name;
        }
        if (!is_null($value)) {
            $options['value'] = $value;
        }
        return Html::input($type, $options);
    }

    public static function text($name, $value = null, $options = []) {
        return self::input('text', $name, $value, $options);
    }

    public static function password($name, $options = []) {
        return self::input('password', $name, '', $options);
    }

    public static function range($name, $value = null, $options = []) {
        return self::input('range', $name, $value, $options);
    }

    public static function hidden($name, $value = null, $options = []) {
        return self::input('hidden', $name, $value, $options);
    }

    public static function search($name, $value = null, $options = []) {
        return self::input('search', $name, $value, $options);
    }

    public static function email($name, $value = null, $options = []) {
        return self::input('email', $name, $value, $options);
    }

    public static function tel($name, $value = null, $options = []) {
        return self::input('tel', $name, $value, $options);
    }

    public static function number($name, $value = null, $options = []) {
        return self::input('number', $name, $value, $options);
    }



}