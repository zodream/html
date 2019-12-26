<?php
namespace Zodream\Html\Rss;
/**
 * Created by PhpStorm.
 * User: zx648
 * Date: 2016/3/16
 * Time: 21:06
 */
abstract class BaseRss {
    protected $tags = array();
    protected $pubDate;
    protected $title;
    protected $link;
    protected $description;

    public function setLink($link) {
        $this->link = $link;
        return $this;
    }

    public function setTitle($title) {
        $this->title = $title;
        return $this;
    }

    public function setDescription($value) {
        if ($value !== null and is_string($value) === true) {
            $value = str_replace('&', '&amp;', $value);
        }
        $this->description = sprintf('<![CDATA[%s]]>', $value);
        return $this;
    }

    public function setPubDate($time) {
        if(is_numeric($time)) {
            $this->pubDate = date('D, d M Y H:i:s ', intval($time)) . 'GMT';
        } else {
            $this->pubDate = date('D, d M Y H:i:s ', strtotime($time)) . 'GMT';
        }
        return $this;
    }

    public function getPubDate() {
        if(empty($this->pubDate)) {
            return date('D, d M Y H:i:s ') . 'GMT';
        }
        return $this->pubDate;
    }

    public function addTag($tag, $value) {
        $this->tags[$tag] = $value;
        return $this;
    }

    abstract public function __toString();
}