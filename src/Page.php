<?php
declare(strict_types=1);
namespace Zodream\Html;

use Zodream\Database\Contracts\SqlBuilder;
use Zodream\Database\Query\Builder;
use Zodream\Infrastructure\Base\MagicObject;
use JsonSerializable;
use ArrayIterator;
use Zodream\Infrastructure\Contracts\ArrayAble;
use Zodream\Infrastructure\Contracts\JsonAble;

class Page extends MagicObject implements JsonAble, ArrayAble {
	private int $_total = 0;

	private int $_index = 1;
	
	private int $_pageSize = 20;

	private string $_key = 'page';

	public function __construct(
        mixed $total, int $pageSize = 20,
                                string|int $key = 'page', int $page = -1) {
	    if (is_numeric($key)) {
	        list($key, $page) = ['page', $key];
        }
        $this->_key = $key;
        $this->_index = max(1, $page >= 0 ? $page : intval(request($key, 1)));
		$this->_pageSize = is_int($pageSize) ? $pageSize : intval($pageSize);
        $this->setTotal($total);
    }

    /**
     * @return int
     */
    public function getPageSize(): int {
        return $this->_pageSize;
    }

	/**
	 * 获取总共的数据
	 * @return int
	 */
	public function getTotal(): int {
		return $this->_total;
	}

    /**
     * 设置总共的数据
     * @param $total
     * @return $this
     * @throws \Exception
     */
	public function setTotal(mixed $total) {
	    if (is_array($total)) {
	        $this->_total = count($total);
	        if ($this->isEmpty()) {
	            $this->setPage(array_splice($total, $this->getStart(), $this->getPageSize()));
            }
	        return $this;
        }
		if ($total instanceof Builder) {
			$this->_total = intval($total->count());
			return $this;
		}
		$this->_total = intval($total);
		return $this;
	}

	/**
	 * 获取一页的数据
	 * @return array
	 */
	public function getPage(): array {
		return $this->getAttribute();
	}

    /**
     * 设置一页的数据
     * @param array|Builder $data
     * @return $this
     * @throws \Exception
     */
	public function setPage(array|SqlBuilder $data) {
		return $this->clearAttribute()->appendPage($data);
	}

    /**
     * 追加一页的数据
     * @param array|Builder $data
     * @return $this
     * @throws \Exception
     */
    public function appendPage(array|SqlBuilder $data) {
        if ($data instanceof Builder) {
            $data = $data->limit($this->getLimit())->get();
        }
        $this->setAttribute($data);
        return $this;
    }

	public function map(callable $cb) {
	    foreach ($this->__attributes as $i => $item) {
	        $res = call_user_func($cb, $item, $i);
	        if (!is_null($res)) {
                $this->__attributes[$i] = $res;
            }
        }
	    return $this;
    }

    /**
     * 判断是否为空
     * @return bool
     */
	public function isEmpty(): bool {
	    return $this->getPageCount() === 0;
    }

	/**
	 * 获取一页数据的长度
	 * @return int
	 */
	public function getPageCount(): int {
		return $this->count();
	}

    public function getPageTotal(): int {
        return (int)ceil($this->_total / $this->_pageSize);
    }

	public function getStart(): int {
	    return max(($this->_index- 1) * $this->_pageSize, 0);
    }

	/**
	 * 获取查询分页的值
	 * @return mixed
	 */
	public function getLimit(): string {
		return $this->getStart() . ','.$this->_pageSize;
	}

    /**
     * 是否还有更多
     * @return bool
     */
	public function hasMore(): bool {
	    return $this->_index * $this->_pageSize < $this->_total;
    }

    /**
     * 页码
     * @return int
     */
    public function getIndex(): int {
	    return $this->_index;
    }

	/**
	 * 获取分页链接
	 * @param array $option
	 * @return string
	 * @throws \Exception
	 */
	public function getLink(array $option = []): string {
		$option['total'] = $this->_total;
		$option['pageSize'] = $this->_pageSize;
		$option['page'] = $this->_index;
		$option['key'] = $this->_key;
		return PageLink::show($option);
	}
	
	public function __toString() {
		return $this->getLink();
	}

    public function jsonSerialize(): mixed {
        return array_map(function ($value) {
            if ($value instanceof JsonSerializable) {
                return $value->jsonSerialize();
            } elseif ($value instanceof JsonAble) {
                return json_decode($value->toJson(), true);
            } elseif ($value instanceof ArrayAble) {
                return $value->toArray();
            } else {
                return $value;
            }
        }, $this->toArray());
    }

    public function getIterator(): \Traversable {
        return new ArrayIterator($this->getAttribute());
    }

    /**
     * Get the instance as an array.
     *
     * @return array
     */
    public function toArray(): array {
        $data = array_map(function ($value) {
            if ($value instanceof ArrayAble) {
                return $value->toArray();
            }
            return $value;
        }, $this->getPage());
        return [
            'paging' => [
                'limit' => $this->getPageSize(),
                'offset' => $this->getIndex(),
                'total' => $this->getTotal(),
                'more' => $this->hasMore()
            ],
            'data' => $data
        ];
    }
}