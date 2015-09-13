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
define('HTML_DIR',    'a');
define('CONF_DIR',    'Conf');
define('LIB_DIR',      'Lib');
define('LOG_DIR',     'logs');
define('LANG_DIR',    'lang');
define('TEMP_DIR',    'tmp');
define('TMPL_DIR',     'Tpl');

// 后台路径设置
define('TMPL_PATH',APP_PATH.TMPL_DIR.'/');
define('HTML_PATH',FANGFACMS_ROOT ); //
define('COMMON_PATH',   APP_PATH.'Common/'); // 项目公共目录
define('LIB_PATH',  APP_PATH.LIB_DIR.'/'); //
define('CACHE_PATH',   RUNTIME_PATH.CACHE_DIR.'/'); //
define('CONFIG_PATH',  APP_PATH.CONF_DIR.'/'); //
define('LOG_PATH',  RUNTIME_PATH.LOG_DIR.'/'); //
define('LANG_PATH', APP_PATH.LANG_DIR.'/'); //
define('TEMP_PATH', RUNTIME_PATH.TEMP_DIR.'/'); //


