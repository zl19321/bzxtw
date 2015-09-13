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
// | 文件描述: 推荐位数据表model
// +----------------------------------------------------------------------

class PositionModel extends Model{
	
	/**
	 * 缓存推荐位信息
	 * @param int $moduleid
	 * @param array $data
	 */
	public function cache($posid,$data = null) {		
		if (empty($data) || !is_array($data)) {
			$data = $this->find($posid);
		}
		if (is_array($data)) {
			if ($data['posid'] == $posid) {
				F ( 'position_' . $posid, $data );
				return true;
			}
		}
		return false;
	}
	
	/**
	 * 删除推荐位
	 */
	public function delete($posid)
	{
		if (parent::delete($posid)) {
			//清楚缓存
			F('position_'.$posid, null, ALL_CACHE_PATH.'cache/');
			//删除关联信息
			parent::execute('DELETE FROM `' . C('DB_PREFIX') . 'content_position` WHERE posid='.$posid);
			return true;
		} else return false;
	}
}

?>