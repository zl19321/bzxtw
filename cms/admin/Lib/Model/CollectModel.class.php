<?php
// +----------------------------------------------------------------------
// | FangFa Net [ http://www.fangfa.net ]
// +----------------------------------------------------------------------
// | File: Fcollect.class.php
// +----------------------------------------------------------------------
// | Date: 2011-11-02
// +----------------------------------------------------------------------
// | Author: mark <376727439@qq.com>
// +----------------------------------------------------------------------
// | 文件描述: 信息采集管理
// +----------------------------------------------------------------------

class CollectModel extends Model {

	protected function _before_insert(&$data){
		$data['replace'] = var_export ( $data ['replace'], true );
		$data['create_time'] = time();
	}
	protected function _after_select(&$data){
		$_category = D("Category");
		
		foreach ($data as $k=>$t){
			$data[$k]['catid_name'] = $_category->where("catid=".$t['into_catid'])->getField("name");
			if (!empty($t['replace'])) {
				$data[$k]['replace'] = eval("return {$t[replace]};");
			}
			$data[$k]['create_time'] = date("Y-m-d",$t['create_time']);
			if(!empty($t['update_time']) && $t['update_time'] != 0){
				$data[$k]['update_time'] = date("Y-m-d",$t['update_time']);
			}else {
				$data[$k]['update_time'] = "未采集";
			}
		}
	}
	protected function _after_find(&$data){
		$_category = D("Category");
		$data['catid_name'] = $_category->where("catid=".$data['into_catid'])->getField("name");
		if (!empty($data['replace'])) {
			$data['replace'] = eval("return {$data[replace]};");
		}
		$data['create_time'] = date("Y-m-d",$data['create_time']);
		if(!empty($data['update_time']) && $data['update_time'] != 0){
			$data['update_time'] = date("Y-m-d",$data['update_time']);
		}
	}
	

}
?>