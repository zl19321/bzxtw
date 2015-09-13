<?php
// +----------------------------------------------------------------------
// | FangFa Net [ http://www.fangfa.net ]
// +----------------------------------------------------------------------
// | File: AdModel.class.php
// +----------------------------------------------------------------------
// | Date: 2010 14:57:19
// +----------------------------------------------------------------------
// | Author: fangfa <1364119331@qq.com>
// +----------------------------------------------------------------------
// | 文件描述: 广告Model
// +----------------------------------------------------------------------

class AdModel extends Model {
	
	/**
	 * 查询的后置方法，会在find()之后自动以引用方式调用
	 */
	protected function _after_find(&$result, $options = '') {
		parent::_after_find ( $result, $options );
		if (isset ( $result ['setting'] )) {
			$result ['setting'] = @eval ( "return {$result ['setting']};" );			
		}		
	}
	
	/**
	 * findAll 或者 select 之后自动调用的数据处理方法
	 * @param unknown_type $resultSet
	 */
	protected function _after_select(&$resultSet, $options = '') {
		parent::_after_select ( $resultSet, $options );
		if (is_array ( $resultSet )) {
			foreach ( $resultSet as $k => $v ) {
				if (isset ( $resultSet [$k] ['setting'] )) {
					$resultSet [$k] ['setting'] = @eval ( "return {$resultSet [$k] ['setting']};" );					
				}				
			}
		}
	}
	
	/**
	 * add方法insert之前处理方法，会在insert之前自动以引用方式调用
	 * @param unknown_type $data
	 */
	protected function _before_insert(&$data, $options) {
		parent::_before_insert ( $data, $options );		
		!empty($data ['setting']) && $data ['setting'] = var_export ( $data ['setting'], true );							
		$data ['update_time'] = date('Y-m-d H:i:s');		
		return true;
	}
	
	/**
	 * save方法update数据之前的处理方法，会在update数据之前自动以引用方式调用
	 * @param unknown_type $data
	 */
	protected function _before_update(&$data, $options) {
		parent::_before_update ( $data, $options );
		return $this->_before_insert ( $data );
	}
	
	/**
	 * 缓存广告信息
	 * @param int $aid
	 * @param array $data
	 */
	public function cache($aid,$data = '') {
		if ($data === null) { //删除缓存
			F ( 'ad_' . $aid, null, ALL_CACHE_PATH . 'ads/' );
			return true;
		}		
		$data = $this->find($aid);
		if (is_array($data)) {
			if ($data['aid'] == $aid) {
				F ( 'ad_' . $aid, $data, ALL_CACHE_PATH . 'ads/' );
				return true;
			}
		}
		return false;
	}
	
	/**
	 * 缓存所有广告信息
	 */
	public function cacheAll() {
		$data = $this->findAll();
		if (is_array($data)) {
			foreach ($data as $v) {
				F('ad_'.$v['aid'],$v, ALL_CACHE_PATH . 'ads/' );
			}
			return true;
		}
		return false;
	}
	
}

?>