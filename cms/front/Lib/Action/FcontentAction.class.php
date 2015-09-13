<?php
// +----------------------------------------------------------------------
// | FangFa Net [ http://www.fangfa.net ]
// +----------------------------------------------------------------------
// | File: FpageAction.class.php
// +----------------------------------------------------------------------
// | Date: 2010-6-3
// +----------------------------------------------------------------------
// | Author: fangfa <1364119331@qq.com>
// +----------------------------------------------------------------------
// | 文件描述: 资讯内容显示
// +----------------------------------------------------------------------
defined('IN') or die('Access Denied!');
/**
 * @name 资讯内容显示
 *
 */
class FcontentAction extends FbaseAction {
	
	/**
	 * content 表对象模型实例
	 * @var object
	 */
	protected $_c = '';
	
	/**
	 * @name栏目数据
	 * @var array
	 */
	private $_category_data = array();
	
	/**
	 * @name内容详细信息
	 * @var array
	 */
	protected $_data = array();
	
	/**
	 * @name执行的动作
	 * @var string
	 */
	protected $_action = '';
	
	/**
	 * @name初始化
	 */
	protected function _initialize() {
		parent::_initialize();
		$prefix = C('DB_PREFIX');
		//查找要执行的动作
		$this->_c = D ('Contentext');
		$where = array(
			'url' => $this->_urls['dburl'],
			'status' => '9'
		);
		$this->_data = $this->_c->field("`cid`")->where($where)->find();
		if (!empty($this->_data)) {
			$this->_action = 'show';
		} else {  //栏目页			
			if ($this->_urls['dburl'] == 'index' . C ('URL_HTML_SUFFIX')) {
				$this->_action = 'index';
			} else {
				if($this->isajax())
				{

				}else
				{
					$this->h404();
				}
			}
		}
	}
	
	/**
	 * @name分发操作
	 */
	 
	public function _empty() {
		if (ACTION_NAME == '_empty') {
			$this->h404();
		} else {  //初始化，分析得到要分发到的action			
			if (method_exists($this,$this->_action)) {				
				$this->_category_data = F ("category_".CATID);
				$this->{$this->_action}();
			}
		}
	}
	
