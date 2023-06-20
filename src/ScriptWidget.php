<?php
declare(strict_types=1);
namespace Zodream\Html;
/**
 * Created by PhpStorm.
 * User: zx648
 * Date: 2016/4/29
 * Time: 16:24
 */

use Zodream\Infrastructure\Support\Html;

class ScriptWidget extends Widget {
    protected function run(): string {
        if (strtolower($this->get('kind')) === 'css') {
            return $this->css();
        }
        return $this->js();
    }

    protected function js(): string {
        if ($this->has('file')) {
            return Html::tag('script' , '', array(
                'type' => 'text/javascript',
                'src' => url()->asset($this->get('file'))
            ));
        }
        return Html::tag('script', $this->get('source'));
    }

    protected function css(): string {
        if ($this->has('file')) {
            return Html::tag('link', '', array(
                'href' => url()->asset($this->get('file')),
                'rel' => 'stylesheet'
            ));
        }
        return Html::tag('style', $this->get('source'));
    }
}