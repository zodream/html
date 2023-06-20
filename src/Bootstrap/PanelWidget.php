<?php
declare(strict_types=1);
namespace Zodream\Html\Bootstrap;
/**
 * Created by PhpStorm.
 * User: zx648
 * Date: 2016/4/30
 * Time: 14:43
 */
use Zodream\Html\Widget;

class PanelWidget extends Widget {
    protected array $default = array(
        'class' => 'panel panel-default'
    );

    protected function run(): string {
        return Html::tag(
            'div',
            $this->getHead(). $this->getBody(). $this->getFoot(),
            $this->get('id,class'));
    }

    protected function getHead(): string {
        if (!$this->has('head')) {
            return '';
        }
        return '<div class="panel-heading">
			<h3 class="panel-title">'.$this->get('head').'</h3>
	  </div>';
    }

    protected function getBody(): string {
        if (!$this->has('body')) {
            return '';
        }
        return Html::tag('div', $this->get('body'), array(
            'class' => 'panel-body'
        ));
    }

    protected function getFoot(): string {
        if (!$this->has('foot')) {
            return '';
        }
        return Html::tag('div', $this->get('foot'), array(
            'class' => 'panel-footer'
        ));
    }
}