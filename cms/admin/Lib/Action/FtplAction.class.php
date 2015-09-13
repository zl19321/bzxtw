<?php
// +----------------------------------------------------------------------
// | FangFa Net [ http://www.fangfa.net ]
// +----------------------------------------------------------------------
// | File: FtagAction.class.php
// +----------------------------------------------------------------------
// | Date: 2010-4-28
// +----------------------------------------------------------------------
// | Author: fangfa <1364119331@qq.com>
// +----------------------------------------------------------------------
// | 文件描述: 网站模板维护
// +----------------------------------------------------------------------

defined('IN_ADMIN') or die('Access Denied');
/**
 * @name 网站模板维护
 *
 */
class FtplAction extends FbaseAction {
	
	protected $_theme = 'default';
	
	/**
	 * 文件操作类实例对象
	 * @var object
	 */
	protected $_dir = '';
	
	/**
	 * 主题目录
	 * @var unknown_type
	 */
	protected $_themePath = '';
	
	/**
	 * @name网站模板初始化数据
	 */
	protected function _initialize() {
		parent::_initialize();
		$in = &$this->in;
		if ($in['theme']) {
			$this->_theme = $in['theme'];
		}
		$this->_themePath = FANGFACMS_ROOT . 'public/theme/' . $this->_theme . '/';
		
		$in['folder'] = urldecode($in['folder']);
		if ($in['folder']) {
			$this->assign('folder',$in['folder']);
			$this->assign('theme',$this->_theme);
			import('ORG.Io.Dir');
			$this->_dir = new Dir($this->_themePath . $in['folder']);
			//上一级
			if (false !== strpos($in['folder'],'/')) {
				$folders = explode('/',$in['folder']);
				array_pop($folders);
				$this->assign('pfolder',implode('/',$folders));
			}
		} else {
			$this->assign('theme',$this->_theme);
			import('ORG.Io.Dir');
			$this->_dir = new Dir($this->_themePath );
		}
		define('TPLNOTE_PATH',ALL_CACHE_PATH . 'tplnote/');
	}
	
	/**
	 * @name模板管理
	 */
	public function manage() {
		$in = &$this->in;
		$scanPath = $this->_themePath . $in['folder'];
		if (file_exists($scanPath)) {			
			$fileLists = $this->_dir->toArray();
//			dump($fileLists);exit;
			if (is_array($fileLists) && !empty($fileLists)) {
				foreach ($fileLists as $k=>$v) {
					if (!$v['isDir']) {
						$fileLists[$k]['note'] = F ($v['filename'].'_'.md5($v['pathname']),'',TPLNOTE_PATH);
					}
				}
			}
			usort($fileLists, array($this, 'dirSort'));
			$this->assign('data',$fileLists);
			$this->display();
		} else {
			$this->message('<font class="red">目录不存在！</font>');
		}
	}
	
	/**
	 * @name模板编辑
	 */
	public function edit() {
		$in = &$this->in;
		if ($in['ajax']) $this->_ajax_edit();
		$in['filename'] = urldecode($in['filename']);
		if ($this->ispost()) { //保存模板
			$file = $in['info']['path'] . '/' . $in['info']['filename'];
			$file = realpath($file);
			$content = htmlspecialchars_decode($in['info']['content']);
//			echo $content;exit;
			file_put_contents($file,$content);
			F ($in['info']['filename'].'_'.md5($file),$in['info']['note'] ? $in['info']['note'] : null,TPLNOTE_PATH);
			$this->message('<font class="green">模板保存成功！</font>');
		}
		$file = realpath($this->_themePath . $in['filename']);
		$this->assign('filename',$in['filename']);
		if (filetype($file) == 'file') {			
			$data['filename'] = basename($file);
			$data['path'] = dirname($file);
			$data['note'] = F ($data['filename'].'_'.md5($file),'',TPLNOTE_PATH);
			$data['info'] = pathinfo($file);
			$this->assign('data',$data);
			$this->display();
		} else {
			$this->message('<font class="red">文件不存在！</font>');
		}		
	}
	
	/**
	 * @name处理编辑模板时候的ajax请求
	 */
	protected function _ajax_edit () {
		$in = &$this->in;
		switch ($in['ajax']) {
			case 'savetplnote':  //保存模板说明，直接保存在缓存中
				$file = realpath($this->_themePath . substr($in['filename'],4));
				if ( F (basename($file).'_'.md5($file),$in['note'],TPLNOTE_PATH) ) {
					echo $in['note'];
				}
				break;
			case 'getcontent':
				$file = realpath($this->_themePath . $in['filename']);				
				$content = file_get_contents($file);
				echo $content;
				break;
		}
		exit ();
	}
	
	
	/**
	 * @name删除模板
	 */
	public function delete() {
		$in = &$this->in;
		$file = realpath($this->_themePath . $in['filename']);
		if (file_exists($file)) {
			unlink($file);
			$this->message('<font class="green">删除成功！</font>');
		} else {
			$this->message('<font class="red">删除失败！</font>');
		}
	}
	
	/**
	 * @name清除模板缓存
	 */
	public function cache() {
		import('ORG.Io.Dir');
		$_dir = new Dir();
		$_dir->clearDir(RUNTIME_PATH . 'templates_c');
		$_dir->clearDir(ALL_CACHE_PATH . 'front/runtime/templates_c');
		$this->message('<font class="green">模板缓存清除成功！</font>', U('ftpl/manage'));
	}
	
	/**
	 * @name标签生成向导
	 */
	public function create_tag() {
		$in = &$this->in;
		
		$this->display();
	}
	
	/**
	 * @name对模板文件进行排序
	 *
	 * @param unknown_type $a
	 * @param unknown_type $b
	 * @return unknown
	 */
	protected function dirSort($a, $b)
	{
		if ($a['type'] == 'dir' && $b['type'] == 'file') {
			return false;
		} else if ($a['type'] == 'file' && $b['type'] == 'dir') {
			return true;
		} else {
			return strcmp($a["filename"], $b["filename"]);
		}
	}
}
?>