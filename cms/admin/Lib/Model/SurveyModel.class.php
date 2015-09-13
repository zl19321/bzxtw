<?php
/**
 * 问卷 模型
 *
 */
class SurveyModel extends Model 
{
	public function _facade(&$data)
	{
		$data['create_time']   = strtotime($data['create_time']);
		$data['update_time']   = strtotime($data['update_time']);
		return $data;
	}

	public function setting($data = array(),$catid = '', $parentid= '') {
		$html = '';
		if (empty ( $data ) && ( int ) $catid) {
			$data = D ( 'Category' )->find ( $catid );		
		} elseif ($parentid) {
			$data['parentid'] = $parentid;
		}
		$data ['controller'] = 'fsurvey';
		//调用相应的widget
		$html = W ( 'CategorySurveyAdd', $data, true );
		return $html;
	}
}