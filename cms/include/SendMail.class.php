<?php
// +----------------------------------------------------------------------
// | FangFa Net [ http://www.fangfa.net ]
// +----------------------------------------------------------------------
// | File: Html.class.php
// +----------------------------------------------------------------------
// | Date: 2010-5-11
// +----------------------------------------------------------------------
// | Author: fangfa <1364119331@qq.com>
// +----------------------------------------------------------------------
// | 文件描述: 邮件发送类
// +----------------------------------------------------------------------


class SendMail {
	
	/**
	 * 服务器地址
	 * @var unknown_type
	 */
	protected $server = '';
	
	/**
	 * 服务器端口
	 * @var unknown_type
	 */
	protected $port = 25;
	
	
	/**
	 * 邮件服务器是否需要验证
	 * @var unknown_type
	 */
	protected $auth = true;
	
	/**
	 * 登录的用户名
	 * @var unknown_type
	 */
	protected $user = '';
	
	/**
	 * 验证密码
	 * @var unknown_type
	 */
	protected $password = '';
	
	/**
	 * 邮件发送方式
	 * 1=通过SMTP协议发送、 2=通过mail函数发送、3=通过SOCKET连接SMTP服务器发送
	 * @var unknown_type
	 */
	protected $type = 1;
	
	/**
	 * 换行符
	 * @var unknown_type
	 */
	protected $delimiter = "\r\n";	
	
	/**
	 * 错误信息
	 * @var unknown_type
	 */
	public $error = array();
	
	/**
	 * 构造函数
	 */
	function __construct($server, $port, $user, $password, $type = 1, $delimiter = 1 ) {
		$this->set ( $server, $port, $user, $password, $type = 1, $delimiter = 1 );
		$this->auth = 1;
	}
	
	/**
	 * 设定发送邮件的配置
	 * @param string $server 服务器地址
	 * @param int $port  //服务器使用的端口
	 * @param string $user	//用户名
	 * @param string $password  //密码
	 * @param int $type  //邮件发送方式  1=通过SMTP协议发送、 2=通过mail函数发送、3=通过SOCKET连接SMTP服务器发送
	 * @param string $delimiter	
	 */
	public function set($server, $port, $user, $password, $type = 1, $delimiter = 1 ) {
		$type && $this->type = $type;
		$this->server = $server;
		$port && $this->port = $port;
		$user && $this->user = $user;
		$password && $this->password = $password;
		$this->delimiter = $delimiter == 1 ? "\r\n" : ($delimiter == 2 ? "\r" : "\n");		
	}
	
	/**
	 * 发送邮件，可群发
	 * 
	 * @param $email_to  收件人（数组，或者以 "," 分割）
	 * @param $email_subject 邮件标题
	 * @param $email_message	邮件内容
	 * @param $email_from 寄件人
	 * 
	 */
	public function send($email_to, $email_subject, $email_message, $email_from = '') {
		$email_subject = '=?utf-8?B?' . base64_encode ( str_replace ( "\r", '', $email_subject ) ) . '?=';
		$email_message = str_replace ( "\r\n.", " \r\n..", str_replace ( "\n", "\r\n", str_replace ( "\r", "\n", str_replace ( "\r\n", "\n", str_replace ( "\n\r", "\r", $email_message ) ) ) ) );
		$adminemail = $this->type == 1 ? C('MAIL_USER') : C('ADMINEMAIL') ;
		$email_from = $email_from == '' ? '=?utf-8?B?' . base64_encode ( C('COMPANYNAME') ) . "?= <$adminemail>" 
										: (preg_match ( '/^(.+?) \<(.+?)\>$/', $email_from, $from ) ? '=?utf-8?B?' . base64_encode ( $from [1] ) . "?= <$from[2]>" : $email_from);
		if (!is_array($email_to)) {  //收件人
			$emails = explode ( ',', $email_to );
		}
		foreach ( $emails as $touser ) {
			$tousers [] = preg_match ( '/^(.+?) \<(.+?)\>$/', $touser, $to ) ? $to [2] : $touser;
		}
		$email_to = implode ( ',', $tousers );
		$headers = "From: $email_from{$this->delimiter}X-Priority: 3{$this->delimiter}X-Mailer: fangfacms {$this->delimiter}MIME-Version: 1.0{$this->delimiter}Content-type: text/html; charset=utf-8{$this->delimiter}";
		if ($this->type == 1) {	//通过SMTP协议发送
			return $this->smtp ( $email_to, $email_subject, $email_message, $email_from, $headers );
		} elseif ($this->type == 2) { //通过mail函数发送
			return @mail ( $email_to, $email_subject, $email_message, $headers );
		} else { //通过SOCKET连接SMTP服务器发送
			ini_set ( 'SMTP', $this->server );
			ini_set ( 'smtp_port', $this->port );
			ini_set ( 'sendmail_from', $email_from );
			return @mail ( $email_to, $email_subject, $email_message, $headers );
		}
	}
	
