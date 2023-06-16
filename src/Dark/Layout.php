<?php
declare(strict_types=1);
namespace Zodream\Html\Dark;

use Zodream\Template\View;

class Layout {

    public static function main(View $view, array $menus = [],
                                mixed $content = null,
                                string $name = 'ZoDream Admin', string $headerAction = '', bool $hasPajax = false) {
        if ($hasPajax) {
            $view->registerJs('function parseAjaxUri(uri) { $.pjax({url: uri, container: \'#page-content\'});}')
                ->registerJs('$(document).pjax(\'a:not(.no-jax)\', \'#page-content\').on(\'pjax:complete\', function() {$(\'.app-header-container .header-body\').text(document.title);});', View::JQUERY_READY);
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
    <div class="app-wrapper">
        <div class="app-mask"></div>
        <div class="sidebar-container">
            <span class="sidebar-container-toggle"></span>
            {$menu}
        </div>
        <div class="main-container">
            <div class="app-header-container">
                <div class="header-icon">
                    <span class="sidebar-container-toggle"></span>
                </div>
                <div class="header-body">
                    {$name}
                </div>
               <div class="header-action">
                    {$headerAction}
                </div>
            </div>
            <div id="page-content" class="app-main">
            {$content}
            </div>        
        </div>
    </div>
   {$footer}
   </body>
</html>
HTML;
    }

    public static function mainIfPjax(View $view, array $menus = [], mixed $content = '', string $name = 'ZoDream Admin', string $headerAction = '') {
        if (static::isPjax()) {
            return sprintf('<title>%s</title>%s%s%s',
                $view->get('title'), $view->header(false),
                $content, $view->footer(false));

        }
        return static::main($view, $menus, $content, $name, $headerAction, true);
    }

    public static function isPjax(): bool {
        return app('request')->isPjax();
    }

}