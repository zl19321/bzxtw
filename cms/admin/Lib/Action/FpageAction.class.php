<?php
// +----------------------------------------------------------------------
// | FangFa Net [ http://www.fangfa.net ]
// +----------------------------------------------------------------------
// | File: FdbAction.class.php
// +----------------------------------------------------------------------
// | Date: 2010-5-5
// +----------------------------------------------------------------------
// | Author: fangfa <1364119331@qq.com>
// +----------------------------------------------------------------------
// | 文件描述: 单页处理
// +----------------------------------------------------------------------


defined ( 'IN_ADMIN' ) or die ( 'Access Denied' );
/**
 * @name 单页处理
 *
 */
class FpageAction extends FbaseAction {
	
	/**
	 * @name 单页处理
	 */
	public function manage() {
		$in = &$this->in;
		$_page = D ('Page');
		if ($this->ispost()) {
			//令牌验证
			if (!$_page->autoCheckToken($in)) $this->message('<font class="red">请不要非法提交或者重复刷新页面！</font>');
			$in['info']['catid'] = &$in['catid'];
			$in['info']['user_id'] = $_SESSION['userdata']['user_id'];
			$in['info']['username'] = $_SESSION['userdata']['username'];
			$in['info']['update_time'] = time();
			if ($in['info']['pageid']) {
				if (false !== $_page->save($in['info'])) {
					$this->message('<font class="green">保存成功！</font>');
				} else {
					$this->message('<font class="red">保存失败！</font>');
				}
			} else {
				$in['info']['create_time'] = time();
				if (false !== $_page->add($in['info'])) {
					$this->message('<font class="green">保存成功！</font>');
				} else {
					$this->message('<font class="red">保存失败！</font>');
				}
			}
		}
		$category_data = D('Category')->find($in ['catid']);
		$this->assign('cat',$category_data);
		$this->assign('data',$_page->where("`catid`='{$in['catid']}'")->find());
		$this->assign('forward',U('fpage/manage?catid='.$in['catid']));
		$this->display();
	}
}
?>