<?php
// +----------------------------------------------------------------------
// | FangFa Net [ http://www.fangfa.net ]
// +----------------------------------------------------------------------
// | File: config.php
// +----------------------------------------------------------------------
// | Date: Wed Apr 21 10:17:09 CST 2010
// +----------------------------------------------------------------------
// | Author: fangfa <1364119331@qq.com>
// +----------------------------------------------------------------------
// | 文件描述: 项目核心配置文件
// +----------------------------------------------------------------------
$config =  array(
	'APP_DEBUG' => IN_DEBUG ? true : false,			//调试模式
	'URL_CASE_INSENSITIVE' => true,		//url大小写不敏感
 	'DEFAULT_MODULE' => 'Findex',		//默认controller
// 	'DEFAULT_ACTION' => 'index',		//默认action
 	'URL_DISPATCH_ON' => false,			//不需要使用URL分发，直接使用传统的URL模式
	'URL_DISPATCH_ON' => false,			//后台不开启URL模式、路由等，url传参使用传统模式
	'APP_PLUGIN_ON' => true,			//启用应用扩展支持
 	'TMPL_ENGINE_TYPE' => 'Smarty',		//后台使用原生的PHP做模板引擎
	'DEFAULT_THEME' => 'default',       //默认default, 其他模板：theme2
	'TMPL_ENGINE_CONFIG' => array(  	 // Smarty模板配置，请勿更改
    	'caching' => false,
        'template_dir' => TMPL_PATH,
		'compile_dir' => RUNTIME_PATH . 'templates_c',
        'cache_dir' => TEMP_PATH,
		'left_delimiter' => '{{',
		'right_delimiter' => '}}',
		'debugging' => false,
        'plugins_dir' => array('plugins',INCLUDE_PATH . '/tag'),
	),
	'TMPL_TEMPLATE_SUFFIX' => '.html',	//模板后缀
	'URL_MODEL' => 0,					//传统模式的url
	'TOKEN_ON' => true,					//开启令牌验证
	'TMPL_ACTION_MESSAGE' => 'Public:message',	//信息提示模板
	'TMPL_ACTION_ERROR' => 'Public:message',
	'TMPL_ACTION_SUCCESS' => 'Public:message',
	'AUTH_PWD_ENCODER'		=>'md5',	// 用户认证密码加密方式
 	'USER_AUTH_GATEWAY' => 'flogin/index', 		//认证网关
	'AUTH_ACT_TYPE' => 'File',	//权限验证的方法，可选： 'File'，'Db'
	'PAGESIZE' => 20,			//列表页默认记录数
	'DEVELOPER_PASSWORD' => 'developer',  //开发用户账号密码
	'SYSTEM_DIR' => array('admin','data','front','include','install','public','min','visit','im'),  //系统文件夹
	'LOG_RECORD' => 'false', //不使用日志记录
);
$data_config = include(ALL_CACHE_PATH.'/config.inc.php');
$cache_config = include(ALL_CACHE_PATH.'/config.cache.php');
return @array_merge($config,(array)$data_config,(array)$cache_config);