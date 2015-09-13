<?php
/**
 * 投票 模型
 *
 */
class VoteModel extends Model 
{
	public function setting($data = array(),$catid = '', $parentid= '') {
		$html = '';
		if (empty ( $data ) && ( int ) $catid) {
			$data = D ( 'Category' )->find ( $catid );		
		} elseif ($parentid) {
			$data['parentid'] = $parentid;
		}
		$data ['controller'] = 'fvote';
		//调用相应的widget
		$html = W ( 'CategoryVoteAdd', $data, true );
		return $html;
	}
}