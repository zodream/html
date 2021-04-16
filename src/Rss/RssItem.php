<?php
declare(strict_types=1);
namespace Zodream\Html\Rss;

class RssItem extends BaseRss {
    protected string|int $guid = '';
    protected string $attachment = '';
    protected int $length = 0;
    protected string $mimeType = '';

    public function setGuid(string|int $guid) {
        $this->guid = $guid;
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
        if($this->attachment !== '') {
            $lines[] = sprintf('<enclosure url="%s" length="%s" type="%s" />', $this->attachment, $this->length, $this->mimeType);
        }
        if(empty($this->guid)) {
            $this->guid = $this->link;
        }
        $lines[] = sprintf('<guid isPermaLink="true">%s</guid>', $this->guid);
        foreach($this->tags as $key => $val) {
            $lines[] = sprintf('<%s>%s</%s>', $key, $val, $key);
        }
        $lines[] = '</item>';
        return implode("\n", $lines);
    }

    /**
     * 添加媒体资源
     * @param string $url
     * @param string $mimeType 文件的类型 例如 audio/mp3
     * @param int $length 文件的大小
     * @return $this
     */
    public function enclosure(string $url, string $mimeType, int $length) {
        $this->attachment = $url;
        $this->mimeType  = $mimeType;
        $this->length   = $length;
        return $this;
    }
}