<?php
/**
 * 人才招聘 模型
 *
 */
class JobModel extends Model 
{
	protected function _after_select(&$resultSet,$options)
	{
		if (is_array($resultSet)) {
			foreach ($resultSet AS &$v) {
				$v['end_time'] = date('Y-m-d', $v['end_time']);
				$v['create_time'] = date('Y-m-d', $v['create_time']);
				$v['update_time'] = date('Y-m-d', $v['update_time']);
			}
		}
		
		return $resultSet;
	}
	
	//处理查询后logo地址
	protected function _after_find(&$result,$options) {
		$result['end_time'] = date('Y-m-d', $result['end_time']);
		$result['create_time'] = date('Y-m-d', $result['create_time']);
		$result['update_time'] = date('Y-m-d', $result['update_time']);
		return $result;
	}
	
	public function setting($data = array(),$catid = '', $parentid = '') {
		$html = '';
		if (empty ( $data ) && ( int ) $catid) {
			$data = D ( 'Category' )->find ( $catid );		
		} elseif ($parentid) {
			$data['parentid'] = $parentid;
		}
		$data ['controller'] = 'fjob';
		//调用相应的widget
		$html = W ( 'CategoryJobAdd', $data, true );
		return $html;
	}
}