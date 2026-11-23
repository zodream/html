<?php
declare(strict_types=1);
namespace Zodream\Html;

use Zodream\Database\Contracts\SqlBuilder;
use Zodream\Infrastructure\Base\MagicObject;
use JsonSerializable;
use ArrayIterator;
use Zodream\Infrastructure\Contracts\ArrayAble;
use Zodream\Infrastructure\Contracts\JsonAble;

class Page extends MagicObject implements JsonAble, ArrayAble {

	private int $_totalItems;
	private readonly int $_currentPage;
	private readonly int $_perPage;
	private readonly string $_formKey;

	public function __construct(
        mixed $total, int $perPage = 20,
        string|int $key = 'page', int $page = -1) {
	    if (is_numeric($key)) {
	        list($key, $page) = ['page', $key];
        }
        $this->_formKey = $key;
        $this->_currentPage = max(1, $page >= 0 ? $page : intval(request($key, 1)));
		$this->_perPage = is_int($perPage) ? $perPage : intval($perPage);
        $this->setTotal($total);
    }

    /**
     * @return int
     */
    public function getPerPage(): int {
        return $this->_perPage;
    }

	/**
	 * 获取总共的数据
	 * @return int
	 */
	public function getTotalItems(): int {
		return $this->_totalItems;
	}

    /**
     * 设置总共的数据
     * @param $total
     * @return $this
     * @throws \Exception
     */
	public function setTotal(mixed $total) {
	    if (is_array($total)) {
	        $this->_totalItems = count($total);
	        if ($this->isEmpty()) {
	            $this->setItems(array_splice($total, $this->getItemsOffset(), $this->getPerPage()));
            }
	        return $this;
        }
		if ($total instanceof SqlBuilder) {
			$this->_totalItems = intval($total->count());
			return $this;
		}
		$this->_totalItems = intval($total);
		return $this;
	}

	/**
	 * 获取一页的数据
	 * @return array
	 */
	public function getItems(): array {
		return $this->getAttribute();
	}

    /**
     * 设置一页的数据
     * @param array|SqlBuilder $data
     * @return $this
     * @throws \Exception
     */
	public function setItems(array|SqlBuilder $data) {
		return $this->clearAttribute()->appendItems($data);
	}

    /**
     * 追加一页的数据
     * @param array|SqlBuilder $data
     * @return $this
     * @throws \Exception
     */
    public function appendItems(array|SqlBuilder $data) {
        if ($data instanceof SqlBuilder) {
            $data = $data->skip($this->getItemsOffset())->take($this->_perPage)->get();
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
	    return $this->getItemsCount() === 0;
    }

	/**
	 * 获取一页数据的长度
	 * @return int
	 */
	public function getItemsCount(): int {
		return $this->count();
	}

    public function getTotalPages(): int {
        return (int)ceil($this->_totalItems / $this->_perPage);
    }

	public function getItemsOffset(): int {
	    return max(($this->_currentPage- 1) * $this->_perPage, 0);
    }

    /**
     * 是否还有更多
     * @return bool
     */
	public function hasMore(): bool {
	    return $this->_currentPage * $this->_perPage < $this->_totalItems;
    }

    /**
     * 页码
     * @return int
     */
    public function getCurrentPage(): int {
	    return $this->_currentPage;
    }

	/**
	 * 获取分页链接
	 * @param array $option
	 * @return string
	 * @throws \Exception
	 */
	public function getLink(array $option = []): string {
		$option['total'] = $this->_totalItems;
		$option['pageSize'] = $this->_perPage;
		$option['page'] = $this->_currentPage;
		$option['key'] = $this->_formKey;
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
        }, $this->getItems());
        return [
            'paging' => [
                'limit' => $this->getPerPage(),
                'offset' => $this->getCurrentPage(),
                'total' => $this->getTotalItems(),
                'more' => $this->hasMore()
            ],
            'data' => $data
        ];
    }
}