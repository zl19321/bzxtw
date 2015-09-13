<?php
// +----------------------------------------------------------------------
// | FangFa Net [ http://www.fangfa.net ]
// +----------------------------------------------------------------------
// | File: TagModel.class.php
// +----------------------------------------------------------------------
// | Date: 2010-5-7
// +----------------------------------------------------------------------
// | Author: fangfa <1364119331@qq.com>
// +----------------------------------------------------------------------
// | 文件描述: 单页Model
// +----------------------------------------------------------------------

class PageModel extends Model{
	
	
	
	/**
	 * 栏目设置、非直接操作，供栏目创建和修改的时候调用
	 * 如果模块不需要进行特殊设置，则直接返回空字符串
	 * @param $data 栏目数据库记录信息
	 * @param $catid 栏目ID
	 */
	public function setting($data = array(),$catid = '',$parentid = '') {
		$html = '';
		$data = array ();
		if (empty ( $data ) && ( int ) $catid) {
			$data = D ( 'Category' )->find ( $catid );
		} elseif ($parentid) {
			$data['parentid'] = $parentid;
		}
		//调用相应的widget
		$html = W ( 'CategoryPageAdd', $data, true );
		return $html;
	}
	
	
}


?>