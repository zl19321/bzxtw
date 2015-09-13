<?php
$config = array(
	'APP_DEBUG' => true,			//调试模式
	'URL_CASE_INSENSITIVE' => true,		//url大小写不敏感
 	'URL_DISPATCH_ON' => false,			//不需要使用URL分发，直接使用传统的URL模式
	'URL_DISPATCH_ON' => false,			//不开启URL模式、路由等，url传参使用传统模式
	'APP_PLUGIN_ON' => true,			//启用应用扩展支持
	'TMPL_ENGINE_TYPE' => 'PHP',
);

if(file_exists(ALL_CACHE_PATH . 'config.inc.php')) {
	$data_config = require_once(ALL_CACHE_PATH.'config.inc.php');
	return @array_merge($config,$data_config);
} else {
	return $config;
}
?>