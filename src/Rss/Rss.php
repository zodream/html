<?php
declare(strict_types=1);
namespace Zodream\Html\Rss;


class Rss extends BaseRss {
    protected string $language = 'zh-CN';
    /**
     * @var RssItem[]
     */
    protected array $items = [];

    protected array $image = [];

    public function setLanguage(string $value) {
        $this->language = $value;
        return $this;
    }

    public function addItem(RssItem $item) {
        $this->items[] = $item;
        return $this;
    }

    /**
     * @param string $url 图片
     * @param string $link
     * @param string|null $title
     * @return Rss
     */
    public function setImage(string $url, string $link, string $title = null) {
        $this->image = compact('url', 'link');
        if (!empty($title)) {
            $this->image['title'] = $title;
        }
        return $this;
    }

    public function __toString(): string {
        $lines = [
            '<?xml version="1.0" encoding="utf-8"?>',
            '<rss version="2.0">',
            '<channel>',
            sprintf('<title>%s</title>', $this->title),
            sprintf('<link>%s</link>', $this->link),
            sprintf('<description>%s</description>', $this->description),
            sprintf('<language>%s</language>', $this->language),
            sprintf('<pubDate>%s</pubDate>', $this->getPubDate()),
        ];
        if (!empty($this->image)) {
            $lines[] = '<image>';
            foreach($this->image as $key => $val) {
                $lines[] = sprintf('<%s>%s</%s>', $key, $val, $key);
            }
            $lines[] = '</image>';
        }
        foreach($this->tags as $key => $val) {
            $lines[] = sprintf('<%s>%s</%s>', $key, $val, $key);
        }
        foreach($this->items as $item) {
            $lines[] = (string)$item;
        }
        $lines[] = '</channel>';
        $lines[] = '</rss>';
        return implode("\n", $lines);
    }

    public function show() {
         return app('response')->rss((string)$this);
    }
}