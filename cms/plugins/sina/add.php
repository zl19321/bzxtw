<?php
session_start();
include_once( 'config.php' );
include_once( 'saetv2.ex.class.php' );
include '../../admin.php';
$content = $_GET['content'];
$pic = $_GET['picture'];
$user = D("bloguser");
$where = " type = '新浪微博' AND state = '1'";
$postuser = $user->where($where)->select();
for($i = 0;$i <= count($postuser) ;$i++ ) {
     $_SESSION['token']['access_token'] = $postuser[$i]['access_token'];
	 $c = new SaeTClientV2( WB_AKEY , WB_SKEY , $_SESSION['token']['access_token'] );
     //$ms  = $c->home_timeline(); // done
     $uid_get = $c->get_uid();
     $uid = $uid_get['uid'];
     $user_message = $c->show_user_by_id($uid);//根据ID获取用户等基本信息
	 if($pic  == null) {
	    $ret = $c->update($content);
	 } else {
	    $ret = $c->upload($content,$pic);
     }	 
}
echo "<script type='text/javascript'>window.close();</script>"
?>