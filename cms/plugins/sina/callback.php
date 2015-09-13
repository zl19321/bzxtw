<?php
session_start();


include_once( 'config.php' );
include_once( 'saetv2.ex.class.php' );

$o = new SaeTOAuthV2( WB_AKEY , WB_SKEY );
if (isset($_REQUEST['code'])) {
	$keys = array();
	$keys['code'] = $_REQUEST['code'];
	$keys['redirect_uri'] = WB_CALLBACK_URL;
	try {
		$token = $o->getAccessToken( 'code', $keys ) ;
	} catch (OAuthException $e) {
	}
}

if ($token) {
	$_SESSION['token'] = $token;
	setcookie( 'weibojs_'.$o->client_id, http_build_query($token) );	
	$c = new SaeTClientV2( WB_AKEY , WB_SKEY , $_SESSION['token']['access_token'] );
	$ms  = $c->home_timeline(); // done
	$uid_get = $c->get_uid();
	$uid = $uid_get['uid'];	
	$user_message = $c->show_user_by_id( $uid);//根据ID获取用户等基本信息	
	$username = $user_message['screen_name'];
	$email = $username."@s.sina.com";	
	require_once("../../admin.php");
	$bloguser = D("bloguser");
    $where = "name = '".$username."' AND type = '新浪微博'";
	$flag = $bloguser->where($where)->find();
	 $data['name'] = $username;
	 $data['type'] = "新浪微博";
	 $data['datelimit'] = strtotime(date('Y-m-d h:i:s',time()+$token['remind_in']));
	 $data['state'] = "1";
	 $data['code'] = $_REQUEST['code'];
	 $data['access_token'] = $_SESSION['token']['access_token'];
	  unset($_SESSION['token']);
	  unset($_REQUEST['code']);
	 $where = "id = ".$flag['id'];
	if ( $flag['id']  == '') {
        $bloguser->add($data);
    	echo "<script type='text/javascript'>location.href('http://site.foway.com/dangyige/blog/admin.php?m=fblog&a=usermanage');alert('授权成功');</script>";
} else {
	$bloguser->where($where)->save($data);
    	echo "<script type='text/javascript'>location.href('http://site.foway.com/dangyige/blog/admin.php?m=fblog&a=usermanage');alert('重新授权成功');</script>";
}
?>
<?php
} else {
?>
授权失败。
<?php
}
?>
