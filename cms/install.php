<?php
// +----------------------------------------------------------------------
// | FangFa Net [ http://www.fangfa.net ]
// +----------------------------------------------------------------------
// | File: install.php
// +----------------------------------------------------------------------
// | Date: 2010-4-27
// +----------------------------------------------------------------------
// | Author: eddy0909 <eddy0909@126.com>
// +----------------------------------------------------------------------
// | 文件描述: 安装文件
// +----------------------------------------------------------------------

error_reporting ( E_ALL & ~ E_NOTICE );
//是否调试模式
define ( 'IN_DEBUG', true );
// 简写的 DIRECTORY_SEPARATOR
define ( 'DS', DIRECTORY_SEPARATOR );
//根目录
define ( 'FANGFACMS_ROOT', dirname ( __FILE__ ) . '/' );
//定义项目名称和路径
define ( 'APP_NAME', 'install' );
define ( 'APP_PATH', FANGFACMS_ROOT . APP_NAME . '/' );
//载入常量定义文件
require FANGFACMS_ROOT . 'define.php';
define ( 'HTTP_HOST', 'http://' . $_SERVER ['HTTP_HOST'] . '/' );
// 加载框架入口文件
require_once INCLUDE_PATH . 'library/ThinkPHP/ThinkPHP.php';
define ( '_PUBLIC_', __ROOT__ . '/' . APP_NAME . '/public/' );
//实例化前台应用实例
App::run ();