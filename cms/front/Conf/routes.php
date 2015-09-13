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
	'registers'      => array('Fblogindex','register'),    // 用户注册
	'form'          => array('Findex','form'),    // 提交表单
	'email'         => array('Femail','index'),    // 发送邮件
	'forget' 	    => array('Fuser','forget'),    // 找回密码
	'r' 			=> array('Findex','redirect'), // URL重定向，用于重定向外部链接
	'code' 			=> array('Findex','verify'),   // 验证码
	'down' 			=> array('Fcontent','down'),   // 文件下载
	'count' 		=> array('Fcontent','count'),  // 内容统计
	'tag' 			=> array('Fcontent','tag'),    // tag列表
	'search' 		=> array('Fsearch','index'),   // 站内搜索
	'upload'		=> array('Fupload','fieldupload'),//前台文件上传
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