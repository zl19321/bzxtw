<?php
/**
 * 二维码 模型
 *
 */
class BrcodeModel extends Model 
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