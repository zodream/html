<?php
namespace Zodream\Html;

use Zodream\Helpers\Str;
use Zodream\Helpers\Time;

/**
 * 结合数据实现断点继续
 * @package Zodream\Html
 */
class Progress {

    protected $options = [];
    protected $start_at = 0;
    protected $spent = 0;
    protected $data = [];
    protected $index = -1;
    protected $booted = false;
    protected $cache_key;
    protected $max_time = 10;

    public function __construct($options = []) {
        $this->options = $options;
        $this->start_at = time();
    }

    /**
     * @param int $index
     * @return Progress
     */
    public function setStart(int $index) {
        $this->index = $index - 1;
        return $this;
    }

    public function init() {
    }

    protected function doInit() {
        if ($this->booted) {
            return;
        }
        $this->cache_key = md5(static::class.Str::random().Time::millisecond());
        $this->spent = 0;
        $this->init();
        $this->booted = true;
    }

    public function current() {
        return $this->data[$this->index];
    }

    /**
     * @param int $index 支持指定开始个数，从0开始 -1 为不指定
     * @return array
     * @throws \Exception
     */
    public function invoke($index = -1) {
        $this->doInit();
        if ($index >= 0) {
            $this->setStart($index);
        }
        while ($this->canDoNext()) {
            $this->index ++;
            if ($this->index >= count($this->data)) {
                return $this->finish();
            }
            $this->play($this->current());
        }
        $spent = time() - $this->start_at;
        $this->spent += $spent;
        cache()->set($this->cache_key, $this);
        return [
            'key' => $this->cache_key,
            'current' => $this->index,
            'next' => $this->index + 1,
            'count' => count($this->data),
            'time' => $spent,
            'spent' => $this->spent
        ];
    }

    public function play($item) {

    }

    protected function canDoNext() {
        if (app('request')->isCli()) {
            return true;
        }
        return time() - $this->start_at < $this->max_time;
    }

    public function finish($expire = 600) {
        $this->spent += time() - $this->start_at;
        if (app('request')->isCli()) {
            echo sprintf('本次耗时：%s秒', $this->spent);
        }
        if ($expire <= 0) {
            cache()->delete($this->cache_key);
            return [
                'count' => count($this->data),
                'spent' => $this->spent
            ];
        }
        cache()->set($this->cache_key, $this, $expire);
        return [
            'count' => count($this->data),
            'spent' => $this->spent
        ];
    }

    public function __invoke($index = -1) {
        return $this->invoke($index);
    }

    public function __sleep() {
        return ['options', 'data', 'index', 'booted', 'cache_key', 'max_time', 'spent'];
    }

    public function __wakeup() {
        $this->start_at = time();
    }
}