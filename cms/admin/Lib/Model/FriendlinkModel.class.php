<?php
/**
 * 友情链接 模型
 *
 */
class FriendlinkModel extends Model 
{
	protected $_auto = array(
		array('created', 'time', 1, 'function'),
	);
	
	
	protected function _after_select(&$resultSet,$options)
	{
		return $resultSet;
	}
	
	//处理查询后logo地址
	protected function _after_find(&$result,$options) {
		return $result;
	}
}