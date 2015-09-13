<?php
// +----------------------------------------------------------------------
// | FangFa Net [ http://www.fangfa.net ]
// +----------------------------------------------------------------------
// | File: OrderModel.class.php
// +----------------------------------------------------------------------
// | Date: 2010
// +----------------------------------------------------------------------
// | Author: fangfa <1364119331@qq.com>
// +----------------------------------------------------------------------
// | 文件描述: 订单模型
// +----------------------------------------------------------------------

class OrderModel extends Model {
	
	protected function _after_find(&$result, $options) {
		 $result['ordername'] && $result['ordername'] = eval("return {$result['ordername']};");
	}
	
	protected function _after_select(&$resultSet, $options) {
		if (is_array($resultSet)) {
			foreach ($resultSet as $k=>$v) {
				$resultSet [$k] ['ordername'] = eval("return {$resultSet [$k]['ordername']};");
			}
		}
	}
	
	protected function _before_insert(&$data, $options) {  //进行数据校验，查看价格与商品是否对应
		$orderName = $data['ordername'];
		if (is_string($orderName)) {
			$orderNameArray = $this->deOrderName($orderName);
		} else {
			$orderNameArray = &$orderName;
		}
		if (is_array($orderNameArray) && !empty($orderNameArray)) { //计算订单中物品的总数量
			$data['number'] = 0;
			foreach ($orderNameArray as $k=>$order) {
				if (empty($order['name'])) {
					unset($orderNameArray[$k]);
				} else {
					$data['number'] += (int)$order['number'];
					$data['total'] += $order['number']*$order['price'];
				}				
			}
		}		
		$data['ordername'] = var_export($orderNameArray, true);
		//数据过滤
		$this->dataFilter($data);
//		dump($data);exit;
		if (!$this->check($data)) {
			return false;
		}		
	}
	
	
	protected function _before_update(&$data, $options) {
		$this->_before_insert($data, $options);
	}
	
	/**
	 * 数据过滤
	 * Enter description here ...
	 * @param unknown_type $data
	 */
	protected function dataFilter(&$data) {
		$field = array('linkman','telephone','mobile','address','message','remark');
		foreach($field as $v) {
			if (isset($data[$v])) {
				$data[$v] = htmlspecialchars($data[$v]);
			}
		}
		$data['postcode'] = intval($data['postcode']);
	}
	
	/**
	 * 检查数据合法性
	 * Enter description here ...
	 * @param unknown_type $data
	 */
	protected function check($data) {
		if (!$this->checkData($data['ordername'])) {			
			return false;
		}		
		if (!$data['number']>0) {
			$this->error .= L ('物品总数不能少于0');
			return false;
		}
		return true;		
	}
	
	/**
	 * 
	 * 将orderName数组转换成存储到数据库的字符串
	 * @param string $data
	 */
	protected function enOrderName($data) {
		$return = '';
		if (is_array($data)) {
			foreach ($data as $k=>$v) {
				if (is_array($v)) {
					$data[$k] = implode(',', $v);
				} else {
					return ;
				}
			}
			$return = implode('|', $data);
		}
		return $return;
	}
	
	/**
	 * 
	 * 将orderName字符串格式化成数组
	 * @param array $data
	 */
	protected function deOrderName($data) {
		if (is_string($data)) {
			$data = explode('|', $data);
			if (is_array($data)) {
				foreach ($data as $k=>$v) {
					$v = explode(',', $v);
					if (is_array($v)) {
						$data[$k] = array(
							'keyid' => intval($v[0]),
							'name' => $v[1],
							'price' => $v[2],
							'pageurl' => $v[3],
							'number' => intval($v[4]),
						);
					}
				}
				return $data;
			}
		}
		return false;
	}
	
	
	/**
	 * 检查订单数据，由客户端提交过来的数据需要进行数据上的验证
	 * 包括价格，cid，是否与系统一致
	 * 
	 * @param string $orderName
	 */
	public function checkData($orderArray) {
		$_mContent = D ('ContentProduct','admin');		
		foreach ($orderArray as $v) {			
			if (!$v['cid']) {
				$this->error .= L ('订单中的物品数据信息错误！');
				return false;
			} else {
				$where = array(
					'cid' => $v['cid']
				);
				$cData = $_mContent->field("`cid`,`price`")->where($where)->find();				
				if (!$cData) {
					$this->error .= L ('要订购的物品不存在或者已经下架！');
					return false;
				} else {
//					if ($cData['price'] != $v['price']) {
//						$this->error .= L ('要订购的物品价格错误！');
//						return false;
//					}
				}
			}
		}
		return true;
	}
	
	
	/**
	 * 生成一个订单号
	 * 
	 * @param string $keyid 标识前缀，如 C25 
	 * @param string $other 
	 */
	public function getOrderNum($keyid = "C") {
		$num = $keyid;
		$num .= date('YmdHis');
		$num .= rand(100, 999);
		return strtoupper($num);
	}
	
	/**
	 * (non-PHPdoc)
	 * @see Model::add()
	 */
	public function add($data,$options) {
		$data['create_time'] = time();
		$data['update_time'] = time();		
		$data['ip'] = get_client_ip();		
		return parent::add($data,$options);
	}
	
	public function save($data,$options) {
		$data['update_time'] = time();
		parent::save($data,$options);
	}
}