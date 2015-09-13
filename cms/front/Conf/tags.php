<?php
return array(
	'app_begin' => array('init_sesstion'),	//项目开始的时候进行session的设置工作
	'app_before_get_module' => array('parse_module'),		//根据URL，分析重定向控制器。  也就是重写$_POST[C('VAR_MODULE')] 和  $_POST[C('VAR_ACTION')]
);