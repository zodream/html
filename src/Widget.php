<?php
declare(strict_types=1);
namespace Zodream\Html;
/**
 * 视图组件基类
 * Created by PhpStorm.
 * User: zx648
 * Date: 2016/4/29
 * Time: 16:04
 */
use Zodream\Infrastructure\Support\Html;
use Zodream\Infrastructure\Base\MagicObject;
use Zodream\Helpers\Json;
use Zodream\Helpers\Time;

abstract class Widget extends MagicObject {
    protected array $default = array();
    
    abstract protected function run(): string;
    
    public function __construct() {
        $this->set($this->default);
    }

    /**
     * 视图组件总入口
     * @param array $config
     * @return string
     * @throws \Exception
     */
    public static function show(array $config = array()): string {
        ob_start();
        ob_implicit_flush(false);
        try {
            $instance = new static;
            $instance->set($config);
            $out = $instance->run();
        } catch (\Exception $e) {
            // close the output buffer opened above if it has not been closed already
            if (ob_get_level() > 0) {
                ob_end_clean();
            }
            throw $e;
        }
        return ob_get_clean() . $out;
    }
    
    protected function json(array $args = array()): string {
        return Json::encode($args);
    }
    
    public function __toString(): string {
        return $this->show();
    }

    protected function format(array $data, mixed $tag = null): string {
        if ($tag instanceof \Closure) {
            return call_user_func_array($tag, $data);
        }
        $result = array();
        foreach ($data as $item) {
            $result[] = $this->formatOne($item, $tag);
        }
        return implode(' ', $result);
    }

    protected function formatOne(mixed $data, string|array|null $tag = ''): mixed {
        if (empty($tag)) {
            return $data;
        }
        if (is_array($tag)) {
            return array_key_exists($data, $tag) ? $tag[$data] : null;
        }
        if ($tag === 'html') {
            return htmlspecialchars_decode($data);
        }
        if ($tag === 'img') {
            return Html::img($data);
        }
        if ($tag === 'url') {
            return Html::a($data, $data);
        }
        if ($tag === 'email') {
            return Html::a($data, 'mailto:'.$data);
        }
        if ($tag === 'tel') {
            return Html::a($data, 'tel:'.$data);
        }
        if ($tag === 'int') {
            return intval($data);
        }
        if (empty($data)) {
            return __('(not set)');
        }
        if ($tag === 'date') {
            return Time::format($data, 'Y-m-d');
        }
        if ($tag === 'datetime') {
            return Time::format($data);
        }
        if ($tag === 'ago') {
            return Time::isTimeAgo($data);
        }
        return $data;
    }
}