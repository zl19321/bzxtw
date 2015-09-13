<?php
// +----------------------------------------------------------------------
// | FangFa Net [ http://www.fangfa.net ]
// +----------------------------------------------------------------------
// | File: FemailAction.class.php
// +----------------------------------------------------------------------
// | Date: 2011-01-27 14:36
// +----------------------------------------------------------------------
// | Author: 孙斌 <sunyichi@163.com>
// +----------------------------------------------------------------------
// | 文件描述: 发送邮件
// +----------------------------------------------------------------------

/**
 * @name 发送邮件
 *
 */
class FemailAction extends FbaseAction {
	
	/**
	 * @name发送邮件
	 */
	public function index() {
		$in = &$this->in;
		import('SendMail',INCLUDE_PATH);
	    $_sendmail = get_instance_of('SendMail');
	    $_mail_server = C('MAIL_SERVER');
	    $_mail_port = C('MAIL_PORT');
	    $_mail_user = C('MAIL_USER');
	    $_mail_password = C('MAIL_PASSWORD');
	    $_mail_type = C('MAIL_TYPE');
	    $_sendmail->set($_mail_server, $_mail_port, $_mail_user, $_mail_password, $_mail_type);
	    $content = "
	    亲爱的站长：您好！<br /><br />
	    　　以下为客户提交的在线加盟意向信息：<br />
	    客户姓名：{$in['name']}<br />
		联系方式：{$in['phone']}<br />
		客户邮箱：{$in['email']}<br />
		客户地址：{$in['addr']}<br />
		意向加盟的城市：{$in['city']}<br />
		特别说明：{$in['description']}<br />
	    ";
	    $url = $in['forward']?$in['forward']:C('SITEURL');
	    if($_sendmail->send($in['sendemail'], $in['subject'], $content, $in['email'])){
	    	$this->message(L("您的在线加盟意向信息已提交到站长邮箱，请等待回复！"), $url);
	    }
	    else{
	    	$this->message(L("您的在线加盟意向信息提交失败，请稍后再试！"), $url);
	    }
	    exit;
	}
}
?>