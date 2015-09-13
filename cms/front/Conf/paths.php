<?php
// +----------------------------------------------------------------------
// | FangFa Net [ http://www.fangfa.net ]
// +----------------------------------------------------------------------
// | File: paths.php
// +----------------------------------------------------------------------
// | Date: Wed Apr 21 10:17:26 CST 2010
// +----------------------------------------------------------------------
// | Author: fangfa <1364119331@qq.com>
// +----------------------------------------------------------------------
// | 文件描述: 项目自定义目录配置文件
// +----------------------------------------------------------------------

// 目录名称设置
define('CACHE_DIR',  'cache');
define('HTML_DIR',    'html');
define('CONF_DIR',    'Conf');
define('LIB_DIR',      'Lib');
define('LOG_DIR',     'logs');
define('LANG_DIR',    'lang');
define('TEMP_DIR',    'tmp');
define('TMPL_DIR',     'tpl');

// 路径设置

define('HTML_PATH',FANGFACMS_ROOT ); //
// 前台项目公共目录  
define('COMMON_PATH',   APP_PATH.'Common/');
//前台核心程序文件夹
define('LIB_PATH',      APP_PATH.LIB_DIR.'/');
//前台ThinkPHP用缓存文件夹  runtime/cache
define('CACHE_PATH',   RUNTIME_PATH.CACHE_DIR.'/');
//项目配置文件夹  
define('CONFIG_PATH',  	APP_PATH.CONF_DIR.'/');
//前台日志文件夹 runtime/logs
define('LOG_PATH',      RUNTIME_PATH.LOG_DIR.'/');
//前台语言文件夹 lang
define('LANG_PATH',     ALL_CACHE_PATH.LANG_DIR.'/');
//前台ThinkPHP用临时文件夹  cache/tmp
define('TEMP_PATH',     RUNTIME_PATH.TEMP_DIR.'/');
//前台TP解析后的模板缓存目录  runtime/tpl
define('TMPL_PATH',		FANGFACMS_ROOT . 'public/theme/default/');
//前台模板文件目录
define('FRONT_FILE_PATH',FANGFACMS_ROOT . 'public/');




