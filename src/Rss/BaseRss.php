<?php
declare(strict_types=1);
namespace Zodream\Html\Rss;
/**
 * Created by PhpStorm.
 * User: zx648
 * Date: 2016/3/16
 * Time: 21:06
 */
abstract class BaseRss {
    protected array $tags = [];
    protected string $pubDate = '';
    protected string $title = '';
    protected string $link = '';
    protected string $description = '';

    public function setLink(string $link): static {
        $this->link = $link;
        return $this;
    }

    public function setTitle(string $title): static {
        $this->title = $title;
        return $this;
    }

    public function setDescription(mixed $value): static {
        if (is_string($value)) {
            $value = str_replace('&', '&amp;', $value);
        }
        $this->description = sprintf('<![CDATA[%s]]>', $value);
        return $this;
    }

    public function setPubDate(string|int $time): static {
        if(is_numeric($time)) {
            $this->pubDate = date('D, d M Y H:i:s ', intval($time)) . 'GMT';
        } else {
            $this->pubDate = date('D, d M Y H:i:s ', strtotime($time)) . 'GMT';
        }
        return $this;
    }

    public function getPubDate(): string {
        if(empty($this->pubDate)) {
            return date('D, d M Y H:i:s ') . 'GMT';
        }
        return $this->pubDate;
    }

    public function addTag(string $tag, string $value): static {
        $this->tags[$tag] = $this->encodeString($value);
        return $this;
    }

    /**
     * 格式化 &
     * @param mixed $value
     * @return mixed
     */
    protected function encodeString(mixed $value): mixed {
        if (is_string($value)) {
            return str_replace('&', '&amp;', $value);
        }
        return $value;
    }

    abstract public function __toString(): string;
}