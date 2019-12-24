<?php
namespace Zodream\Html\Rss;

class RssItem extends BaseRss {
    protected $giud;
    protected $attachment;
    protected $length;
    protected $mimeType;

    public function setGiud($giud) {
        $this->giud = $giud;
        return $this;
    }

    public function __toString() {
        $lines = [
            '<item>',
            sprintf('<title>%s</title>', $this->title),
            sprintf('<link>%s</link>', $this->link),
            sprintf('<description>%s</description>', $this->description),
            sprintf('<pubDate>%s</pubDate>', $this->getPubDate()),
        ];
        if($this->attachment != '') {
            $lines[] = sprintf('<enclosure url="%s" length="%s" type="%s" />', $this->attachment, $this->length, $this->mimeType);
        }
        if(empty($this->giud)) {
            $this->giud = $this->link;
        }
        $lines[] = sprintf('<guid isPermaLink="true">%s</guid>', $this->giud);
        foreach($this->tags as $key => $val) {
            $lines[] = sprintf('<%s>%s</%s>', $key, $val, $key);
        }
        $lines[] = '</item>';
        return implode("\n", $lines);
    }

    public function enclosure($url, $mimeType, $length) {
        $this->attachment = $url;
        $this->mimeType  = $mimeType;
        $this->length   = $length;
        return $this;
    }
}