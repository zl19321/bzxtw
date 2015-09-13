<?php
// +----------------------------------------------------------------------
// | FangFa Net [ http://www.fangfa.net ]
// +----------------------------------------------------------------------
// | File: Pager.class.php
// +----------------------------------------------------------------------
// | Date: 下午01:59:13
// +----------------------------------------------------------------------
// | Author: fangfa <1364119331@qq.com>
// +----------------------------------------------------------------------
// | 文件描述: 内容分页
// +----------------------------------------------------------------------
class Pager {

	/**
	 * 总页数
	 * @var int
	 */
	private  $totalpage = 0;

	/**
	 * 每一页的内容
	 * @var array
	 */
	protected $fileds = '';

	/**
	 * 当前页码
	 * @var int
	 */
	private $pagenum = 1;

	function Pager($content = '', $page = 1) {
		$this->init ( $content, $page );
	}

	/**
	 * 初始化数据： 每页内容，总页数，当前页码
	 * @param $content
	 * @param $page
	 */
	function init($content = '', $page = 1) {
		$page = intval($page);
		if (is_array ( $content )) {
			$this->fileds = $content;
		} else {
            $this->fileds = explode ( '<div class="pagebreak"><!-- pagebreak --></div>', $content );  //kind
            if(empty($this->fields)) { //tinymce
                $this->fileds = explode ( '<!-- pagebreak -->', $content );
            }
		}
		$this->totalpage = count ( $this->fileds );
		if ($page < 1) {
			$page = 1;
		}
		$this->pagenum = $page;
	}

	/**
	 * 当前页的内容
	 */
	function content() {
		return $this->fileds [$this->pagenum - 1];
	}

	/**
	 * 生成页码
	 * @param $url
	 */
	function navbar($url = 'baseurl_{page}.html') {
		if ($this->totalpage < 2)
			return '';
		$str = '<div class="c_page">';
		if ($this->pagenum > 1) {
			$str .= '<a href="' . str_replace('{page}',$this->pagenum-1,$url) . '">上一页</a>';
		}
		for($i = 1; $i < $this->totalpage + 1; $i ++) {
			if($i == 1) {  //第一页不出现页码
				if ($i == $this->pagenum) {
					$str .= '<span>'.$i .'</span>';
				} else {
					$str .= '<a href="' . str_replace('_{page}','',$url) . '">' . $i . '</a>';
				}
			} else {
				if ($i == $this->pagenum) {
					$str .= '<span>'.$i .'</span>';
				} else {
					$str .= '<a href="' . str_replace('{page}',$i,$url) . '">' . $i . '</a>';
				}
			}
		}
		if ($this->pagenum < $this->totalpage) {
			$str .= '<a href="' . str_replace('{page}',$this->pagenum+1,$url) . '">下一页</a>';
		}
		$str .= '</div>';
		return $str;
	}
}

?>