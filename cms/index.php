<?php
// +----------------------------------------------------------------------
// | FangFa Net [ http://www.fangfa.net ]
// +----------------------------------------------------------------------
// | File: index.php
// +----------------------------------------------------------------------
// | Date: Wed Apr 21 09:56:14 CST 2010
// +----------------------------------------------------------------------
// | Author: fangfa <1364119331@qq.com>
// +----------------------------------------------------------------------
// | 文件描述: 前台入口文件
// +----------------------------------------------------------------------
error_reporting ( E_ALL & ~ E_NOTICE );
function is_mobile() {
    $user_agent = $_SERVER['HTTP_USER_AGENT'];
    $mobile_agents = Array("240x320","acer","acoon","acs-","abacho","ahong","airness","alcatel","amoi","android","anywhereyougo.com","applewebkit/525","applewebkit/532","asus","audio","au-mic","avantogo","becker","benq","bilbo","bird","blackberry","blazer","bleu","cdm-","compal","coolpad","danger","dbtel","dopod","elaine","eric","etouch","fly ","fly_","fly-","go.web","goodaccess","gradiente","grundig","haier","hedy","hitachi","htc","huawei","hutchison","inno","ipaq","ipod","jbrowser","kddi","kgt","kwc","lenovo","lg ","lg2","lg3","lg4","lg5","lg7","lg8","lg9","lg-","lge-","lge9","longcos","maemo","mercator","meridian","micromax","midp","mini","mitsu","mmm","mmp","mobi","mot-","moto","nec-","netfront","newgen","nexian","nf-browser","nintendo","nitro","nokia","nook","novarra","obigo","palm","panasonic","pantech","philips","phone","pg-","playstation","pocket","pt-","qc-","qtek","rover","sagem","sama","samu","sanyo","samsung","sch-","scooter","sec-","sendo","sgh-","sharp","siemens","sie-","softbank","sony","spice","sprint","spv","symbian","tablet","talkabout","tcl-","teleca","telit","tianyu","tim-","toshiba","tsm","up.browser","utec","utstar","verykool","virgin","vk-","voda","voxtel","vx","wap","wellco","wig browser","wii","windows ce","wireless","xda","xde","zte");
    $is_mobile =  false;//为false 则能在PC上调试 true则不能在PC上调试
    foreach ($mobile_agents as $device) {
        if (stristr($user_agent, $device)) {
            $is_mobile = true;
            break;
        }
    }
    return $is_mobile;
}

if($_GET['close_mobile']) $_SESSION['close_wap'] = true;
//前台入口标识
define ( 'IN', true );
//是否调试模式，调试模式下面不会生成静态页面，会输出调试信息
define ( 'IN_DEBUG', true );
//简写的 DIRECTORY_SEPARATOR
define ( 'DS', DIRECTORY_SEPARATOR );
//根目录
define ( 'FANGFACMS_ROOT', dirname ( __FILE__ ) . '/' );
if (! file_exists ( FANGFACMS_ROOT . 'data/config.inc.php' )) {
	header ( "Content-type: text/html; charset=utf-8" );
	header ( 'Location:install.php' );
	die ( '请先运行 install.php进行' );
}

//定义项目名称和路径
// if(is_mobile() && !$_SESSION['close_wap']){
//     define ( 'APP_NAME', 'wap' );
//     define ( 'APP_PATH', FANGFACMS_ROOT . APP_NAME . '/' );  
// }else{
    define ( 'APP_NAME', 'front' );
    define ( 'APP_PATH', FANGFACMS_ROOT . APP_NAME . '/' );  
// }
       
//载入常量定义文件
require FANGFACMS_ROOT . 'define.php';
//前台提交的数据中可以允许出现的HTML标签。
define ( 'ALLOWED_HTMLTAGS', '<a><p><br><hr><h1><h2><h3><h4><h5><h6><font><u><i><b><strong><div><span><ol><ul><li><img><table><tr><td><map>');
// 加载框架入口文件
require THINK_PATH . "ThinkPHP.php";
//实例化前台应用实例
App::run ();