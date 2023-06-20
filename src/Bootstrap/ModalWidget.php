<?php
declare(strict_types=1);
namespace Zodream\Html\Bootstrap;

use Zodream\Html\Widget;
class ModalWidget extends Widget {

    protected array $default = [
        'id' => 'modal',
        'size' => '',
        'title' => null,
        'body' => '',
        'foot' => ''
    ];

    protected function run(): string {
        $id = $this->get('id');
        $size = $this->get('size');
        if (!empty($size)) {
            $size = '  modal-'.$size;
        }
        $title = $this->getTitle();
        $body = $this->get('body');
        $footer = $this->getFoot();
        $html = <<<HTML
<div class="modal fade" id="{$id}" tabindex="-1" role="dialog" aria-labelledby="{$id}Label">
  <div class="modal-dialog{$size}" role="document">
    <div class="modal-content">
        {$title}
      <div class="modal-body">
        {$body}
      </div>
      {$footer}
    </div>
  </div>
</div>
HTML;
        return $html;
    }

    protected function getFoot(): string {
        $footer = $this->get('foot');
        if (is_null($footer)) {
            return '';
        }
        return <<<HTML
       <div class="modal-footer">
       {$footer}
      </div> 
HTML;
    }
    /**
     * @return string
     */
    protected function getTitle(): string {
        $id = $this->get('id');
        $title = $this->get('title');
        if (is_null($title)) {
            return '';
        }
        return <<<HTML
<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
    <h4 class="modal-title" id="{$id}Label">{$title}</h4>
</div>
HTML;
    }

    /**
     * ADD OPEN MODAL EVENT
     * @param $id
     * @return string
     */
    public static function addEvent(string $id = 'modal'): string {
        return ' data-toggle="modal" data-target="#'.$id.'" ';
    }

    /**
     * ADD MODAL CLOSE EVENT
     * @return string
     */
    public static function addClose(): string {
        return ' data-dismiss="modal" ';
    }
}