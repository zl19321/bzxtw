<?php
/**
 * 人才招聘 模型
 *
 */
class SalenetModel extends Model 
{
	protected function _after_select(&$resultSet,$options)
	{
		if (is_array($resultSet)) {
			foreach ($resultSet AS &$v) {
				$v['create_time'] = date('Y-m-d', $v['create_time']);
				$v['update_time'] = date('Y-m-d', $v['update_time']);
			}
		}
		
		return $resultSet;
	}
	
	//处理查询后logo地址
	protected function _after_find(&$result,$options) {
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
		$data ['controller'] = 'fsalenet';
		//调用相应的widget
		$html = W ( 'CategorySalenet', $data, true );
		return $html;
	}
}