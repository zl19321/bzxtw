<?php
// +----------------------------------------------------------------------
// | FangFa Net [ http://www.fangfa.net ]
// +----------------------------------------------------------------------
// | File: admin.php
// +----------------------------------------------------------------------
// | Date: Wed Apr 21 09:55:28 CST 2010
// +----------------------------------------------------------------------
// | Author: fangfa <1364119331@qq.com>
// +----------------------------------------------------------------------
// | 文件描述: 后台管理的入口文件 
// +----------------------------------------------------------------------

error_reporting ( E_ALL & ~ E_NOTICE );
// 后台入口标识
define ( 'IN_ADMIN', true );
// 是否调试模式
define ( 'IN_DEBUG', true );
// 简写的 DIRECTORY_SEPARATOR
define ( 'DS', DIRECTORY_SEPARATOR );
// 根目录
define ( 'FANGFACMS_ROOT', dirname ( __FILE__ ) . '/' );
if (! file_exists ( FANGFACMS_ROOT . 'data/config.inc.php' )) {
	header ( "Content-type: text/html; charset=utf-8" );
	die ( '请先运行 install.php' );
}
// 定义项目名称和路径
define ( 'APP_NAME', 'admin' );
define ( 'APP_PATH', FANGFACMS_ROOT . APP_NAME . '/' );
require FANGFACMS_ROOT . 'define.php';
// 加载框架入口文件 
require THINK_PATH . "ThinkPHP.php";
// 实例化后台应用实例
App::run ();