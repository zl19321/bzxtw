<?php

// +----------------------------------------------------------------------

// | FangFa Net [ http://www.fangfa.net ]

// +----------------------------------------------------------------------

// | File: routes.php

// +----------------------------------------------------------------------

// | Date: Fri Apr 23 14:18:54 CST 2010

// +----------------------------------------------------------------------

// | Author: fangfa <1364119331@qq.com>

// +----------------------------------------------------------------------

// | 文件描述: 路由规则定义，配置方法参考TP手册

// +----------------------------------------------------------------------

// 已经定义的规则请勿轻易更改

return array(

	'sitemap'       => array('Findex','sitemap'),  // 网站地图  html

	'login' 	    => array('Findex','login'),    // 登录

	'register'      => array('Findex','register'),    // 用户注册

	'form'          => array('Findex','form'),    // 提交表单
	
	'add'    => array('Fwapguestbook','add'),     //提交问题

	'user@' 		=> array(  //泛路由，会员中心

							array('/home/','Fuser','home'),

							array('/edit/','Fuser','edit'),

							array('/logout/','Fuser','logout'),

						),

	'cart@' 		=> array(  //泛路由，购物车维护

							array('/show/','Fcart','show'),

						),

	'order@' 		=> array(  //泛路由，订单处理

							array('/submit/','Forder','submit'),

						),

	'comment@' 		=> array(  //泛路由，评论

							array('/add/','Fcomment','add'),

							array('/show-\d{1,}-\d.+/','Fcomment','show'),

						),

);