<?php
/**
 * 此为PHP-SDK 2.0 的一个使用Demo,用于流程和接口调用演示
 * 请根据自身需求和环境进相应的安全和兼容处理，勿直接用于生产环境
 */
error_reporting(0);
require_once './Config.php';
require_once './Tencent.php';
OAuth::init($client_id, $client_secret);
Tencent::$debug = $debug;
header('Content-Type: text/html; charset=utf-8');
include '../../admin.php';
$content = mb_substr($_GET['content'], 0,140,'utf-8');

    $arr = explode("/",$_GET['picture']);
	if(count($arr)>4) {
            $str = '';
		  for( $i=count($arr)-4;$i<count($arr);$i++) {
		     $str .= "/".$arr[$i];  
		  }              
          $newpic = $str; 		  
	   } else {
        $newpic = "/".$_GET['picture'];
    }		

if($newpic != "/null") {
    $temparr = explode("/",dirname(__FILE__));
	if(count($temparr) == 1) $temparr = explode("\\",dirname(__FILE__));
     $realpic = '';
	for($j=0;$j<count($temparr)-2;$j++) {
	    if($j>0) {
		   $realpic .= "/".$temparr[$j];
		 } else {
		   $realpic .= $temparr[$j];
		}
	} 
   $realpic .= "/public/uploads".$newpic;
}

$user = D("bloguser");
$where = " type = '腾讯微博' AND state = '1'";
$postuser = $user->where($where)->select();
for($i = 0;$i <= count($postuser) ;$i++ ) {
      $_SESSION['t_access_token'] = $postuser[$i]['access_token'];
	  $_SESSION['t_openid'] = $postuser[$i]['openid'];
	  $_SESSION['t_openkey'] = $postuser[$i]['openkey'];
     // 部分接口的调用示例
    $r = Tencent::api('user/info');   
   $params = array(
        'content' =>$content
    );
	if($newpic != "/null") {
    $multi = array('pic' => $realpic);
	}
    $r = Tencent::api('t/add_pic', $params, 'POST', $multi);
   	unset($_SESSION['t_access_token']);
	unset($_SESSION['t_openid']);
	unset($_SESSION['t_openkey']);
}
echo "<script type='text/javascript'>window.open('../sina/add.php?content=".$content."&picture=".$realpic."','','height=5,width=5,top=0,left=0,toolbar=no,menubar=no,scrollbars=no,resizable=no,location=no,status=no');window.close();</script>";
?>