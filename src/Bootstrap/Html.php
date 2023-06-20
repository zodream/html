<?php
declare(strict_types=1);
namespace Zodream\Html\Bootstrap;
/**
 * Created by PhpStorm.
 * User: zx648
 * Date: 2016/6/4
 * Time: 10:58
 */
use Zodream\Infrastructure\Support\Html as BaseHtml;

class Html extends BaseHtml {

    public static function container(string $content, bool $full = false): string {
        return static::div(
            $content,
            [
                'class' => 'container'.($full ? '-fluid' : null)
            ]
        );
    }
    
    public static function row(string $content): string {
        return static::div($content, [
            'class' => 'row'
        ]);
    }
    
    public static function col(string $content, int|string $size = 1, array|string $types = ['md']): string {
        $class = [];
        foreach ((array)$types as $item) {
            $class[] = 'col-'.$item.'-'.$size;
        }
        return static::div($content, array(
            'class' => implode(' ', $class)
        ));
    }

}