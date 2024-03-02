<?php
declare(strict_types=1);
namespace Zodream\Html;

use Zodream\Helpers\Arr;
use Zodream\Helpers\Json;
use Zodream\Helpers\Str;
use Zodream\Html\Dark\DarkRenderer;
use Zodream\Infrastructure\Contracts\ArrayAble;
use Zodream\Infrastructure\Contracts\JsonAble;
/**
 *
 *
 * @example [
 *      Input::text('title', '标题', true),
 *      Input::radio('type', '标题', [], true),
 *      Input::select('type', '标题', [], true),
 *      Input::checkbox('type', '标题', [], true),
 *      Input::image('type', '标题', true),
 *      Input::file('type', '标题', true),
 *      Input::switch('is_editable', '标题'),
 *      'group' => [
 *          Input::html('user'),
 *          Input::markdown('user'),
 *          Input::textarea('description'),
 *          Input::date('date'),
 *          Input::color('color'),
 *      ]
 * ]
 */
class Input implements ArrayAble, JsonAble, \Stringable {

    const TYPE_ITEMS = ['text',
        'numeric',
        'email',
        'url',
        'password',
        'file',
        'tel',
        'image',
        'radio',
        'checkbox',
        'select', 'textarea', 'switch', 'html',
        'markdown'
    ];
    const PROPERTY_ITEMS = ['id', 'class', 'type', 'label', 'name', 'tip', 'items', 'value',
        'required', 'placeholder'];

    public function __construct(
        protected array $data = [],
        protected array $option = [],
    ) {

    }

    public function setType(string $type, ?string $name = null, mixed $value = null) {
        $this->data['type'] = $type;
        if (!empty($name)) {
            $this->data['name'] = $name;
        }
        if (!is_null($value)) {
            $this->data['value'] = $value;
        }
        return $this;
    }

    public function attr(string $key, mixed $value): static {
        $this->option[$key] = $value;
        return $this;
    }

    /**
     * 转化请求的值
     * @param mixed $value
     * @return mixed
     */
    public function filter(mixed $value): mixed {
        return $value;
    }

    public function toArray(): array {
        return array_merge(Arr::toArray($this->data), [
            'option' => $this->option
        ]);
    }

    public function toJson(int $options = JSON_UNESCAPED_UNICODE): string {
        return Json::encode($this->toArray(), $options);
    }


    public function __toString(): string {
        app()->singletonIf(IHtmlRenderer::class, DarkRenderer::class);
        return app(IHtmlRenderer::class)->renderInput($this);
    }

    public function __set(string $name, $value): void {
        if (in_array($name, self::PROPERTY_ITEMS)) {
            $this->data[$name] = $value;
            return;
        }
        if ($name === 'option') {
            $this->option = array_merge($this->option, $value);
            return;
        }
        $this->option[$name] = $value;
    }

    public function __get(string $name) {
        if (in_array($name, self::PROPERTY_ITEMS)) {
            return $this->data[$name] ?? '';
        }
        if ($name === 'option') {
            return $this->option;
        }
        return $this->option[$name] ?? '';
    }

    public function __call(string $name, array $arguments): Input {
        if (in_array($name, static::TYPE_ITEMS)) {
            return $this->setType($name, ...$arguments);
        }
        $method = 'set'.Str::studly($name);
        if (method_exists($this, $method)) {
            return $this->$method(...$arguments);
        }
        if (empty($arguments)) {
            return $this;
        }
        $this->__set($name, $arguments[0]);
        return $this;
    }


    public static function text(string $name, string $label, bool $required = false): static {
        $type = __FUNCTION__;
        return new static(compact('type', 'name', 'label', 'required'));
    }

    public static function password(string $name, string $label, bool $required = false): static {
        $type = __FUNCTION__;
        return new static(compact('type', 'name', 'label', 'required'));
    }

    public static function url(string $name, string $label, bool $required = false): static {
        $type = __FUNCTION__;
        return new static(compact('type', 'name', 'label', 'required'));
    }

    public static function tel(string $name, string $label, bool $required = false): static {
        $type = __FUNCTION__;
        return new static(compact('type', 'label','name', 'required'));
    }

    public static function number(string $name, string $label, bool $required = false): static {
        $type = __FUNCTION__;
        return new static(compact('type', 'label','name', 'required'));
    }

    public static function color(string $name, string $label, bool $required = false): static {
        $type = __FUNCTION__;
        return new static(compact('type', 'label','name', 'required'));
    }

    public static function textarea(string $name, string $label, bool $required = false): static {
        $type = __FUNCTION__;
        return new static(compact('type', 'label','name', 'required'));
    }

    public static function date(string $name, string $label, bool $required = false): static {
        $type = __FUNCTION__;
        return new static(compact('type', 'label','name', 'required'));
    }

    public static function email(string $name, string $label, bool $required = false): static {
        $type = __FUNCTION__;
        return new static(compact('type', 'label','name', 'required'));
    }

    public static function switch(string $name, string $label): static {
        $type = __FUNCTION__;
        return new static(compact('type', 'name', 'label'));
    }

    public static function file(string $name, string $label, bool $required = false): static {
        $type = __FUNCTION__;
        return new static(compact('type', 'name', 'label', 'required'));
    }

    public static function image(string $name, string $label, bool $required = false): static {
        $type = __FUNCTION__;
        return new static(compact('type', 'name', 'label', 'required'));
    }

    public static function html(string $name, string $label, bool $required = false): static {
        $type = __FUNCTION__;
        return new static(compact('type', 'name', 'label', 'required'));
    }

    public static function markdown(string $name, string $label, bool $required = false): static {
        $type = __FUNCTION__;
        return new static(compact('type', 'name', 'label', 'required'));
    }

    public static function radio(string $name, string $label, array $items, bool $required = false): static {
        $type = __FUNCTION__;
        return new static(compact('type', 'name', 'label', 'items', 'required'));
    }

    public static function checkbox(string $name, string $label, array $items, bool $required = false): static {
        $type = __FUNCTION__;
        return new static(compact('type', 'name', 'label', 'items', 'required'));
    }

    public static function select(string $name, string $label, array $items, bool $required = false): static {
        $type = __FUNCTION__;
        return new static(compact('type', 'name', 'label', 'items', 'required'));
    }

    public static function group(string $label, array $items): static {
        $type = __FUNCTION__;
        return new static(compact('type', 'label', 'items'));
    }

    public static function optionItem(string $name, mixed $value): array {
        return compact('name', 'value');
    }

    public static function optionItems(...$items): array {
        if (func_num_args() === 1 && (is_array($items[0]) || is_object($items[0]))) {
            $items = $items[0];
        }
        $data = [];
        foreach ($items as $i => $item) {
            if (is_string($item) || is_numeric($item)) {
                $data[] = static::optionItem((string)$item, $i);
                continue;
            }
            $data[] = $item;
        }
        return $data;
    }
}