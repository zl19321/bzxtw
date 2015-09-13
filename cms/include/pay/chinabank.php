<?php
// +----------------------------------------------------------------------
// | FangFa Net [ http://www.fangfa.net ]
// +----------------------------------------------------------------------
// | File: chinabank.php
// +----------------------------------------------------------------------
// | Date: 2010
// +----------------------------------------------------------------------
// | Author: fangfa <1364119331@qq.com>
// +----------------------------------------------------------------------
// | 文件描述: 网银在线支付封装
// +----------------------------------------------------------------------
class chinabank {
	var $err = '';
	/**
	 * 生成支付代码
	 * @param   array   $order      订单信息
	 * @param   array   $payment    支付方式信息
	 */
	function get_code($order, $payment) {
		$data_vid = trim ( $payment ['chinabank_account'] );
		$data_orderid = $order ['order_sn'];
		$data_vamount = $order ['order_amount'];
		$data_vmoneytype = 'CNY';
		$data_vpaykey = trim ( $payment ['chinabank_key'] );
		$data_vreturnurl = return_url ( 'chinabank' );
		
		$v_ordername = trim ( $order ['contactname'] ); // 订货人姓名
		$v_ordertel = trim ( $order ['telephone'] ); // 订货人电话
		$v_orderemail = trim ( $order ['email'] ); // 订货人邮件
		$remark1 = htmlspecialchars ( $order ['remark1'] ); // 备注
		

		$MD5KEY = $data_vamount . $data_vmoneytype . $data_orderid . $data_vid . $data_vreturnurl . $data_vpaykey;
		$MD5KEY = strtoupper ( md5 ( $MD5KEY ) );
		
		$def_url = '<form style="text-align:left;" method=post action="https://pay3.chinabank.com.cn/PayGate" target="_blank">';
		$def_url .= "<input type='hidden' name='v_mid' value='" . $data_vid . "'>";
		$def_url .= "<input type='hidden' name='v_oid' value='" . $data_orderid . "'>";
		$def_url .= "<input type='hidden' name='v_amount' value='" . $data_vamount . "'>";
		$def_url .= "<input type='hidden' name='v_moneytype'  value='" . $data_vmoneytype . "'>";
		$def_url .= "<input type='hidden' name='v_url'  value='" . $data_vreturnurl . "'>";
		$def_url .= "<input type='hidden' name='v_ordername'  value='" . $v_ordername . "'>";
		$def_url .= "<input type='hidden' name='v_ordertel'  value='" . $v_ordertel . "'>";
		$def_url .= "<input type='hidden' name='v_orderemail'  value='" . $v_orderemail . "'>";
		$def_url .= "<input type='hidden' name='remark1'  value='" . $remark1 . "'>";
		$def_url .= "<input type='hidden' name='v_md5info' value='" . $MD5KEY . "'>";
		$def_url .= "<input type=submit value='立即使用网银在线支付'>";
		$def_url .= "</form>";
		
		return $def_url;
	}
	
	/**
	 * 响应操作
	 */
	function respond() {
		$payment = get_payment ( basename ( __FILE__, '.php' ) );
		echo $v_oid = trim ( $_POST ['v_oid'] );
		$v_pmode = trim ( $_POST ['v_pmode'] );
		$v_pstatus = trim ( $_POST ['v_pstatus'] );
		$v_pstring = trim ( $_POST ['v_pstring'] );
		$v_amount = trim ( $_POST ['v_amount'] );
		$v_moneytype = trim ( $_POST ['v_moneytype'] );
		$remark1 = trim ( $_POST ['remark1'] );
		$remark2 = trim ( $_POST ['remark2'] );
		$v_md5str = trim ( $_POST ['v_md5str'] );
		$total = floatval ( $v_amount );
		
		$key = $payment ['chinabank_key'];
		
		$md5string = strtoupper ( md5 ( $v_oid . $v_pstatus . $v_amount . $v_moneytype . $key ) );
		
		///* 检查秘钥是否正确 */
		if ($v_md5str == $md5string) {
			if ($v_pstatus == '20') {
				if (changeorder ( $v_oid )) {
					return true;
				}
			}
		} else {
			$this->err = '校验失败，若您的确已经在网关处被扣了款项，请及时联系店主，并且请不要再次点击支付按钮(原因：错误的签名)';
			return false;
		}
	}
}

?>