	/**
	 * @name栏目页
	 * 
	 */
	public function index() {
		$in = &$this->in;	

		$arr = explode("/", REQUEST_FILE);
		$cate = M('category');
		$url = $cate->field('setting')->where("catdir='".$arr[0]."'")->select();

		eval("\$temp=".$url[0]['setting'].";");
		if($temp['template']['index'] == "admin.html"||$temp['template']['index'] == "admin_member.html"){
			// print_r($_SESSION);exit();
	    		if(!$_SESSION['user']){
	    			header("location:".__ROOT__."/blog_index/");exit();
	    		}
	    	}

	    	if($temp['template']['index'] == "admin.html"){
	    		$this->adm();
	    	}
	    	// if($temp['template']['index'] == "admin_member.html"){
	    	// 	$this->admmem();
	    	// }
    	$this->assign('role',$_SESSION['role']);
		//seo设置
		$seo['seotitle'] = $this->_category_data['seotitle'] ? $this->_category_data['seotitle'] : $this->_category_data['name'];
		$seo['seokeywords'] = $this->_category_data['seokeywords'] ? $this->_category_data['seokeywords'] : $this->_category_data['name'];
		$seo['seodescription'] = $this->_category_data['seodescription'] ? $this->_category_data['seodescription'] : $this->_category_data['description'];		
		//指定当前页面唯一链接			
		if ($this->_page > 1) {
			$seo['url'] = C ('SITEURL') . $this->_category_data['url'] . 'index_' . $this->_page . C('URL_HTML_SUFFIX');
		} else {
			$seo['url'] = C ('SITEURL') . $this->_category_data['url'];
		}
		//字符替换
		$seo = parent::meta_replace($seo);

		$member = M("info_member");
		$sql = "SELECT role,count(*)as number from fangfa_info_member where role <> '2' and class_name=(select class_name from fangfa_class_blog where id='".$_SESSION['class_id']."') GROUP BY role";
		$num = $member->query($sql);
		$this->assign('num',$num);

		$model = new model();
		$stud = $model->query("SELECT * from fangfa_info_member where role='0' and class_name=(select class_name from fangfa_class_blog where id='".$_SESSION['class_id']."')");
		$this->assign('stud',$stud);

		$this->assign('seo',$seo); //meta信息
		$this->assign('cat',$this->_category_data);	//栏目信息	
		$this->assign('p',$this->_page); //当前页码

		$mem = M('info_member')->where("username = '".$_SESSION['user']."'")->select();
		$this->assign('mem',$mem);

		$meminfo = M('info_member')->where("username='".$_SESSION['user']."'")->select();
		$this->assign("meminfo",$meminfo);

		$classname = $member->query("select class_name from fangfa_info_member where id='".$_SESSION['bid']."'");
		$this->assign('class',$classname[0]['class_name']);

		$this->display($this->_category_data['setting']['template']['index']);
	}
	/**
	 * 博客后台首页
	 */
	public function adm(){
		$info = M('info_member')->where("username = '".$_SESSION['user']."'")->select();
		$model = new model();
		$mems = $model->query("SELECT * from fangfa_info_member where username='".$_SESSION['user']."'");
		$artnum = M("article")->field("count(*) as num")->where("class_id = '".$_SESSION['class_id']."'")->find();
		$picnum = M("album_picture")->field("count(*) as num")->where("class_id = '".$_SESSION['class_id']."'")->find();
		$comnum1 = M("album_picture")->field("sum(pcomment) as num")->where("class_id = '".$_SESSION['class_id']."'")->select();
		$comnum2 = M("article")->field("sum(acomment) as num")->where("class_id = '".$_SESSION['class_id']."'")->select();
		$comnum = $comnum1[0]['num']+$comnum2[0]['num'];
		$count1 = M('article')->query("select SUM(count) as num from fangfa_article where class_id='".$_SESSION['class_id']."'");
		$count2 = M('album_picture')->query("select SUM(pcount) as num from fangfa_album_picture where class_id='".$_SESSION['class_id']."'");
		$count = $count1 + $count2;
		$arrs = explode("-", $mems[0]['birth']);
		$mems[0]['birth'] = implode("/", $arrs);

//最近动态
		$arpicom = $model->query("SELECT mem.class_name,mem.nickname,mem.role,ac.* from (select * FROM fangfa_album_comment UNION all select * from fangfa_article_comment) ac LEFT JOIN fangfa_info_member mem ON ac.user=mem.username where class_name=(select class_name from fangfa_class_blog where id='".$_SESSION['class_id']."') ORDER BY create_time desc");
		$this->assign('arpccom',$arpicom);
		// print_r($arpicom);exit();

		$this->assign('count',$count[0]['num']);
		$this->assign('comnum',$comnum);
		$this->assign('picnum',$picnum);
		$this->assign('mems',$mems);
		$this->assign('sum',$artnum);
		$this->assign('info',$info);
		$this->assign('role',$_SESSION['role']);
	}
	
	/**
	 * @name详细内容信息
	 * 
	 */
	public function show() {
		import('Pager',INCLUDE_PATH);
		$in = &$this->in;
		//查询具体记录的所有相关信息：  扩展表、统计表、tag
		$this->_category_data = F ("category_".CATID);
		$this->assign('cat',$this->_category_data);
		$this->assign('comment_open', C('CONTENT_COMMENT_OPEN')); //是否开启评论
		//获取数据
		$options = array(
			'where' => array('url'=>$this->_urls['dburl']),
		);		
		$data = $this->_c->get($options,'all');
		//seo设置
		$data = parent::meta_replace($data);
		$seo['seotitle'] = &$data['seotitle'];
		$seo['seokeywords'] = &$data['seokeywords'];
		$seo['seodescription'] = &$data['seodescription'];
		//内容分页
		/*$pager = new Pager($data['content'],$this->_page);			
		$data['pages'] = $pager->navbar($this->_category_data['url'] . $this->_urls['baseurl'] . '_{page}.html');
		$content = $pager->content();
		if (empty($content) && $this->_page != 1) {
			$this->h404();
		} else {
			$data['content'] = &$content;
		}*/
		import("ORG.Util.Page");
		$_comment = D("Comment");
		$comment['count'] = $_comment->where("newsid =".$data['cid']." and status=1")->count();
		$Page = new Page($comment['content'], 10);	
		$comment['pages'] = $Page->show();	
		$comment['info'] = $_comment->where("newsid =".$data['cid']." and status=1")->order("id DESC")->select();
		
		
		
//		dump($data);exit;
		//指定当前页面唯一链接
		if ($this->_page > 1) {
			$seo['url'] = C ('SITEURL') . $this->_category_data['url'] . str_replace(C('URL_HTML_SUFFIX'),'',$this->_urls['baseurl']) . '_' . $this->_page . C('URL_HTML_SUFFIX');
		} else {
			$seo['url'] = C ('SITEURL') . $this->_category_data['url'] . $data['url'];
		}
		//上一条  下一条
		$_content = M ('Content');
		$pre = $_content->where("`cid` < ".$data['cid']." and `status` = 9 and `catid`=".$data['catid']."")->order('cid DESC')->limit('1')->find();
		$next = $_content->where("`cid` > ".$data['cid']." and `status` = 9 and `catid`=".$data['catid']."")->order('cid asc')->limit('1')->find();
		$cat = $this->_category_data;
		
		if (!empty($pre)) {
			$data['pre_title'] = $pre['title'];
			$data['pre_url']   = $cat['url'] . $pre['url'];
		} else {
			$data['pre_title'] = '没有了';
			$data['pre_url']   = '#';
		}
		if (!empty($next)) {
			$data['next_title'] = $next['title'];
			$data['next_url']   = $cat['url'] . $next['url'];
		} else {
			$data['next_title'] = '没有了';
			$data['next_url']   = '#';
		}
		
		$this->assign('seo',$seo);
		$this->assign('comment',$comment);
		$this->assign('data',$data);
		$this->display($this->_category_data['setting']['template']['show']);
	}
	
