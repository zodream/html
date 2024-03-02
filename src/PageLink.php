<?php
declare(strict_types=1);
namespace Zodream\Html;

use Zodream\Http\Uri;

/**
 * 分页类
 * 使用方式:
 * $page = new PageLink();
 */

class PageLink extends Widget {
	
	protected array $default = array(
		'total' => 0, //总条数
		'pageSize' => 20,
        'key' => 'page',
		'page' => 1,
		'length' => 8, //数字分页显示
		/**
		 * 分页显示模板
		 * 可用变量参数
		 * {total}      总数据条数
		 * {pageSize}   每页显示条数
		 * {start}      本页开始条数
		 * {end}        本页结束条数
		 * {pageTotal}  共有多少页
         * {url}        生成的链接
         * {key}        页码所对应的键
         * {page}       当前页
		 * {previous}   上一页
		 * {next}       下一页
		 * {list}       数字分页
		 * {goto}       跳转按钮
		 */
		'template' => '<nav><ul class="pagination pagination-lg">{previous}{list}{next}</ul></nav>',
		'active' => '<li class="active"><span>{text}</span></li>',
		'common' => '<li><a href="{url}">{text}</a></li>',
		'previous' => '《',
		'next' => '》',
        'omit' => '...',
        'goto' => '&nbsp;<input type="text" value="{page}" 
            onkeydown="if ( event.key==\'Enter\') {
                var page = (this.value > {pageTotal}) ? {pageTotal} :this.value;
            }
            location =\'{url}&{key}=\'+page+\'\'}" style="width:25px;"/>
            <input type="button" onclick="
            var page = (this.previousSibling.value>{pageTotal} ) ? {pageTotal} : this.previousSibling.value;
            location =\'{url}&{key}=\'+page+\'\'" value="GO"/>'
	);
	/**
	 * 总页数
	 */
	protected int $pageTotal = -1;

	public function getPageTotal(): int {
	    $total = ceil($this->get('total') / $this->get('pageSize'));
        $this->pageTotal = intval($total);
        return $this->pageTotal;
    }

	/**
	 * 返回分页
	 * @return string
	 */
	public function getHtml(): string {
	   if ($this->getPageTotal() < 2 && $this->get('page') == 1) {
	       return '';
       }
       return str_ireplace(array(
				'{total}',
				'{pageSize}',
				'{start}',
				'{end}',
				'{pageTotal}',
				'{previous}',
				'{next}', 
				'{list}',
				'{goto}',
		), array(
				$this->get('total'),
				$this->setPageSize(),
				$this->getStart(),
				$this->getEnd(),
				$this->pageTotal,
				$this->getPrevious(),
				$this->getNext(),
				$this->getPageList(),
				$this->getGoToPage(),
		), $this->get('template'));
	}

	/**
	 * 本页开始条数
	 * @return int
	 */
	protected function getStart(): int {
		if ($this->get('total') == 0) {
			return 0;
		}
		return ($this->get('page') - 1) * $this->get('pageSize') + 1;
	}

	/**
	 * 本页结束条数
	 * @return int
	 */
    protected function getEnd(): int {
		return min($this->get('page') * $this->get('pageSize'), $this->get('total'));
	}

	/**
	 * 设置当前页大小
	 * @return int
	 */
	protected function setPageSize(): int {
		return $this->getEnd() - $this->getStart() + 1;
	}

	/**
	 * 上一页
	 * @return string
	 */
	protected function getPrevious(): string {
		if ($this->get('page')> 1) {
			return $this->replaceLine($this->get('page') - 1, $this->get('previous'));
		}
		return '';
	}

    /**
     * 获取省略
     * @return string
     */
	protected function getOmit(): string {
	    return $this->replaceTemplate('', $this->get('omit'));
    }

	/**
	 * 分页数字列表
	 * @return string
	 */
	protected function getPageList(): string {
		$linkPage = '';
		$linkPage .= $this->replaceLine(1);
		$lastList = floor($this->get('length') / 2);
		$i = 0;
		$length = 0;
		if ($this->pageTotal < $this->get('length') || $this->get('page') - $lastList < 2 || $this->pageTotal - $this->get('length') < 2) {
			$i = 2;
			if ($this->pageTotal <= $this->get('length')) {
				$length = $this->pageTotal - 1;
			} else {
				$length = $this->get('length');
			}
		} elseif ($this->get('page') - $lastList>= 2 && $this->get('page') + $lastList <= $this->pageTotal) {
			$i = $this->get('page')- $lastList;
			$length = $this->get('page') + $lastList- 1;
		} elseif ($this->get('page') + $lastList > $this->pageTotal) {
			$i = $this->pageTotal - $this->get('length') + 1;
			$length = $this->pageTotal - 1;
		}
		if ($this->get('page') > $lastList + 1 && $i > 2) {
			$linkPage .= $this->getOmit();
		}
		for (; $i <= $length; $i ++) {
			$linkPage .= $this->replaceLine((int)$i);
		}
		if ($this->get('page') < $this->pageTotal - $lastList && $length < $this->pageTotal - 1) {
			$linkPage .= $this->getOmit();
		}
		if ($this->pageTotal > 1) {
            $linkPage .= $this->replaceLine($this->pageTotal);
        }
		return $linkPage;
	}

	/**
	 * 下一页
	 * @return string
	 */
	protected function getNext(): string {
		if ($this->get('page')< $this->pageTotal) {
			return $this->replaceLine($this->get('page')+ 1, $this->get('next'));
		}
		return '';
	}

	/**
	 * 跳转按钮
	 * @return string
	 */
	protected function getGoToPage(): string {
	    $uri = (new Uri(request()->url()))
            ->removeData($this->get('key'));
	    if (!$uri->hasData()) {
	        $uri .= '?';
        }
		return str_ireplace([
		    '{url}',
            '{page}',
            '{pageTotal}',
            '{key}'
        ], [
            (string)$uri,
            $this->get('page'),
            $this->pageTotal,
            $this->get('key')
        ], $this->get('goto'));
	}
	
	protected function replaceLine(int $page, ?string $text = null): string {
		return $this->replaceTemplate(
            url([
                $this->get('key') => $page
            ], [], true, false),
            $text == null ? $page : $text,
            $page == $this->get('page')
		);
	}

    /**
     * 模板替换
     * @param string $url 替换内容
     * @param string|int $text
     * @param bool|string $result 条件
     * @return string
     */
	protected function replaceTemplate(string $url, string|int $text, bool $result = true): string {
		$template = ($result ? $this->get('active') : $this->get('common'));
		$html = str_replace('{url}', $url, $template);
		return str_replace('{text}', (string)$text, $html);
	}

	/**
	 * 执行
	 * @return string
	 */
	protected function run(): string {
		return $this->getHtml();
	}
}