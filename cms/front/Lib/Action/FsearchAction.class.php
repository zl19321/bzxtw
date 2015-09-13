<?php
// +----------------------------------------------------------------------
// | FangFa Net [ http://www.fangfa.net ]
// +----------------------------------------------------------------------
// | File: FsearchAction.class.php
// +----------------------------------------------------------------------
// | Date: 上午08:40:55
// +----------------------------------------------------------------------
// | Author: fangfa <1364119331@qq.com>
// +----------------------------------------------------------------------
// | 文件描述: 处理站内搜索
// +----------------------------------------------------------------------

/**
 * @name处理站内搜索
 *
 */
class FsearchAction extends FbaseAction {
	/**
	 * @name站内搜索入口
	 */
	public function index() {
		$in = &$this->in;
        $in['q'] = trim($in['q']);
		if ($in['q'] && ($this->isget() || $this->ispost()) ) {
			switch ($in['engine']) {
				case 'google':
					$url = 'http://www.google.com.hk/search?hl=zh-CN&safe=strict&domains='.$_SERVER['HTTP_HOST'].'&sitesearch='.$_SERVER['HTTP_HOST'].'&ie=UTF8&oe=UTF8&q=' . $in['q'];
					header('Location:' . $url);
					exit;
					break;
				case 'baidu':
					$url = 'http://www.baidu.com/baidu?ct=2097152&si='.$_SERVER['HTTP_HOST'].'&tn=bds&cl=3&word=' . $in['q'];
					header('Location:' . $url);
					exit;
					break;
				case 'yahoo':
					$url = 'http://www.yahoo.com.cn/search?vs='.$_SERVER['HTTP_HOST'].'&p=' . $in['q'];
					header('Location:' . $url);
					exit;
					break;
				default:
					$this->manage();
					exit;
                    break;
			}
		}

		$Category = D('Category', 'admin');
		$where['ishtml'] = 1;
		$where['type'] = 'normal';
		if($in['catid']) {
			if($in['travel']) {
				$category_ids = $Category->getChildIdsArr($in['catid']);
				$category_ids[] = $in['catid'];
				$where['catid'] = array('in', implode(',', $category_ids));
			} else {
				$where['catid'] = $in['catid'];
			}
		}
		$data = $Category->field("`catid` AS `id`,`name`,`modelid`,`parentid`")
				->where($where)
				->order("`sort` ASC,`catid` DESC")
				->findAll();

		import ( 'Tree', INCLUDE_PATH );
		$tree = get_instance_of ( 'Tree' );
		$tree->init ( $data );
		$str = "<option value='\$id'>\$spacer\$name</option>";
		$html = $tree->get_tree ( 0, $str );
		$this->assign('html',$html);

		$in['ishtml'] = false;
		$seo['seotitle'] = '高级搜索';
		$seo['keywords'] = $in['q'];
		$seo['description'] = $in['q'];
		$this->assign('seo',$seo);
		$this->display('search.html');
	}

    /**
     * @name站内搜索
     */
	public function manage()
	{
		$in = &$this->in;
		$in['orderby'] = $in['orderby'] ? $in['orderby'] : 'cid';
		$pagesize = $in['pagesize'] ? intval($in['pagesize']) : 20;  //每页显示条数

		$in['p'] = (intval($in['p']) ? $in['p'] : 1);   //当前页码
		$Content = D('Content', 'admin');

		$where = ' WHERE 1 ';
		$order = ' ORDER BY c.' . $in['orderby'] . ' DESC';

		if ($in['catid']){
			if($in['travel']) {
				$Category = D('Category', 'admin');
				$category_ids = $Category->getChildIdsArr($in['catid']);
				$category_ids[] = $in['catid'];
				$where .= 'AND c.catid IN(' . implode(',', $category_ids) . ')';
			} else {
				$where .= 'AND c.catid=' . $in['catid'];
			}
		}
		if(!empty($in['starttime']) && intval($in['starttime']) > 0) {
			$where .= ' AND c.create_time>' . (time()-3600*24*$in['starttime']);
		}

		$in['q'] = trim($in['q']);
 		switch ($in['searchtype']) {
			case 'title':
				$where .= ' AND c.title LIKE BINARY "%' . $in['q'] . '%"';
				break;
			default:
				$where .= ' AND (c.title LIKE BINARY "%' . $in['q'] . '%" OR c.description LIKE BINARY "%' . $in['q'] . '%") ';
		}
		//获取总数
		$count_query = 'SELECT count(c.cid) AS total FROM {db_prefix}content AS c ' . $where . $order;
		$total = $Content->query(str_replace('{db_prefix}', C('DB_PREFIX'), $count_query));

		$pageurl = __ROOT__ . '/search/list?dosubmit=1&p={page}&q=' . urlencode($in['q']);
		$in['catid'] && $pageurl .= '&catid=' . $in['catid'];
		$in['travel'] && $pageurl .= '&travel=' . $in['travel'];
		$in['starttime'] && $pageurl .= '&starttime=' . $in['starttime'];
		$in['orderby'] && $pageurl .= '&orderby=' . $in['orderby'];
		$in['pagesize'] && $pageurl .= '&pagesize=' . $in['pagesize'];
		$in['searchtype'] && $pageurl .= '&searchtype=' . $in['searchtype'];

		$data ['pages'] = multi($total[0]['total'], $in['p'], $pageurl, $pagesize, false); //分页html
		$limit = ' LIMIT ' . ($in['p']-1)*$pagesize . ', ' . $pagesize;

		$query = 'SELECT c.*, cat.name AS category_name FROM {db_prefix}content AS c LEFT JOIN {db_prefix}category AS cat ON cat.catid=c.catid ' . $where . $order . $limit;
		$data['info'] = $Content->query(str_replace('{db_prefix}', C('DB_PREFIX'), $query));

		if ($data['info']) {
			foreach ($data['info'] AS &$c) {
				$c['title'] = str_replace($in['q'], '<font color="red">'.$in['q'].'</font>', $c['title']);
				$c['description'] = str_replace($in['q'], '<font color="red">'.$in['q'].'</font>', $c['description']);
				$c['thumb'] && $c['thumb'] = __ROOT__ . '/' . C('UPLOAD_DIR') . $c['thumb'];
				$c['url'] = $Content->getUrl($c);
			}
		}

		$in['ishtml'] = false;
		$seo['seotitle'] = $in['q'] . ' 搜索结果';
		$seo['keywords'] = $in['q'];
		$seo['description'] = $in['q'];
		$this->assign('seo',$seo);

		$this->assign('data', $data);
		$this->display('search_result.html');
	}
}