	/**
	 * 通过SMTP协议发送邮件
	 * 
	 *
	 */
	protected function smtp($email_to, $email_subject, $email_message, $email_from = '', $headers = '') {		
		if (! $fp = fsockopen ( $this->server, $this->port, $errno, $errstr, 10 )) {
			$this->errorlog ( 'SMTP', "($this->server:$this->port) CONNECT - Unable to connect to the SMTP server", 0 );
			return false;
		}
		stream_set_blocking ( $fp, true );
		$lastmessage = fgets ( $fp, 512 );
		if (substr ( $lastmessage, 0, 3 ) != '220') {
			$this->errorlog ( 'SMTP', "$this->server:$this->port CONNECT - $lastmessage", 0 );
			return false;
		}
		fwrite ( $fp, ($this->auth ? 'EHLO' : 'HELO') . " fangfacms\r\n" );
		$lastmessage = fgets ( $fp, 512 );
		if (substr ( $lastmessage, 0, 3 ) != 220 && substr ( $lastmessage, 0, 3 ) != 250) {
			$this->errorlog ( 'SMTP', "($this->server:$this->port) HELO/EHLO - $lastmessage", 0 );
			return false;
		}		
		while ( 1 ) {
			if (substr ( $lastmessage, 3, 1 ) != '-' || empty ( $lastmessage )) {
				break;
			}
			$lastmessage = fgets ( $fp, 512 );
		}
		fwrite ( $fp, "AUTH LOGIN\r\n" );
		$lastmessage = fgets ( $fp, 512 );
		if (substr ( $lastmessage, 0, 3 ) != 334) {
			$this->errorlog ( 'SMTP', "($this->server:$this->port) AUTH LOGIN - $lastmessage", 0 );
			return false;
		}		
		fwrite ( $fp, base64_encode ( $this->user ) . "\r\n" );
		$lastmessage = fgets ( $fp, 512 );
		if (substr ( $lastmessage, 0, 3 ) != 334) {
			$this->errorlog ( 'SMTP', "($this->server:$this->port) USERNAME - $lastmessage", 0 );
			return false;
		}		
		fwrite ( $fp, base64_encode ( $this->password ) . "\r\n" );
		$lastmessage = fgets ( $fp, 512 );
		if (substr ( $lastmessage, 0, 3 ) != 235) {
			$this->errorlog ( 'SMTP', "($this->server:$this->port) PASSWORD - $lastmessage", 0 );
			return false;
		}
		fwrite ( $fp, "MAIL FROM: <" . preg_replace ( "/.*\<(.+?)\>.*/", "\\1", $email_from ) . ">\r\n" );
		$lastmessage = fgets ( $fp, 512 );
		if (substr ( $lastmessage, 0, 3 ) != 250) {
			fwrite ( $fp, "MAIL FROM: <" . preg_replace ( "/.*\<(.+?)\>.*/", "\\1", $email_from ) . ">\r\n" );
			$lastmessage = fgets ( $fp, 512 );
			if (substr ( $lastmessage, 0, 3 ) != 250) {
				$this->errorlog ( 'SMTP', "($this->server:$this->port) MAIL FROM - $lastmessage", 0 );
				return false;
			}
		}
		$email_tos = array ();
		$emails = explode ( ',', $email_to );
		foreach ( $emails as $touser ) {
			$touser = trim ( $touser );
			if ($touser) {
				fwrite ( $fp, "RCPT TO: <" . preg_replace ( "/.*\<(.+?)\>.*/", "\\1", $touser ) . ">\r\n" );
				$lastmessage = fgets ( $fp, 512 );
				if (substr ( $lastmessage, 0, 3 ) != 250) {
					fwrite ( $fp, "RCPT TO: <" . preg_replace ( "/.*\<(.+?)\>.*/", "\\1", $touser ) . ">\r\n" );
					$lastmessage = fgets ( $fp, 512 );
					$this->errorlog ( 'SMTP', "($this->server:$this->port) RCPT TO - $lastmessage", 0 );
					return false;
				}
			}
		}
		fwrite ( $fp, "DATA\r\n" );
		$lastmessage = fgets ( $fp, 512 );
		if (substr ( $lastmessage, 0, 3 ) != 354) {
			$this->errorlog ( 'SMTP', "($this->server:$this->port) DATA - $lastmessage", 0 );
		}
		$headers .= 'Message-ID: <' . gmdate ( 'YmdHs' ) . '.' . substr ( md5 ( $email_message . microtime () ), 0, 6 ) . rand ( 100000, 999999 ) . '@' . $_SERVER ['HTTP_HOST'] . ">{$this->delimiter}";
		fwrite ( $fp, "Date: " . gmdate ( 'r' ) . "\r\n" );
		fwrite ( $fp, "To: " . $email_to . "\r\n" );
		fwrite ( $fp, "Subject: " . $email_subject . "\r\n" );
		fwrite ( $fp, $headers . "\r\n" );
		fwrite ( $fp, "\r\n\r\n" );
		fwrite ( $fp, "$email_message\r\n.\r\n" );
		$lastmessage = fgets ( $fp, 512 );
		fwrite ( $fp, "QUIT\r\n" );
		return true;
	}
	
	/**
	 * 记录错误信息
	 * @param $type  错误类型
	 * @param $message  错误消息
	 * @param $is
	 */
	function errorlog($type, $message, $is) {
		$this->error [] = array (
			$type, $message, $is 
		);
	}

}

?>