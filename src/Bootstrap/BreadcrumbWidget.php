<?php
declare(strict_types=1);
namespace Zodream\Html\Bootstrap;
/**
 * Created by PhpStorm.
 * User: zx648
 * Date: 2016/6/4
 * Time: 13:03
 */
use Zodream\Html\Widget;

class BreadcrumbWidget extends Widget {

    protected array $default = [
        'links' => []
    ];

    protected function run(): string {
        $links = $this->get('links');
        $content = null;
        $count = count($links);
        foreach ($links as $key => $link) {
            if (!is_integer($key) || $key >= $count) {
                $link = [$key, $link];
                $count --;
            }
            if (is_array($link)) {
                $label =
                    array_key_exists('label', $link) ? $link['label'] : $link[0];
                $url = array_key_exists('url', $link) ?
                    $link['url'] : (count($links) > 1 ? $link[1] : null);
            } else {
                $label = $link;
                $url = null;
            }
            if (empty($url) && $key == $count - 1) {
                $content .= Html::li($label, ['class' => 'active']);
                break;
            }
            $content .= Html::li(Html::a($label, $url));
        }
        return Html::ol($content, [
            'class' => 'breadcrumb'
        ]);
    }
}