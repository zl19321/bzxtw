<?php
// +----------------------------------------------------------------------
// | FangFa Net [ http://www.fangfa.net ]
// +----------------------------------------------------------------------
// | File: Pay.class.php
// +----------------------------------------------------------------------
// | Date: 2010
// +----------------------------------------------------------------------
// | Author: fangfa <1364119331@qq.com>
// +----------------------------------------------------------------------
// | 文件描述: 统一支付接口；支持网银在线、财付通、支付宝、快钱、易宝、NPS
// +----------------------------------------------------------------------

class Pay {
	
	/**
	 * 
	 * 支付配置
	 * @var array
	 */
	protected $_config = array ();
	
	/**
	 * 支付封装实例
	 * 
	 * @var object
	 */
	protected $_payHandle = '';
	
	/**
	 * 构造函数
	 * 
	 * @param string $paytype 支付方式
	 * @param array $config 支付配置
	 */
	public function __construct($paytype, $config) {
		
	}
	
	/**
	 * 设置当前使用的支付接口信息
	 * 
	 * @param string $paytype 支付方式
	 * @param array $config 支付配置
	 */
	public function setPayType($paytype, $config) {
		
	}
	
	/**
	 * Magic: 方法调用
	 *
	 * @param string $method
	 * @param array $args
	 **/
	public function __call($method, $args) {
		
	}
	
}

?>