<?php
/**
 * 留言板模块
 */
class GuestbookModel extends Model 
{
	public function _facade(&$data)
	{
		$data['addtime']   = strtotime($data['addtime']);
		$data['replytime'] = strtotime($data['replytime']);
		
		return $data;
	}
	
	public function setting($data = array(),$catid = '', $parentid = '') {
		$html = '';
		if (empty ( $data ) && ( int ) $catid) {
			$data = D ( 'Category' )->find ( $catid );		
		} elseif ($parentid) {
			$data['parentid'] = $parentid;
		}
		$data ['controller'] = 'fguestbook';
		//调用相应的widget
		$html = W ( 'CategoryGuestbookAdd', $data, true );
		return $html;
//		$_model = D ( 'Model' );
//		$module_data = $_model->relation ( true )->find ( $data ['modelid'] );
//		if (is_array ( $module_data )) {
//			$data ['controller'] = ucfirst ( $module_data ['module'] ['controller'] );
//			//调用相应的widget
//			$html = W ( 'CategoryGuestbookAdd', $data, true );
//			return $html;
//		} else {
//			return false;
//		}
	}
}