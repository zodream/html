<?php
declare(strict_types=1);
namespace Zodream\Html;

use Zodream\Helpers\Arr;
use Zodream\Infrastructure\Contracts\Http\Input as Request;

class InputHelper {

    /**
     * 从表单提交获取结果
     * @param array $inputItems
     * @param array|Request $data
     * @return array
     */
    public static function value(array $inputItems, array|Request $data): array {
        $items = [];
        foreach ($inputItems as $item) {
            $name = $item->name;
            if (!($item instanceof Input) || empty($name)) {
                continue;
            }
            if ($data instanceof Request) {
                if ($data->has($name)) {
                    $items[$name] = $item->filter($data->get($name));
                }
            } elseif (isset($data[$name])) {
                $items[$name] = $item->filter($data[$name]);
            }
        }
        return $items;
    }

    /**
     * 给表单项赋值
     * @param Input[] $inputItems
     * @param array $data
     * @return array
     */
    public static function patch(array $inputItems, array $data): array {
        $items = [];
        foreach ($inputItems as $item) {
            $name = $item->name;
            if (!($item instanceof Input) || empty($name)) {
                continue;
            }
            if (isset($data[$name])) {
                $item->value($data[$name]);
            }
            $items[] = $item;
        }
        return $items;
    }

    /**
     * 转化为键值对数组
     * @param array $data
     * @param string|array $value
     * @param string|array $key
     * @param array $prepend
     * @return array|mixed
     */
    public static function getColumnsSource(array $data, string|array $value = 'name',
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

    public static function formatItems(mixed $items): array {
        if (!empty($items) && is_array($items) &&
            !Arr::isAssoc($items) &&
            !is_numeric($items[0]) && !is_string($items[0])) {
            return InputHelper::getColumnsSource(...$items);
        }
        return $items;
    }
}