	/**
	 * @namePHP方式下载指定文件
	 * url 链接： 
	 */
	public function down() {
		$in = &$this->in;
		//此内容的栏目
		$cid = intval($in['cid']);
		!$cid && $this->message('没有您要下载文件！', '', 3);
		!$in['hash'] && $this->message('下载连接失败，请重新下载！', '', 3);		
		$_c = D('Contentext','front');
		$download = $_c->get($cid, 'all');
		//判断下载权限
		if (isset($download[$download['download_field_name']]['permission_status']) && !$download[$download['download_field_name']]['permission_status']) {
			$this->message('抱歉，您没有权限下载此文件！', '', 3);
		}
		if (IS_WIN) {  //对于WIN服务器，则转换编码，否则file_exists  会受编码影响
			$download[$download['download_field_name']]['value'] = auto_charset($download[$download['download_field_name']]['value'], 'utf-8', 'gbk');
		}
		if((time()-intval(base64_decode($in['hash']))) < 3600*8) {   //8个小时后过期
			if (substr($download[$download['download_field_name']]['value'],0,7) == 'http://' || substr($download[$download['download_field_name']]['value'],0,6) == 'ftp://') {
				$filename = &$download[$download['download_field_name']]['value'];	
			} elseif (file_exists(FANGFACMS_ROOT . C('UPLOAD_DIR') . $download[$download['download_field_name']]['value'])) {
				$filename = FANGFACMS_ROOT . C('UPLOAD_DIR') . $download[$download['download_field_name']]['value'];
			} elseif (strpos($download[$download['download_field_name']]['value'], '|files') || strpos($download[$download['download_field_name']]['value'], '|media')) {
				$file_info = explode('|', $download[$download['download_field_name']]['value']);
				$filename = FANGFACMS_ROOT  . C('UPLOAD_DIR') . $file_info[1];
			} else {
				$this->h404(L ('文件不存在！'));
			}			
			if(!file_exists($filename)) {
				$this->h404(L ('文件不存在！'));
			} else {
				//TODO  读取文件，发送到客户端	
				import('ORG.Net.Http');
				Http::download($filename);
			}			
		} else {
			$this->h404(L ('下载连接失败，请重新下载！'));
		}
	}
	
	/**
	 * @name浏览次数
	 *
	 */
	public function count() {
		$in = &$this->in;
		if (!$in['cid'] || !$in['type']) exit ();
		$_count = M ('ContentCount');
		
		$data = $_count->field("`{$in['type']}`")->where("`cid`='{$in['cid']}'")->find();

		if(empty($data))
		{
			$_count->add(array('cid'=>$in['cid']));
			$data = $_count->field("`{$in['type']}`")->where("`cid`='{$in['cid']}'")->find();
		}
		if (isset($data[$in['type']])) {
			
			if ($in['type'] == 'hits') {
				echo (int)$data[$in['type']]+1;
				$_count->execute("update __TABLE__ set `hits`=`hits`+1 where `cid`='{$in['cid']}' limit 1");
			}else if($in['type'] == 'comments')
			{
				echo (int)$data[$in['type']];
			}else if($in['type'] == 'comments_checked')
			{
				echo (int)$data[$in['type']];
			}
			exit ();
		}
	}
}