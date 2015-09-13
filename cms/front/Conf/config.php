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
	'APP_DEBUG' => IN_DEBUG ? true : false ,
	'DEFAULT_MODULE' => 'Findex',		 		//	默认controller
	'DEFAULT_ACTION' => '',		 				//	不设置默认action，否则许多控制器的_empty会失效
	'URL_CASE_INSENSITIVE' => true,		 //	url大小写不敏感
	'URL_DISPATCH_ON' => false,
	'URL_MODEL' => 2,					 //	URL重写模式
    'URL_ROUTER_ON' => true,
	'TMPL_TEMPLATE_SUFFIX' => '.html',   // 模板后缀
	'URL_HTML_SUFFIX' => '.html',  //URL后缀、不可更改
    'URL_PATHINFO_DEPR' => '/',    //URL参数之间的分隔符
	'TMPL_ACTION_MESSAGE' => 'system/message.html',	//信息提示模板
	'TMPL_ACTION_ERROR' => 'system/message.html',
	'TMPL_ACTION_SUCCESS' => 'system/message.html',
	'TMPL_ENGINE_TYPE' =>'Smarty',       // 使用Smarty的模板引擎
	'TMPL_ENGINE_CONFIG' => array(  	 // Smarty模板配置，请勿更改
		'caching' => false,
        'template_dir' => FANGFACMS_ROOT . 'public/theme/default/',
		'compile_dir' => RUNTIME_PATH . 'templates_c',
        'cache_dir' => TEMP_PATH,
		'left_delimiter' => '{{',
		'right_delimiter' => '}}',
		'debugging' => false,
        'plugins_dir' => array('plugins',INCLUDE_PATH . '/tag'),
	),
	//前台发布信息允许的HTML标签，可防止XSS跨站攻击
	'ALLOWED_HTMLTAGS' => '<a><p><br><hr><h1><h2><h3><h4><h5><h6><font><u><i><b><strong><div><span><ol><ul><li><img><table><tr><td><map>',
	'GZIP' => 0,				//是否允许gzip输出
	'APP_PLUGIN_ON' => true,	//启用应用扩展支持
	'TMPL_EXCEPTION_FILE' => APP_PATH.'Tpl/Exception.tpl.php',  //异常模板
	'LOG_RECORD' => 'false',  //不使用日志记录
	//for upload
	'UPLOAD_MAXSIZE' => 1024,//默认上传文件大小
	'FILEEXT' => array('jpg','gif','png','rar','zip'),//默认后最
	'MULTI' => false,//是否开启多图
	'UPLOAD_FILE_RULE' => 'time',//默认命名规则
	'UPLOAD_DIR' => 'public/uploads/',//上传路径
	'WARTER_MARK' => false,//默认关闭水印
	'THUMB' => false,//默认关闭缩略图
);
$data_config = include(ALL_CACHE_PATH.'config.inc.php');
$cache_config = include(ALL_CACHE_PATH.'/config.cache.php');
return @array_merge($config,$data_config,$cache_config);
?>