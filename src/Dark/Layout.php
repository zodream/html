<?php
namespace Zodream\Html\Dark;

use Zodream\Template\View;

class Layout {

    public static function main(View $view, array $menus = [], $content = null, $name = 'ZoDream Admin', $hasPajax = false) {
        if ($hasPajax) {
            $view->registerJs('function parseAjaxUri(uri) { $.pjax({url: uri, container: \'#page-content\'});}')
                ->registerJs('$(document).pjax(\'a\', \'#page-content\');', View::JQUERY_READY);
        }
        $lang = $view->get('language', 'zh-CN');
        $description = $view->get('description');
        $keywords = $view->get('keywords');
        $title = $view->get('title');
        $header = $view->header();
        $footer = $view->footer();
        $menu = Theme::menu($menus);
        $token = csrf_token();
        return <<<HTML
<!DOCTYPE html>
<html lang="{$lang}">
   <head>
       <meta name="viewport" content="width=device-width, initial-scale=1"/>
       <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
       <meta name="Description" content="{$description}" />
       <meta name="keywords" content="{$keywords}" />
       <meta name="csrf-token" content="{$token}">
       <title>{$title}</title>
       {$header}
   </head>
   <body>
   <header>
        <div class="container">
            {$name}
        </div>
    </header>
    <div class="app-wrapper">
        <div class="sidebar-container navbar">
            <span class="sidebar-container-toggle"></span>
            {$menu}
        </div>
        <div id="page-content" class="main-container">
            {$content}
        </div>
    </div>
   {$footer}
   </body>
</html>
HTML;
    }

    public static function mainIfPjax(View $view, array $menus = [], $content = null, $name = 'ZoDream Admin') {
        if (app('request')->isPjax()) {
            return sprintf('<title>%s</title>%s%s%s',
                $view->get('title'), $view->renderHeader(),
                $content, $view->renderFooter());

        }
        return static::main($view, $menus, $content, $name,true);
    }

}