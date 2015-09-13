<?php
/**
 * 问答 模型
 *
 */
class AskModel extends Model 
{
	public function setting($data = array(),$catid = '',$parentid = '') {
		$html = '';
		if (empty ( $data ) && ( int ) $catid) {
			$data = D ( 'Category' )->find ( $catid );		
		} elseif ($parentid) {
			$data['parentid'] = $parentid;
		}
		$data ['controller'] = 'fask';
		//调用相应的widget
		$html = W ( 'CategoryAskAdd', $data, true );
		return $html;
	}
	
	/**
	 * 更新回答总数
	 */
	public function updateAnswerNum($ask_id)
	{
		$ask_answer_num = $this->where('parentid='.$ask_id)->count();
		$data['ask_id'] = $ask_id;
		$data['answernum'] = $ask_answer_num;
		return $this->save($data);
	}
}