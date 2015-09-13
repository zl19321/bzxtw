<?php
// +----------------------------------------------------------------------
// | FangFa Net [ http://www.fangfa.net ]
// +----------------------------------------------------------------------
// | File: config.inc.php
// +----------------------------------------------------------------------
// | Date: Wed Apr 21 10:05:42 CST 2010
// +----------------------------------------------------------------------
// | Author: fangfa <1364119331@qq.com>
// +----------------------------------------------------------------------
// | 文件描述: 网站公共配置文件、一般情况下会由程序自动生成
// +----------------------------------------------------------------------

//配置文件
return array(
	//默认数据库配置
	'DB_TYPE'=> 'mysql',		// 数据库类型
	'DB_HOST'=> 'localhost', 	// 数据库服务器地址
	'DB_NAME'=>'fangfacms',  // 数据库名称
	'DB_USER'=>'username', 			// 数据库用户名
	'DB_PWD'=>'password', 				// 数据库密码
	'DB_PORT'=>'3306', 			// 数据库端口
	'DB_PREFIX'=>'fangfa_', 	// 数据表前缀
	//TODO 附加数据库配置
	//站点配置
    'SITE_URL' => 'http://www.fangfa.net',
    'SITE_ROOT' => '/',  //相对根目录的路径
	'ICPNO' => '蜀ICP备 06005255号',	//备案号
	'COPYRIGHT' => '© 2002-2011 四川方法数码科技有限公司 版权所有',		//版权声明	
	'URL_HTML_SUFFIX' => '.html',	//url后缀		
	//SESSION配置
	'SESSION_SAVE_PATH' => 'data/session',	//会话保存地址
	//编辑器 'tiny_mce' or 'kindeditor'
	'EDITOR_TYPE' => 'ueditor',
	//附件相关配置
	'UPLOAD_DIR' => 'public/uploads/',	//上传目录
	'UPLOAD_ATTACHMENT_ALLOWEXT' => 'doc|docx|xls|ppt|wps|zip|rar|txt',	//默认允许的上传附件类型
	'UPLOAD_IMAGES_ALLOWEXT' => 'jpg|jpeg|gif|bmp|png',	//默认允许的上传的图片类型
	'UPLOAD_THUMB_ISTHUMB' => 0, //是否生成缩略图
	'UPLOAD_THUMB_WIDTH' => 150,	//缩略图宽度
	'UPLOAD_THUMB_HEIGHT' => 150,	//缩略图高度
	'UPLOAD_WATER_ISWATERMARK'	=> 0,	//上传的图片是否加水印
	'UPLOAD_WATER_PLACE' => '9',	//水印图位置 可选  1 2 3 4 5 6 7 8 9 分别代表上中下的  左中右9个位置
	'UPLOAD_WATER_PATH' => 'images/watermark.png',		//水印图片的路径
	'UPLOAD_WATER_TRANS' => 50,		//水印图片透明度   范围为 1~100 的整数，数值越小水印图片越透明
	'UPLOAD_MAXSIZE' => 1024,	//默认允许上传的最大文件  KB
	'USER_AUTH_COOKIE_KEY' => '',	//解密cookie使用的密钥
    'CONTENT_COMMENT_OPEN' => '1',  //是否开启评论
    'COMMENT_USER_LOGIN' => '0',	//是否需要用户登陆才能评论
	'CREATE_HTML' => false,  //文件夹创建规则
	'CONTENT_COMMENT_OPEN'=>0,
	'COMMENT_USER_LOGIN'=>0,
	'PAGESIZE' => 20,  //每页记录数
);



