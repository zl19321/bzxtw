SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

DROP TABLE IF EXISTS `#@__activity`;
CREATE TABLE IF NOT EXISTS `#@__activity` (
  `aid` mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `catid` smallint(5) unsigned NOT NULL COMMENT '栏目ID',
  `title` char(80) NOT NULL DEFAULT '' COMMENT '活动名称',
  `attr` set('top','scroll','hot') DEFAULT NULL COMMENT '文档属性',
  `thumb` char(100) NOT NULL DEFAULT '' COMMENT '活动海报',
  `description` varchar(250) DEFAULT NULL COMMENT '活动摘要',
  `seokeywords` char(200) NOT NULL COMMENT '关键字',
  `seodescription` char(255) NOT NULL DEFAULT '' COMMENT '描述',
  `seotitle` varchar(100) DEFAULT NULL COMMENT 'SEO标题',
  `url` char(100) NOT NULL COMMENT '链接',
  `sort` tinyint(4) unsigned NOT NULL DEFAULT '1' COMMENT '排序',
  `user_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '用户ID',
  `username` varchar(50) NOT NULL COMMENT '用户名',
  `template` varchar(50) DEFAULT NULL COMMENT '模板名称',
  `create_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '建立时间',
  `update_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `start_time` int(11) DEFAULT NULL COMMENT '活动开始时间',
  `end_time` int(11) DEFAULT NULL COMMENT '活动结束时间',
  `content` text COMMENT '活动详情',
  `in_time` int(11) DEFAULT NULL COMMENT '活动开始时间',
  `allowguest` tinyint(4) NOT NULL COMMENT '是否会员才能报名',
  `address` varchar(255) CHARACTER SET ucs2 DEFAULT NULL COMMENT '活动地址',
  `chengbanfang` varchar(255) DEFAULT NULL COMMENT '活动承办方',
  `zuzhifang` varchar(255) DEFAULT NULL,
  `out_time` int(10) DEFAULT NULL COMMENT '活动结束时间',
  `status` tinyint(2) NOT NULL DEFAULT '0' COMMENT '待审=0,审核=9，回收站=-1',
  `vip` tinyint(2) NOT NULL COMMENT '允许会员参加=1，不允许会员参加=0',
  PRIMARY KEY (`aid`),
  KEY `status` (`sort`,`aid`),
  KEY `listorder` (`catid`,`sort`,`aid`),
  KEY `catid` (`catid`,`aid`),
  KEY `updatetime` (`catid`,`update_time`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='动活列表';


DROP TABLE IF EXISTS `#@__activity_apply`;
CREATE TABLE IF NOT EXISTS `#@__activity_apply` (
  `mid` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `bianhao` int(11) unsigned NOT NULL COMMENT '会员编号',
  `ip` char(15) NOT NULL COMMENT '报名ip地址',
  `aid` int(11) NOT NULL COMMENT '活动ID',
  `name` char(10) NOT NULL COMMENT '姓名',
  `tel` char(15) DEFAULT NULL COMMENT '电话',
  `email` char(20) DEFAULT NULL COMMENT '电子邮件',
  `qq` int(12) DEFAULT NULL COMMENT 'qq',
  `message` text COMMENT '报名者留言',
  `create_time` int(11) DEFAULT NULL COMMENT '创建时间',
  `update_time` int(11) DEFAULT NULL COMMENT '更新时间',
  `reply` char(250) DEFAULT NULL COMMENT '管理员回复',
  `reply_time` int(11) DEFAULT NULL COMMENT '回复时间',
  `reply_user_id` tinyint(4) DEFAULT NULL COMMENT '回复人ID',
  `status` enum('1','0','-1') DEFAULT '0',
  PRIMARY KEY (`mid`),
  KEY `aid` (`aid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='活动报名表';


DROP TABLE IF EXISTS `#@__acts`;
CREATE TABLE IF NOT EXISTS `#@__acts` (
  `aid` smallint(4) unsigned NOT NULL auto_increment,
  `name` varchar(100) NOT NULL COMMENT '操作名称，中文',
  `appname` varchar(100) NOT NULL COMMENT '应用名称',
  `controller` varchar(100) DEFAULT NULL COMMENT '控制器',
  `action` varchar(100) DEFAULT NULL COMMENT '动作',
  `allow` varchar(255) DEFAULT NULL COMMENT '允许访问的角色',
  `deny` varchar(255) DEFAULT NULL COMMENT '禁止访问的角色',
  PRIMARY KEY  (`aid`),
  KEY `controller` (`controller`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='访问控制表';

INSERT INTO `#@__acts` (`name`, `appname`, `controller`, `action`, `allow`, `deny`) VALUES
('活动内容管理', 'admin', 'factivity', 'manage', 'administrator', NULL),
('录入要显示的内容', 'admin', 'factivity', 'add', 'administrator', NULL),
('更新内容', 'admin', 'factivity', 'edit', 'administrator', NULL),
('删除活动信息', 'admin', 'factivity', 'delete', 'administrator', NULL),
('活动报名信息', 'admin', 'factivity', 'manage_apply', 'administrator', NULL),
('添加报名信息', 'admin', 'factivity', 'add_apply', 'administrator', NULL),
('报名信息修改', 'admin', 'factivity', 'edit_apply', 'administrator', NULL),
('报名信息删除', 'admin', 'factivity', 'delete_apply', 'administrator', NULL),
('标签管理', 'admin', 'fad', 'manage', 'administrator', NULL),
('添加广告', 'admin', 'fad', 'add', 'administrator', NULL),
('编辑广告', 'admin', 'fad', 'edit', 'administrator', NULL),
('AJAX获取AD表单', 'admin', 'fad', 'ajax_getform', 'administrator', NULL),
('预览', 'admin', 'fad', 'preview', 'administrator', NULL),
('分类管理', 'admin', 'fask', 'category_manage', 'administrator', NULL),
('列表', 'admin', 'fask', 'manage', 'administrator', NULL),
('编辑', 'admin', 'fask', 'edit', 'administrator', NULL),
('删除', 'admin', 'fask', 'delete', 'administrator', NULL),
('更新所有缓存', 'admin', 'fcache', 'all', 'administrator', NULL),
('更新app缓存以及项目临时文件', 'admin', 'fcache', 'app', 'administrator', NULL),
('更新首页', 'admin', 'fcache', 'index', 'administrator', NULL),
('更新参数配置文件', 'admin', 'fcache', 'sysset', 'administrator', NULL),
('更新act缓存', 'admin', 'fcache', 'act', 'administrator', NULL),
('更新模型缓存', 'admin', 'fcache', 'model', 'administrator', NULL),
('更新模块缓存', 'admin', 'fcache', 'module', 'administrator', NULL),
('更新栏目缓存', 'admin', 'fcache', 'category', 'administrator', NULL),
('更新菜单缓存', 'admin', 'fcache', 'menu', 'administrator', NULL),
('更新角色信息缓存', 'admin', 'fcache', 'role', 'administrator', NULL),
('缓存广告信息', 'admin', 'fcache', 'ad', 'administrator', NULL),
('更新关键词链接', 'admin', 'fcache', 'keylink', 'administrator', NULL),
('添加栏目', 'admin', 'fcategory', 'add', 'administrator', NULL),
('AJAX获取对应模型分类', 'admin', 'fcategory', 'ajaxgetclassify', 'administrator', NULL),
('获取栏目信息', 'admin', 'fcategory', 'getcategory', 'administrator', NULL),
('编辑栏目分类', 'admin', 'fcategory', 'edit', 'administrator', NULL),
('栏目管理', 'admin', 'fcategory', 'manage', 'administrator', NULL),
('删除栏目', 'admin', 'fcategory', 'delete', 'administrator', NULL),
('列表', 'admin', 'fcomment', 'manage', 'administrator', NULL),
('编辑', 'admin', 'fcomment', 'edit', 'administrator', NULL),
('审核', 'admin', 'fcomment', 'check', 'administrator', NULL),
('过滤敏感词', 'admin', 'fcomment', 'filter', 'administrator', NULL),
('删除留言', 'admin', 'fcomment', 'delete', 'administrator', NULL),
('内容管理', 'admin', 'fcontent', 'manage', 'administrator', NULL),
('录入要显示的内容', 'admin', 'fcontent', 'add', 'administrator', NULL),
('更新内容', 'admin', 'fcontent', 'edit', 'administrator', NULL),
('获取分类信息', 'admin', 'fcontent', 'getclassify', 'administrator', NULL),
('彻底删除信息', 'admin', 'fcontent', 'delete', 'administrator', NULL),
('数据库备份', 'admin', 'fdb', 'backup', 'administrator', NULL),
('数据库恢复', 'admin', 'fdb', 'restore', 'administrator', NULL),
('删除备份', 'admin', 'fdb', 'del', 'administrator', NULL),
('数据库修复', 'admin', 'fdb', 'repair', 'administrator', NULL),
('数据表优化', 'admin', 'fdb', 'optimize', 'administrator', NULL),
('查看数据表结构', 'admin', 'fdb', 'viewinfo', 'administrator', NULL),
('编辑器管理入口', 'admin', 'feditor', 'manage', 'administrator', NULL),
('上传入口', 'admin', 'feditor', 'upload', 'administrator', NULL),
('图片库列表', 'admin', 'ffiles', 'images', 'administrator', NULL),
('模板库', 'admin', 'ffiles', 'tpl', 'administrator', NULL),
('管理入口', 'admin', 'ffriendlink', 'index', 'administrator', NULL),
('友情链接管理', 'admin', 'ffriendlink', 'manage', 'administrator', NULL),
('添加', 'admin', 'ffriendlink', 'add', 'administrator', NULL),
('编辑', 'admin', 'ffriendlink', 'edit', 'administrator', NULL),
('编辑', 'admin', 'ffriendlink', '_edit_ajax', 'administrator', NULL),
('删除', 'admin', 'ffriendlink', 'delete', 'administrator', NULL),
('分类管理', 'admin', 'ffriendlink', 'manage_type', 'administrator', NULL),
('编辑', 'admin', 'ffriendlink', 'edit_type', 'administrator', NULL),
('处理友情链接类型的AJAX请求', 'admin', 'ffriendlink', '_edit_type_ajax', 'administrator', NULL),
('添加分类', 'admin', 'ffriendlink', 'add_type', 'administrator', NULL),
('删除分类', 'admin', 'ffriendlink', 'delete_type', 'administrator', NULL),
('列表', 'admin', 'fguestbook', 'manage', 'administrator', NULL),
('编辑', 'admin', 'fguestbook', 'edit', 'administrator', NULL),
('审核', 'admin', 'fguestbook', 'check', 'administrator', NULL),
('过滤敏感词', 'admin', 'fguestbook', 'filter', 'administrator', NULL),
('删除留言', 'admin', 'fguestbook', 'delete', 'administrator', NULL),
('更新主页', 'admin', 'fhtml', 'index', 'administrator', NULL),
('栏目页', 'admin', 'fhtml', 'category', 'administrator', NULL),
('更新详细内容页', 'admin', 'fhtml', 'show', 'administrator', NULL),
('生成', 'admin', 'fhtml', 'sitemaps', 'administrator', NULL),
('生成rss文件', 'admin', 'fhtml', 'rss', 'administrator', NULL),
('后台框架', 'admin', 'findex', 'index', 'administrator', NULL),
('管理主页', 'admin', 'findex', 'home', 'administrator', NULL),
('后台导航', 'admin', 'findex', 'map', 'administrator', NULL),
('系统信息', 'admin', 'findex', 'system', 'administrator', NULL),
('测试', 'admin', 'findex', 'test', 'administrator', NULL),
('职位列表', 'admin', 'fjob', 'manage', 'administrator', NULL),
('添加职位', 'admin', 'fjob', 'add', 'administrator', NULL),
('编辑职位', 'admin', 'fjob', 'edit', 'administrator', NULL),
('修改状态', 'admin', 'fjob', 'check', 'administrator', NULL),
('删除职位', 'admin', 'fjob', 'delete', 'administrator', NULL),
('简历列表', 'admin', 'fjob', 'resume_manage', 'administrator', NULL),
('查看简历', 'admin', 'fjob', 'resume_show', 'administrator', NULL),
('删除简历', 'admin', 'fjob', 'resume_delete', 'administrator', NULL),
('编辑简历', 'admin', 'fjob', 'resume_edit', 'administrator', NULL),
('系统登录', 'admin', 'flogin', 'index', 'administrator', NULL),
('注销登录', 'admin', 'flogin', 'logout', 'administrator', NULL),
('验证码', 'admin', 'flogin', 'verify', 'administrator', NULL),
('找回密码', 'admin', 'flogin', 'getpwd', 'administrator', NULL),
('设置密码', 'admin', 'flogin', 'setpwd', 'administrator', NULL),
('管理员管理', 'admin', 'fmanager', 'manage', 'administrator', NULL),
('添加管理员', 'admin', 'fmanager', 'add', 'administrator', NULL),
('编辑管理员', 'admin', 'fmanager', 'edit', 'administrator', NULL),
('删除管理员', 'admin', 'fmanager', 'delete', 'administrator', NULL),
('会员模型列表', 'admin', 'fmember', 'manage_model', 'administrator', NULL),
('新增模型', 'admin', 'fmember', 'add_model', 'administrator', NULL),
('字段列表', 'admin', 'fmember', 'list_field', 'administrator', NULL),
('添加', 'admin', 'fmember', 'add_field', 'administrator', NULL),
('编辑模型', 'admin', 'fmember', 'update_model', 'administrator', NULL),
('删除模型', 'admin', 'fmember', 'del_model', 'administrator', NULL),
('删除字段', 'admin', 'fmember', 'del_field', 'administrator', NULL),
('启用', 'admin', 'fmember', 'status_model', 'administrator', NULL),
('启用', 'admin', 'fmember', 'status_field', 'administrator', NULL),
('预览模型', 'admin', 'fmember', 'preview_model', 'administrator', NULL),
('导出模型', 'admin', 'fmember', 'export_model', 'administrator', NULL),
('会员角色管理', 'admin', 'fmember', 'manage_group', 'administrator', NULL),
('添加会员角色', 'admin', 'fmember', 'add_group', 'administrator', NULL),
('编辑会员角色', 'admin', 'fmember', 'update_group', 'administrator', NULL),
('删除会员角色', 'admin', 'fmember', 'delete_group', 'administrator', NULL),
('启用', 'admin', 'fmember', 'status_group', 'administrator', NULL),
('会员模块配置', 'admin', 'fmember', 'setting_module', 'administrator', NULL),
('模型列表', 'admin', 'fmodel', 'manage', 'administrator', NULL),
('字段列表', 'admin', 'fmodel', 'list_field', 'administrator', NULL),
('新增模型', 'admin', 'fmodel', 'add_model', 'administrator', NULL),
('编辑模型', 'admin', 'fmodel', 'update_model', 'administrator', NULL),
('添加', 'admin', 'fmodel', 'add_field', 'administrator', NULL),
('删除模型', 'admin', 'fmodel', 'del_model', 'administrator', NULL),
('删除字段', 'admin', 'fmodel', 'del_field', 'administrator', NULL),
('启用', 'admin', 'fmodel', 'status_model', 'administrator', NULL),
('启用', 'admin', 'fmodel', 'status_field', 'administrator', NULL),
('导出模型', 'admin', 'fmodel', 'export_model', 'administrator', NULL),
('导入模型', 'admin', 'fmodel', 'import_model', 'administrator', NULL),
('预览模型', 'admin', 'fmodel', 'preview_model', 'administrator', NULL),
('模块管理', 'admin', 'fmodule', 'manage', 'administrator', NULL),
('注册模块', 'admin', 'fmodule', 'add', 'administrator', NULL),
('注册', 'admin', 'fmodule', 'edit', 'administrator', NULL),
('删除模块', 'admin', 'fmodule', 'delete', 'administrator', NULL),
('更新模块缓存信息', 'admin', 'fmodule', 'cache', 'administrator', NULL),
('订单管理', 'admin', 'forder', 'manage', 'administrator', NULL),
('修改订单', 'admin', 'forder', 'edit', 'administrator', NULL),
('查看订单详情', 'admin', 'forder', 'show', 'administrator', NULL),
('查看某个订单的日志记录', 'admin', 'forder', 'log', 'administrator', NULL),
('模块配置', 'admin', 'forder', 'setting', 'administrator', NULL),
('单页处理', 'admin', 'fpage', 'manage', 'administrator', NULL),
('编辑个人资料', 'admin', 'fprofile', 'edit', 'administrator', NULL),
('修改个人密码', 'admin', 'fprofile', 'pwd', 'administrator', NULL),
('注销登录', 'admin', 'fprofile', 'logout', 'administrator', NULL),
('模块操作列表', 'admin', 'frbac', 'manage', 'administrator', NULL),
('添加模块', 'admin', 'frbac', 'add', 'administrator', NULL),
('编辑模块', 'admin', 'frbac', 'edit', 'administrator', NULL),
('取消模块注册', 'admin', 'frbac', 'delete', 'administrator', NULL),
('角色授权', 'admin', 'frbac', 'power', 'administrator', NULL),
('自动注册模块', 'admin', 'frbac', 'Autoadd', 'administrator', NULL),
('角色管理', 'admin', 'frole', 'manage', 'administrator', NULL),
('添加角色', 'admin', 'frole', 'add', 'administrator', NULL),
('编辑角色', 'admin', 'frole', 'edit', 'administrator', NULL),
('删除角色', 'admin', 'frole', 'delete', 'administrator', NULL),
('启用', 'admin', 'frole', 'status', 'administrator', NULL),
('栏目管理', 'admin', 'fsalenet', 'category_manage', 'administrator', NULL),
('营销内容管理', 'admin', 'fsalenet', 'manage', 'administrator', NULL),
('审核内容', 'admin', 'fsalenet', 'check', 'administrator', NULL),
('录入要显示的内容', 'admin', 'fsalenet', 'add', 'administrator', NULL),
('更新内容', 'admin', 'fsalenet', 'edit', 'administrator', NULL),
('删除营销信息', 'admin', 'fsalenet', 'delete', 'administrator', NULL),
('系统设置', 'admin', 'fset', 'set', 'administrator', NULL),
('菜单管理', 'admin', 'fset', 'menu', 'administrator', NULL),
('系统参数设置', 'admin', 'fset', 'arg', 'administrator', NULL),
('关键词链接', 'admin', 'fset', 'keylink', 'administrator', NULL),
('TODO更新参数缓存', 'admin', 'fset', 'cacheset', 'administrator', NULL),
('常规的添加', 'admin', 'fset', 'addData', 'administrator', NULL),
('常规的删除操作', 'admin', 'fset', 'deleteData', 'administrator', NULL),
('数据列表', 'admin', 'fset', 'manageData', 'administrator', NULL),
('问卷列表', 'admin', 'fsurvey', 'manage', 'administrator', NULL),
('添加问卷', 'admin', 'fsurvey', 'add', 'administrator', NULL),
('编辑问卷', 'admin', 'fsurvey', 'edit', 'administrator', NULL),
('问卷主题列表', 'admin', 'fsurvey', 'survey_manage_subject', 'administrator', NULL),
('管理问题', 'admin', 'fsurvey', 'manage_question', 'administrator', NULL),
('编辑问题', 'admin', 'fsurvey', 'edit_question', 'administrator', NULL),
('添加问题', 'admin', 'fsurvey', 'add_question', 'administrator', NULL),
('删除问题', 'admin', 'fsurvey', 'delete_question', 'administrator', NULL),
('问卷主题编辑', 'admin', 'fsurvey', 'survey_edit_subject', 'administrator', NULL),
('删除问卷', 'admin', 'fsurvey', 'delete', 'administrator', NULL),
('重置数据', 'admin', 'fsurvey', 'reset', 'administrator', NULL),
('tag管理', 'admin', 'ftag', 'manage', 'administrator', NULL),
('添加TAG', 'admin', 'ftag', 'add', 'administrator', NULL),
('编辑TAG', 'admin', 'ftag', 'edit', 'administrator', NULL),
('模板管理', 'admin', 'ftpl', 'manage', 'administrator', NULL),
('模板编辑', 'admin', 'ftpl', 'edit', 'administrator', NULL),
('删除模板', 'admin', 'ftpl', 'delete', 'administrator', NULL),
('清除模板缓存', 'admin', 'ftpl', 'cache', 'administrator', NULL),
('标签生成向导', 'admin', 'ftpl', 'create_tag', 'administrator', NULL),
('处理模型上传字段的上传动作', 'admin', 'fupload', 'fieldupload', 'administrator', NULL),
('普遍上传', 'admin', 'fupload', 'CommonUpload', 'administrator', NULL),
('会员管理', 'admin', 'fuser', 'manage', 'administrator', NULL),
('添加会员', 'admin', 'fuser', 'add', 'administrator', NULL),
('编辑会员', 'admin', 'fuser', 'edit', 'administrator', NULL),
('删除会员', 'admin', 'fuser', 'delete', 'administrator', NULL),
('投票列表', 'admin', 'fvote', 'manage', 'administrator', NULL),
('添加投票', 'admin', 'fvote', 'add', 'administrator', NULL),
('编辑投票', 'admin', 'fvote', 'edit', 'administrator', NULL),
('删除问卷', 'admin', 'fvote', 'delete', 'administrator', NULL),
('查看问卷统计', 'admin', 'fvote', 'voteshow', 'administrator', NULL),
('问卷列表', 'admin', 'fvote', 'survey_manage', 'administrator', NULL),
('添加问卷', 'admin', 'fvote', 'survey_add', 'administrator', NULL),
('编辑问卷', 'admin', 'fvote', 'survey_edit', 'administrator', NULL),
('问卷主题列表', 'admin', 'fvote', 'survey_manage_subject', 'administrator', NULL),
('问卷主题编辑', 'admin', 'fvote', 'survey_edit_subject', 'administrator', NULL),
('重置数据', 'admin', 'fvote', 'reset', 'administrator', NULL),
('数据列表', 'admin', 'fvote', 'manageData', 'administrator', NULL),
('活动报名频道页', 'front', 'factivity', 'index', NULL, NULL),
('活动报名列表页面', 'front', 'factivity', 'lists', NULL, NULL),
('活动列表显示', 'front', 'factivity', 'show', NULL, NULL),
('问答列表页', 'front', 'fask', 'index', NULL, NULL),
('问答详情页', 'front', 'fask', 'show', NULL, NULL),
('提交问答表单页', 'front', 'fask', 'add', NULL, NULL),
('显示购物车', 'front', 'fcart', 'show', NULL, NULL),
('评论展示', 'front', 'fcomment', 'show', NULL, NULL),
('添加评论', 'front', 'fcomment', 'add', NULL, NULL),
('过滤敏感词', 'front', 'fcomment', 'filter', NULL, NULL),
('栏目页', 'front', 'fcontent', 'index', NULL, NULL),
('详细内容信息', 'front', 'fcontent', 'show', NULL, NULL),
('PHP方式下载指定文件', 'front', 'fcontent', 'down', NULL, NULL),
('浏览次数', 'front', 'fcontent', 'count', NULL, NULL),
('发送邮件', 'front', 'femail', 'index', NULL, NULL),
('留言板首页列表页', 'front', 'fguestbook', 'index', NULL, NULL),
('添加留言', 'front', 'fguestbook', 'add', NULL, NULL),
('过滤敏感词', 'front', 'fguestbook', 'filter', NULL, NULL),
('系统首页', 'front', 'findex', 'index', NULL, NULL),
('验证码', 'front', 'findex', 'verify', NULL, NULL),
('用户注册', 'front', 'findex', 'register', NULL, NULL),
('登入系统', 'front', 'findex', 'login', NULL, NULL),
('网站地图', 'front', 'findex', 'sitemap', NULL, NULL),
('链接重定向', 'front', 'findex', 'redirect', NULL, NULL),
('链接重定向', 'front', 'findex', 'form', NULL, NULL),
('列表頁', 'front', 'fjob', 'index', NULL, NULL),
('职位详情', 'front', 'fjob', 'show', NULL, NULL),
('投递简历', 'front', 'fjob', 'send', NULL, NULL),
('提交购物车物品', 'front', 'forder', 'submit', NULL, NULL),
('添加订单', 'front', 'forder', 'add', NULL, NULL),
('确认订单', 'front', 'forder', 'confirm', NULL, NULL),
('显示单页详细信息', 'front', 'fpage', 'show', NULL, NULL),
('频道页', 'front', 'fsalenet', 'index', NULL, NULL),
('职位详情', 'front', 'fsalenet', 'show', NULL, NULL),
('站内搜索入口', 'front', 'fsearch', 'index', NULL, NULL),
('站内搜索', 'front', 'fsearch', 'manage', NULL, NULL),
('登录后会员中心主页', 'front', 'fuser', 'home', NULL, NULL),
('编辑个人资料', 'front', 'fuser', 'edit', NULL, NULL),
('用户忘记密码', 'front', 'fuser', 'forget', NULL, NULL),
('注销登录', 'front', 'fuser', 'logout', NULL, NULL),
('验证码', 'front', 'fverify', 'index', NULL, NULL),
('投票列表页', 'front', 'fvote', 'index', NULL, NULL),
('处理投票', 'front', 'fvote', 'add', NULL, NULL),
('投票页', 'front', 'fvote', 'vote', NULL, NULL),
('验证码', 'front', 'fvote', 'verify', NULL, NULL),
('结果页', 'front', 'fvote', 'show', NULL, NULL);


DROP TABLE IF EXISTS `#@__ad`;
CREATE TABLE IF NOT EXISTS `#@__ad` (
  `aid` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `type` enum('flash','image','html','link','banner','banner_t') NOT NULL DEFAULT 'html' COMMENT '关高类型',
  `name` varchar(60) NOT NULL DEFAULT '' COMMENT '广告位名称',
  `starttime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '开始时间',
  `endtime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '到期时间',
  `setting` text COMMENT '广告设置',
  `expbody` text COMMENT '过期显示内容',
  `update_time` datetime DEFAULT NULL,
  PRIMARY KEY (`aid`),
  KEY `tagname` (`type`,`endtime`,`starttime`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='广告表';


DROP TABLE IF EXISTS `#@__ask`;
CREATE TABLE IF NOT EXISTS `#@__ask` (
  `ask_id` mediumint(9) NOT NULL AUTO_INCREMENT,
  `catid` mediumint(9) NOT NULL,
  `ask_category_id` mediumint(9) NOT NULL,
  `parentid` mediumint(9) NOT NULL,
  `title` text,
  `content` text,
  `good_answer` tinyint(1) DEFAULT '0',
  `user_id` mediumint(9) DEFAULT NULL,
  `username` varchar(255) DEFAULT NULL,
  `answernum` mediumint(9) DEFAULT '0',
  `ip` varchar(32) DEFAULT NULL,
  `status` tinyint(4) DEFAULT '1',
  `create_time` int(10) DEFAULT NULL,
  `update_time` int(10) DEFAULT NULL,
  PRIMARY KEY (`ask_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='问答表';


DROP TABLE IF EXISTS `#@__ask_category`;
CREATE TABLE IF NOT EXISTS `#@__ask_category` (
  `ask_category_id` mediumint(9) NOT NULL AUTO_INCREMENT,
  `catid` mediumint(9) NOT NULL,
  `name` varchar(255) NOT NULL,
  `parentid` mediumint(9) NOT NULL DEFAULT '0',
  `sort` int(11) NOT NULL DEFAULT '0',
  `status` tinyint(4) NOT NULL DEFAULT '1',
  `create_time` int(10) NOT NULL,
  `update_time` int(10) DEFAULT NULL,
  PRIMARY KEY (`ask_category_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='问答分类表';


DROP TABLE IF EXISTS `#@__bloguser`;
CREATE TABLE IF NOT EXISTS `#@__bloguser` (
  `id` int(5) NOT NULL auto_increment,
  `name` varchar(20) NOT NULL COMMENT '用户名',
  `type` varchar(20) NOT NULL COMMENT '微博名称',
  `code` char(32) NOT NULL COMMENT '用于重新授权',
  `access_token` char(32) default NULL,
  `refresh_token` char(32) default NULL,
  `openid` char(32) default NULL,
  `openkey` char(32) default NULL,
  `datelimit` int(10) NOT NULL COMMENT '授权时间限制',
  `state` tinyint(1) NOT NULL default '1' COMMENT '状态（0、关闭 1、开启）',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='微博用户列表' AUTO_INCREMENT=6 ;



DROP TABLE IF EXISTS `#@__category`;
CREATE TABLE IF NOT EXISTS `#@__category` (
  `catid` smallint(5) unsigned NOT NULL auto_increment,
  `modelid` tinyint(4) unsigned NOT NULL DEFAULT '0' COMMENT '模型ID',
  `type` enum('normal','page','link') NOT NULL DEFAULT 'normal' COMMENT '栏目类型：普通，单页，外链',
  `controller` varchar(50) NOT NULL DEFAULT ' ' COMMENT '控制器名称，冗余字段',
  `parentid` tinyint(4) unsigned NOT NULL DEFAULT '0' COMMENT '父栏目ID',
  `name` varchar(30) NOT NULL COMMENT '栏目名称',
  `ename` varchar(255) NOT NULL COMMENT '栏目别名',
  `thumb` varchar(100) DEFAULT NULL COMMENT '缩略图',
  `catdir` varchar(100) DEFAULT NULL COMMENT '栏目目录',
  `description` varchar(250) DEFAULT NULL COMMENT '栏目说明',
  `seotitle` varchar(80) DEFAULT NULL COMMENT '栏目SEO标题',
  `seokeywords` varchar(250) DEFAULT NULL COMMENT '栏目SEO关键字',
  `seodescription` varchar(250) DEFAULT NULL COMMENT '栏目SEO描述',
  `url` varchar(200) DEFAULT NULL COMMENT '栏目链接',
  `template` varchar(200) DEFAULT NULL COMMENT '栏目各页面的模板配置',
  `permissions` text COMMENT '栏目权限设置',
  `setting` text COMMENT '栏目的私有属性的配置',
  `ishtml` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否静态目录',
  `islock` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否锁定，锁定不能删除',
  `sort` smallint(4) unsigned NOT NULL DEFAULT '1' COMMENT '栏目排序',
  `cattype` enum('cla','cat','site') NOT NULL DEFAULT 'cat' COMMENT '‘cla’分类，‘cat’栏目,''site''分表',
  `topcatid` varchar(50) NOT NULL DEFAULT '0' COMMENT '分类的顶级父目录，仅供分类使用',
  PRIMARY KEY  (`catid`),
  KEY `url` (`url`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='栏目表';


DROP TABLE IF EXISTS `#@__collect`;
CREATE TABLE IF NOT EXISTS `#@__collect` (
  `c_id` int(11) NOT NULL auto_increment,
  `name` varchar(250) NOT NULL COMMENT '结点名称',
  `source_lang` varchar(20) NOT NULL COMMENT '编码',
  `source_type` varchar(10) NOT NULL COMMENT '数据来源类型',
  `source_url` varchar(250) NOT NULL COMMENT '结点url',
  `profiex_url` varchar(100) NOT NULL COMMENT 'url前缀',
  `prefix_img` varchar(100) NOT NULL COMMENT '图片地址前缀',
  `html_start` varchar(250) NOT NULL COMMENT 'html开始区域',
  `html_end` varchar(250) NOT NULL COMMENT 'html结束区域',
  `have_is` varchar(250) NOT NULL COMMENT '必须包含',
  `have_not` varchar(250) NOT NULL COMMENT '不包含',
  `into_catid` int(11) NOT NULL COMMENT '导入栏目',
  `into_is` int(11) NOT NULL,
  `replace` text NOT NULL COMMENT '替换设置',
  `create_time` int(11) NOT NULL COMMENT '创建时间',
  `update_time` int(11) NOT NULL COMMENT '修改时间',
  PRIMARY KEY  (`c_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `#@__collect_page`;
CREATE TABLE IF NOT EXISTS `#@__collect_page` (
  `pid` int(11) NOT NULL auto_increment,
  `c_id` int(11) default NULL COMMENT '结点编号',
  `title` varchar(200) default NULL COMMENT '文章标题',
  `content` text COMMENT '文章内容',
  `source` varchar(100) default NULL COMMENT '文章作者',
  `link` varchar(250) default NULL COMMENT '文章链接',
  `thumb` text COMMENT '文章图片',
  `create_time` int(11) default NULL COMMENT '创建时间',
  `status` int(11) NOT NULL COMMENT '是否导入',
  PRIMARY KEY  (`pid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `#@__comment`;
CREATE TABLE IF NOT EXISTS `#@__comment` (
  `id` smallint(5) NOT NULL AUTO_INCREMENT,
  `catid` smallint(5) NOT NULL COMMENT '栏目ID',
  `newsid` smallint(5) NOT NULL,
  `comment` text NOT NULL,
  `userid` int(10) unsigned DEFAULT '0',
  `username` char(20) DEFAULT NULL,
  `status` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `ip` char(15) NOT NULL,
  `create_time` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `hidden` (`status`,`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='评论表';


DROP TABLE IF EXISTS `#@__content`;
CREATE TABLE IF NOT EXISTS `#@__content` (
  `cid` mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `catid` smallint(5) unsigned NOT NULL COMMENT '栏目ID',
  `title` char(80) NOT NULL DEFAULT '' COMMENT '标题',
  `style` varchar(50) DEFAULT NULL COMMENT '样式',
  `thumb` char(100) DEFAULT NULL COMMENT '缩略图',
  `brcode` varchar(255) default NULL COMMENT '二维码',
  `attr` set('top','scroll','hot') DEFAULT NULL COMMENT '文档属性',
  `description` varchar(250) DEFAULT NULL COMMENT '栏目摘要',
  `status` tinyint(2) NOT NULL DEFAULT '0' COMMENT '状态：0=待审,9=终审,-1=回收站',
  `sort` tinyint(4) unsigned DEFAULT '1' COMMENT '排序',
  `url` char(100) DEFAULT NULL COMMENT '链接',
  `template` varchar(50) DEFAULT NULL COMMENT '模板名称',
  `seokeywords` char(200) DEFAULT NULL COMMENT '关键字',
  `seodescription` char(255) DEFAULT NULL COMMENT '描述',
  `seotitle` varchar(100) DEFAULT NULL COMMENT 'SEO标题',
  `user_id` int(10) unsigned DEFAULT '0' COMMENT '用户ID',
  `username` varchar(50) DEFAULT NULL COMMENT '用户名',
  `create_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '建立时间',
  `update_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`cid`),
  KEY `status` (`status`,`sort`,`cid`),
  KEY `listorder` (`catid`,`status`,`sort`,`cid`),
  KEY `catid` (`catid`,`status`,`cid`),
  KEY `updatetime` (`catid`,`status`,`update_time`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='内容模型主表';


DROP TABLE IF EXISTS `#@__content_article`;
CREATE TABLE IF NOT EXISTS `#@__content_article` (
  `cid` int(10) unsigned NOT NULL COMMENT '主表ID',
  `fulltitle` varchar(60) DEFAULT NULL COMMENT '完整标题',
  `content` mediumtext COMMENT '详细内容',
  `resource` varchar(255) DEFAULT '本地' COMMENT '文章来源',
  `classify_select` smallint(5) DEFAULT '0' COMMENT '分类管理',
    `weibo` tinyint(3) default '1' COMMENT '同步到微博',
  PRIMARY KEY  (`cid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='文章发布模型扩展表';



DROP TABLE IF EXISTS `#@__content_count`;
CREATE TABLE IF NOT EXISTS `#@__content_count` (
  `cid` mediumint(8) unsigned NOT NULL COMMENT '主表ID',
  `hits` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '浏览量',
  `comments` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT '评论数',
  `comments_checked` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT '已回复评论数',
  PRIMARY KEY  (`cid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='内容模型统计表';


DROP TABLE IF EXISTS `#@__content_download`;
CREATE TABLE IF NOT EXISTS `#@__content_download` (
  `cid` int(10) unsigned NOT NULL COMMENT '主表ID',
  `fulltitle` varchar(60) DEFAULT NULL COMMENT '完整标题',
  `content` mediumtext COMMENT '详细内容',
  `downfile` varchar(250) DEFAULT NULL COMMENT '文件下载',
  `type` char(10) DEFAULT 'pdf' COMMENT '文件类型',
  `filesize` varchar(255) DEFAULT '未知' COMMENT '文件大小',
  `version` varchar(255) DEFAULT NULL COMMENT '版本号',
  `language` varchar(10) DEFAULT '简体中文' COMMENT '软件语言',
  `systems` varchar(255) DEFAULT 'Win2000/WinXP/Win2003' COMMENT '软件平台',
  PRIMARY KEY (`cid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='文件下载模型扩展表';


DROP TABLE IF EXISTS `#@__content_magazine`;
CREATE TABLE IF NOT EXISTS `#@__content_magazine` (
  `cid` int(10) unsigned NOT NULL COMMENT '主表ID',
  `fulltitle` varchar(60) default NULL COMMENT '完整标题',
  `content` mediumtext COMMENT '详细内容',
  `resource` varchar(255) default '本地' COMMENT '文章来源',
  `classify_select` smallint(5) default '0' COMMENT '分类管理',
  PRIMARY KEY  (`cid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='报刊模型扩展表';


DROP TABLE IF EXISTS `#@__content_picture`;
CREATE TABLE IF NOT EXISTS `#@__content_picture` (
  `cid` int(10) unsigned NOT NULL COMMENT '主表ID',
  `fulltitle` varchar(60) DEFAULT NULL COMMENT '完整标题',
  `content` mediumtext COMMENT '详细内容',
  `big_pic` char(100) DEFAULT NULL COMMENT '显示图片',
  `images` text COMMENT '组图',
    `weibo` tinyint(3) default '1' COMMENT '同步到微博',
  PRIMARY KEY  (`cid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='图片欣赏模型扩展表';


DROP TABLE IF EXISTS `#@__content_product`;
CREATE TABLE IF NOT EXISTS `#@__content_product` (
  `cid` int(10) unsigned NOT NULL COMMENT '主表ID',
  `fulltitle` varchar(60) DEFAULT NULL COMMENT '完整标题',
  `content` mediumtext COMMENT '详细内容',
  `big_pic` char(100) DEFAULT NULL COMMENT '显示图片',
  `images` text COMMENT '产品组图',
  `size` varchar(255) DEFAULT NULL COMMENT '产品型号',
  `unit` char(10) DEFAULT '个' COMMENT '产品单位',
  `market_price` varchar(255) DEFAULT NULL COMMENT '市场价格',
  `price` varchar(255) DEFAULT NULL COMMENT '优惠价',
    `weibo` tinyint(3) default '1' COMMENT '同步到微博',
  PRIMARY KEY  (`cid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='产品展示模型扩展表';


DROP TABLE IF EXISTS `#@__content_tag`;
CREATE TABLE IF NOT EXISTS `#@__content_tag` (
  `tagid` smallint(5) NOT NULL COMMENT 'tagID',
  `keyid` varchar(20) NOT NULL COMMENT '其他表的ID',
  PRIMARY KEY  (`tagid`,`keyid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='tag与其他内容的中间表';


DROP TABLE IF EXISTS `#@__content_video`;
CREATE TABLE IF NOT EXISTS `#@__content_video` (
  `cid` int(10) unsigned NOT NULL COMMENT '主表ID',
  `fulltitle` varchar(60) DEFAULT NULL COMMENT '完整标题',
  `content` mediumtext COMMENT '详细内容',
  `video` varchar(250) DEFAULT NULL COMMENT '视频地址',
  PRIMARY KEY (`cid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='视频播放模型扩展表';


DROP TABLE IF EXISTS `#@__friendlink`;
CREATE TABLE IF NOT EXISTS `#@__friendlink` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `type_id` mediumint(9) DEFAULT NULL,
  `type_name` varchar(255) DEFAULT NULL,
  `style` varchar(50) NOT NULL COMMENT '链接样式',
  `name` varchar(50) NOT NULL COMMENT '链接名称',
  `url` varchar(255) NOT NULL COMMENT '链接地址',
  `logo` varchar(255) DEFAULT NULL COMMENT 'logo链接',
  `notes` text COMMENT '链接的提示文字',
  `user_id` int(10) NOT NULL COMMENT '添加者ID',
  `username` varchar(50) DEFAULT NULL COMMENT '用户名',
  `sort` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT '排序',
  `created` int(10) unsigned DEFAULT NULL COMMENT '添加时间',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '1',
  PRIMARY KEY (`id`),
  KEY `typeid` (`sort`,`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='友情链接' AUTO_INCREMENT=1 ;


DROP TABLE IF EXISTS `#@__friendlink_type`;
CREATE TABLE IF NOT EXISTS `#@__friendlink_type` (
  `type_id` mediumint(9) NOT NULL AUTO_INCREMENT,
  `type_name` varchar(255) NOT NULL,
  PRIMARY KEY (`type_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC AUTO_INCREMENT=3 ;

INSERT INTO `#@__friendlink_type` (`type_id`, `type_name`) VALUES
(1, '文字链接'),
(2, '图片链接');


DROP TABLE IF EXISTS `#@__guestbook`;
CREATE TABLE IF NOT EXISTS `#@__guestbook` (
  `id` smallint(5) NOT NULL auto_increment,
  `catid` smallint(5) NOT NULL COMMENT '栏目ID',
  `title` char(80) NOT NULL,
  `content` text NOT NULL,
  `reply` text NOT NULL,
  `userid` int(10) unsigned DEFAULT '0',
  `username` char(20) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `qq` char(15) DEFAULT NULL,
  `homepage` char(25) DEFAULT NULL,
  `status` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `ip` char(15) NOT NULL,
  `addtime` int(10) unsigned NOT NULL DEFAULT '0',
  `replyer` char(20) NOT NULL,
  `replytime` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY  (`id`),
  KEY `hidden` (`status`,`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='留言板' AUTO_INCREMENT=1 ;


DROP TABLE IF EXISTS `#@__job`;
CREATE TABLE IF NOT EXISTS `#@__job` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `catid` mediumint(9) DEFAULT NULL,
  `user_id` mediumint(9) DEFAULT NULL,
  `username` varchar(255) DEFAULT NULL,
  `title` varchar(30) NOT NULL DEFAULT '' COMMENT '职位名称',
  `guerdon` varchar(30) NOT NULL DEFAULT '' COMMENT '薪酬待遇',
  `department` varchar(30) NOT NULL DEFAULT '' COMMENT '所属部门',
  `user_sex` varchar(5) NOT NULL DEFAULT '' COMMENT '性别要求',
  `user_age` varchar(20) NOT NULL DEFAULT '' COMMENT '年龄要求',
  `job_number` int(10) NOT NULL DEFAULT '0' COMMENT '招聘人数',
  `degree` varchar(30) NOT NULL DEFAULT '' COMMENT '学历要求',
  `end_time` int(10) NOT NULL COMMENT '到期时间',
  `place` varchar(30) NOT NULL DEFAULT '' COMMENT '工作地点',
  `experience` varchar(30) NOT NULL DEFAULT '' COMMENT '职业经验',
  `notes` text NOT NULL COMMENT '职位说明',
  `linkman` varchar(20) NOT NULL DEFAULT '' COMMENT '招聘主管',
  `im` varchar(60) NOT NULL DEFAULT '' COMMENT 'QQ或MSN',
  `email` varchar(60) NOT NULL DEFAULT '' COMMENT '招聘邮箱',
  `tel` varchar(20) NOT NULL DEFAULT '' COMMENT '招聘电话',
  `address` varchar(200) NOT NULL DEFAULT '' COMMENT '面试地址',
  `filename` varchar(60) NOT NULL DEFAULT '' COMMENT '空白简历',
  `sort` mediumint(9) NOT NULL DEFAULT '1',
  `status` tinyint(1) NOT NULL DEFAULT '0',
  `create_time` int(10) NOT NULL,
  `update_time` int(10) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='招聘职位表';


DROP TABLE IF EXISTS `#@__job_apply`;
CREATE TABLE IF NOT EXISTS `#@__job_apply` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `job_id` int(11) NOT NULL DEFAULT '0',
  `job_title` varchar(255) DEFAULT '' COMMENT '应聘职位',
  `user_name` varchar(255) NOT NULL DEFAULT '' COMMENT '姓名',
  `user_sex` varchar(32) DEFAULT '' COMMENT '性别',
  `user_age` varchar(3) DEFAULT '0' COMMENT '年龄',
  `user_card` varchar(64) DEFAULT '' COMMENT '身份证',
  `user_phone` varchar(64) DEFAULT '0' COMMENT '联系电话',
  `user_im` varchar(64) DEFAULT '' COMMENT 'QQ',
  `user_email` varchar(64) DEFAULT '' COMMENT '邮箱',
  `user_address` varchar(255) DEFAULT '' COMMENT '地址',
  `end_time` varchar(255) DEFAULT '' COMMENT '到岗时间',
  `user_education` varchar(255) DEFAULT '' COMMENT '学历',
  `education_training` text COMMENT '填写您的教育培训经历',
  `work_experience` text COMMENT '填写您的工作经历',
  `filename` varchar(255) DEFAULT NULL COMMENT '简历',
  `status` varchar(255) NOT NULL DEFAULT '未阅',
  `create_time` int(10) NOT NULL,
  `update_time` int(10) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;


DROP TABLE IF EXISTS `#@__keylink`;
CREATE TABLE IF NOT EXISTS `#@__keylink` (
  `id` smallint(5) unsigned NOT NULL auto_increment,
  `word` char(40) NOT NULL COMMENT '关键字',
  `url` char(100) NOT NULL COMMENT '链接',
  `sort` smallint(5) unsigned NOT NULL DEFAULT '1' COMMENT '排序',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='关键字链接';


DROP TABLE IF EXISTS `#@__magazine`;
CREATE TABLE IF NOT EXISTS `#@__magazine` (
  `id` int(11) NOT NULL auto_increment COMMENT '编号',
  `catid` int(11) NOT NULL COMMENT '栏目编号',
  `parentid` int(11) NOT NULL COMMENT '父级编号',
  `title` varchar(100) NOT NULL COMMENT '标题',
  `description` varchar(200) default NULL COMMENT '简介',
  `images` varchar(200) NOT NULL COMMENT '封面',
  `img_map` text NOT NULL COMMENT '图片地图',
  `create_time` int(11) NOT NULL COMMENT '创建时间',
  `update_time` int(11) NOT NULL COMMENT '修改时间',
  `sort` int(11) NOT NULL default '1' COMMENT '排序',
  `content_id` varchar(50) NOT NULL COMMENT '新闻编号',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='报刊模型表' ;


DROP TABLE IF EXISTS `#@__menu`;
CREATE TABLE IF NOT EXISTS `#@__menu` (
  `menuid` smallint(5) unsigned NOT NULL auto_increment,
  `parentid` smallint(5) unsigned NOT NULL DEFAULT '0',
  `name` char(20) DEFAULT NULL COMMENT '菜单名称',
  `url` char(100) DEFAULT NULL COMMENT '菜单链接',
  `target` char(15) NOT NULL DEFAULT 'mainFrame' COMMENT '打开窗口',
  `rolenames` char(100) DEFAULT NULL COMMENT '可浏览的用户角色',
  `isopen` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否打开',
  `sort` smallint(5) unsigned NOT NULL DEFAULT '1' COMMENT '排序',
  `keyid` varchar(20) DEFAULT NULL COMMENT '唯一key，供程序使用',
  `issystem` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否系统菜单，系统菜单不能删除',
  PRIMARY KEY  (`menuid`),
  KEY `parentid` (`parentid`,`sort`,`menuid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='菜单表' AUTO_INCREMENT=64 ;

INSERT INTO `#@__menu` (`menuid`, `parentid`, `name`, `url`, `target`, `rolenames`, `isopen`, `sort`, `keyid`, `issystem`) VALUES
(1, 0, '后台顶部菜单', '', '_self', 'array (\n  0 => ''administrator'',\n)', 1, 1, NULL, 1),
(2, 0, '会员中心', '', '_self', 'array (\n  0 => ''administrator'',\n)', 1, 1, NULL, 1),
(3, 0, '会员中心快捷菜单', '', '_self', 'array (\n  0 => ''administrator'',\n)', 1, 1, NULL, 1),
(4, 1, '内容管理', '', 'left', 'array (\n  0 => ''administrator'',\n)', 1, 1, NULL, 0),
(5, 1, '模块管理', '', 'left', 'array (\n  0 => ''administrator'',\n)', 1, 1, NULL, 0),
(6, 1, '用户管理', '', 'left', 'array (\n  0 => ''administrator'',\n)', 1, 1, NULL, 0),
(7, 1, '栏目管理', '', 'left', 'array (\n  0 => ''administrator'',\n)', 1, 1, NULL, 0),
(8, 1, '系统设置', '', 'left', 'array (\n  0 => ''administrator'',\n)', 1, 1, NULL, 0),
(9, 1, '模板管理', '', 'left', 'array (\n  0 => ''administrator'',\n)', 1, 1, NULL, 0),
(10, 1, '附表管理', '', 'left', 'array (\n  0 => ''administrator'',\n  1 => ''banmanage'',\n)', 1, 4, NULL, 0),
(11, 6, '会员管理', '?m=fuser&a=manage', 'mainFrame', 'array (\n  0 => ''administrator'',\n)', 1, 1, NULL, 0),
(12, 6, '管理员管理', '?m=fmanager&a=manage', 'mainFrame', 'array (\n  0 => ''administrator'',\n)', 1, 1, NULL, 0),
(13, 6, '个人信息', '?m=fprofile&a=pwd', 'mainFrame', 'array (\n  0 => ''administrator'',\n)', 1, 1, NULL, 0),
(14, 7, '添加栏目', '?m=fcategory&a=add', 'mainFrame', 'array (\n  0 => ''administrator'',\n)', 1, 1, NULL, 0),
(15, 7, '管理栏目', '?m=fcategory&a=manage', 'mainFrame', 'array (\n  0 => ''administrator'',\n)', 1, 1, NULL, 0),
(16, 7, '管理分类', '?m=fcategory&a=manage&cattype=cla', 'mainFrame', 'array (\n  0 => ''administrator'',\n)', 1, 1, NULL, 0),
(17, 7, '更新栏目缓存', '?m=fcache&a=category', 'mainFrame', 'array (\n  0 => ''administrator'',\n)', 1, 1, NULL, 0),
(18, 8, '网站配置', '?m=fset&a=set&selected=1', 'mainFrame', 'array (\n  0 => ''administrator'',\n)', 1, 1, NULL, 0),
(19, 8, '相关设置', '?m=fset&a=menu&do=manage', 'mainFrame', 'array (\n  0 => ''administrator'',\n)', 1, 1, NULL, 0),
(20, 8, '系统工具', '?m=findex&a=system', 'mainFrame', 'array (\n  0 => ''administrator'',\n)', 0, 1, NULL, 0),
(21, 8, '模型管理', '?m=fmodel&a=manage&sidetable=1', 'mainFrame', 'array (\n  0 => ''administrator'',\n)', 0, 1, NULL, 0),
(22, 8, '模块管理', '?m=fmodule&a=manage', 'mainFrame', 'array (\n  0 => ''administrator'',\n)', 0, 1, NULL, 0),
(23, 8, '数据库管理', '?m=fdb&a=backup', 'mainFrame', 'array (\n  0 => ''administrator'',\n)', 0, 1, NULL, 0),
(24, 8, '操作注册管理', '?m=frbac&a=manage', 'mainFrame', 'array (\n  0 => ''administrator'',\n)', 0, 1, NULL, 0),
(25, 9, '模板管理', '?m=ftpl&a=manage', 'mainFrame', 'array (\n  0 => ''administrator'',\n)', 1, 1, NULL, 0),
(26, 9, '标签向导', '?m=ftpl&a=create_tag', 'mainFrame', 'array (\n  0 => ''administrator'',\n)', 1, 1, NULL, 0),
(27, 9, '更新模板缓存', '?m=ftpl&a=cache', 'mainFrame', 'array (\n  0 => ''administrator'',\n)', 1, 1, NULL, 0),
(28, 11, '添加会员', '?m=fuser&a=add&moduleid=2', 'mainFrame', 'array (\n  0 => ''administrator'',\n)', 1, 1, NULL, 0),
(29, 11, '管理会员', '?m=fuser&a=manage', 'mainFrame', 'array (\n  0 => ''administrator'',\n)', 1, 1, NULL, 0),
(30, 11, '会员角色管理', '?m=fmember&a=manage_group&moduleid=2', 'mainFrame', 'array (\n  0 => ''administrator'',\n)', 1, 1, NULL, 0),
(31, 11, '会员模型管理', '?m=fmember&a=manage_model&moduleid=2', 'mainFrame', 'array (\n  0 => ''administrator'',\n)', 1, 1, NULL, 0),
(32, 11, '会员模块配置', '?m=fmember&a=setting_module', 'mainFrame', 'array (\n  0 => ''administrator'',\n)', 1, 1, NULL, 0),
(33, 12, '添加管理员', '?m=fmanager&a=add&modelid=6', 'mainFrame', 'array (\n  0 => ''administrator'',\n)', 1, 1, NULL, 0),
(34, 12, '管理员管理', '?m=fmanager&a=manage', 'mainFrame', 'array (\n  0 => ''administrator'',\n)', 1, 1, NULL, 0),
(35, 12, '添加角色', '?m=frole&a=add&modelid=6', 'mainFrame', 'array (\n  0 => ''administrator'',\n)', 1, 1, NULL, 0),
(36, 12, '管理角色', '?m=frole&a=manage&modelid=6', 'mainFrame', 'array (\n  0 => ''administrator'',\n)', 1, 1, NULL, 0),
(37, 13, '修改资料', '?m=fprofile&a=edit', 'mainFrame', 'array (\n  0 => ''administrator'',\n)', 1, 1, NULL, 0),
(38, 13, '修改密码', '?m=fprofile&a=pwd', 'mainFrame', 'array (\n  0 => ''administrator'',\n)', 1, 1, NULL, 0),
(39, 18, '基本信息', '?m=fset&a=set&selected=1', 'mainFrame', 'array (\n  0 => ''administrator'',\n)', 1, 1, NULL, 0),
(40, 18, '网站设置', '?m=fset&a=set&selected=2', 'mainFrame', 'array (\n  0 => ''administrator'',\n)', 1, 1, NULL, 0),
(41, 18, '邮件设置', '?m=fset&a=set&selected=3', 'mainFrame', 'array (\n  0 => ''administrator'',\n)', 1, 1, NULL, 0),
(42, 18, '附件设置', '?m=fset&a=set&selected=4', 'mainFrame', 'array (\n  0 => ''administrator'',\n)', 1, 1, NULL, 0),
(43, 18, 'FTP设置', '?m=fset&a=set&selected=5', 'mainFrame', 'array (\n  0 => ''administrator'',\n)', 1, 1, NULL, 0),
(44, 18, '撰写设置', '?m=fset&a=set&selected=6', 'mainFrame', 'array (\n  0 => ''administrator'',\n)', 1, 1, NULL, 0),
(45, 19, '菜单管理', '?m=fset&a=menu&do=manage', 'mainFrame', 'array (\n  0 => ''administrator'',\n)', 1, 1, NULL, 0),
(46, 19, 'TAG管理', '?m=ftag&a=manage', 'mainFrame', 'array (\n  0 => ''administrator'',\n)', 1, 1, NULL, 0),
(47, 19, '参数管理', '?m=fset&a=arg&do=manage', 'mainFrame', 'array (\n  0 => ''administrator'',\n)', 1, 1, NULL, 0),
(48, 19, '页面带连接词设置', '?m=fset&a=keylink&do=manage', 'mainFrame', 'array (\n  0 => ''administrator'',\n)', 1, 1, NULL, 0),
(49, 19, 'Baibu/Google地图', '?m=fhtml&a=sitemaps', 'mainFrame', 'array (\n  0 => ''administrator'',\n)', 1, 1, NULL, 0),
(50, 20, '运行环境诊断', '?m=findex&a=system', 'mainFrame', 'array (\n  0 => ''administrator'',\n)', 0, 1, NULL, 0),
(51, 21, '添加模型', '?m=fmodel&a=add_model', 'mainFrame', 'array (\n  0 => ''administrator'',\n)', 0, 1, NULL, 0),
(52, 21, '管理模型', '?m=fmodel&a=manage&sidetable=1', 'mainFrame', 'array (\n  0 => ''administrator'',\n)', 0, 1, NULL, 0),
(53, 22, '注册模块', '?m=fmodule&a=add', 'mainFrame', 'array (\n  0 => ''administrator'',\n)', 0, 1, NULL, 0),
(54, 22, '管理模块', '?m=fmodule&a=manage', 'mainFrame', 'array (\n  0 => ''administrator'',\n)', 0, 1, NULL, 0),
(55, 23, '数据库备份', '?m=fdb&a=backup', 'mainFrame', 'array (\n  0 => ''administrator'',\n)', 0, 1, NULL, 0),
(56, 23, '数据库恢复', '?m=fdb&a=restore', 'mainFrame', 'array (\n  0 => ''administrator'',\n)', 0, 1, NULL, 0),
(57, 24, '后台操作注册管理', '?m=frbac&a=manage&appname=admin', 'mainFrame', 'array (\n  0 => ''administrator'',\n)', 0, 1, NULL, 0),
(58, 24, '会员操作注册管理', '?m=frbac&a=manage&appname=front', 'mainFrame', 'array (\n  0 => ''administrator'',\n)', 0, 1, NULL, 0),
(59, 5, '广告管理', '?m=fad&a=manage', 'mainFrame', 'array (\n  0 => ''administrator'',\n)', 0, 1, NULL, 0),
(60, 5, '友情链接', '?m=ffriendlink&a=manage', 'mainFrame', 'array (\n  0 => ''administrator'',\n)', 0, 1, NULL, 0),
(61, 5, '订单管理', '?m=forder&a=manage', 'mainFrame', 'array (\n  0 => ''administrator'',\n)', 0, 1, NULL, 0),
(62, 5, '评论管理', '?m=fcomment&a=manage', 'mainFrame', 'array (\n  0 => ''administrator'',\n)', 0, 1, NULL, 0),
(63, 59, '添加广告', '?m=fad&a=add', 'mainFrame', 'array (\n  0 => ''administrator'',\n)', 0, 1, NULL, 0),
(64, 59, '管理广告', '?m=fad&a=manage', 'mainFrame', 'array (\n  0 => ''administrator'',\n)', 0, 1, NULL, 0),
(65, 5, '采集管理', '#', 'mainFrame', 'array (\n  0 => ''administrator'',\n)', 0, 1, NULL, 0),
(66, 65, '节点管理', '?m=fcollect&a=manage', 'mainFrame', 'array (\n  0 => ''administrator'',\n)', 0, 1, NULL, 0),
(67, 21, '副表模型', '?m=fmodel&a=manage&sidetable=2', 'mainFrame', 'array (\n  0 => ''member'',\n  1 => ''banmanage'',\n)', 0, 1, NULL, 0),
(68, 5, '跟单专用工具', '#', 'mainFrame', 'array (\n  0 => ''administrator'',\n  1 => ''member'',\n)', 0, 1, NULL, 0),
(69, 68, '测试数据添加', '?m=fplugin&a=testdata', 'mainFrame', 'array (\n  0 => ''administrator'',\n  1 => ''member'',\n)', 0, 1, NULL, 0),
(70, 5, '二维码','?m=fbrcode&a=manage','mainFrame','array (\n  0 => \'administrator\',\n)','0','1',NULL,'0'),
(71, 70, '二维码生成器','?m=fbrcode&a=manage','mainFrame','array (\n  0 => \'administrator\',\n)','0','1',NULL,'0'),
(72, 70, '批量生成二维码','?m=fbrcode&a=batch','mainFrame','array (\n  0 => \'administrator\',\n)','0','1',NULL,'0'),
(73, 1, '手机站', '#', 'left', 'array (\n  0 => ''administrator'',\n)', 1, 1, NULL, 0),
(74, 73, '栏目管理', '#', 'mainFrame', 'array (\n  0 => ''administrator'',\n)', 0, 1, NULL, 0),
(75, 74, '管理手机栏目', 'admin.php?m=fmobilemenu&a=menumanage', 'mainFrame', 'array (\n  0 => ''administrator'',\n)', 0, 1, NULL, 0),
(76, 74, '添加栏目', '?m=fmobilemenu&a=add', 'mainFrame', 'array (\n  0 => ''administrator'',\n)', 0, 1, NULL, 0),
(82, 74, '手机模版批量生成', '?m=fmobile&a=huancun', 'mainFrame', 'array (\n  0 => ''administrator'',\n)', 0, 1, NULL, 0),
(77, 73, '手机站广告管理', '#', 'mainFrame', 'array (\n  0 => ''administrator'',\n)', 0, 1, NULL, 0),
(78, 77, '添加手机广告', '?m=fmobilead&a=add', 'mainFrame', 'array (\n  0 => ''administrator'',\n)', 0, 1, NULL, 0),
(79, 77, '管理手机广告', '?m=fmobilead&a=manage', 'mainFrame', 'array (\n  0 => ''administrator'',\n)', 0, 1, NULL, 0),
(80, 73, '模版管理', '#', 'mainFrame', 'array (\n  0 => ''administrator'',\n)', 0, 1, NULL, 0),
(81, 80, '模版设置', '?m=fmobile&a=setting', 'mainFrame', 'array (\n  0 => ''administrator'',\n)', 0, 1, NULL, 0),
(83, 1, '微博管理', '#', 'left', 'array (\n  0 => ''administrator'',\n  1 => ''member'',\n)', 0, 1, NULL, 0),
(84, 83, '微博转发', '?m=fblog&a=manage', 'mainFrame', 'array (\n  0 => ''administrator'',\n  1 => ''member'',\n)', 0, 1, NULL, 0),
(85, 83, '微博帐号管理', '?m=fblog&a=usermanage', 'mainFrame', 'array (\n  0 => ''administrator'',\n  1 => ''member'',\n)', 0, 1, NULL, 0);







DROP TABLE IF EXISTS `#@__mobilead`;
CREATE TABLE IF NOT EXISTS `#@__mobilead` (
  `aid` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '手机广告位ID',
  `name` varchar(60) NOT NULL,
  `classes` tinyint(3) NOT NULL COMMENT '1是图片 2是文字 ',
  `notes` text NOT NULL,
  `url` varchar(50) DEFAULT NULL,
  `create_time` int(11) NOT NULL,
  `out_time` int(11) DEFAULT NULL,
  PRIMARY KEY (`aid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=12 ;



DROP TABLE IF EXISTS `#@__mobilemenu`;
CREATE TABLE IF NOT EXISTS `#@__mobilemenu`  (
  `id` tinyint(5) unsigned NOT NULL AUTO_INCREMENT COMMENT '手机栏目id',
  `catid` tinyint(5) NOT NULL COMMENT '挂靠栏目id',
  `name` varchar(32) NOT NULL COMMENT '手机栏目名称',
  `url` varchar(64) NOT NULL,
  `catdir` varchar(64) NOT NULL,
  `description` varchar(255) NOT NULL COMMENT '手机栏目描述',
  `setting` mediumtext NOT NULL COMMENT '模版设置',
  `sort` tinyint(3) NOT NULL DEFAULT '1' COMMENT '排序',
  `create_time` int(11) NOT NULL COMMENT '创建时间',
  `status` tinyint(3) NOT NULL COMMENT '状态',
  PRIMARY KEY (`id`),
  KEY `create_time` (`create_time`),
  KEY `status` (`status`),
  KEY `sort` (`sort`),
  KEY `catid` (`catid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;





DROP TABLE IF EXISTS `#@__model`;
CREATE TABLE IF NOT EXISTS `#@__model` (
  `modelid` smallint(4) unsigned NOT NULL auto_increment COMMENT '模型ID',
  `name` varchar(30) NOT NULL COMMENT '模型名称',
  `description` varchar(200) NOT NULL COMMENT '模型描述',
  `exttable` varchar(20) DEFAULT NULL COMMENT '模型扩展表名',
  `tablename` varchar(64) DEFAULT NULL COMMENT '完整表名',
  `moduleid` smallint(4) NOT NULL COMMENT '所属模块ID',
  `status` enum('0','1') NOT NULL DEFAULT '0' COMMENT '1=启用、0=禁用',
  `extendable` enum('0','1') NOT NULL DEFAULT '1' COMMENT '是否扩展模型',
  PRIMARY KEY  (`modelid`),
  UNIQUE KEY `tablename` (`tablename`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='模型注册表' AUTO_INCREMENT=14 ;

INSERT INTO `#@__model` (`modelid`, `name`, `description`, `exttable`, `tablename`, `moduleid`, `status`, `extendable`) VALUES
(1, '文章发布', '文章类型的栏目可以采用此模型', 'article', 'content_article', 1, '1', '1'),
(2, '产品展示', '产品类型的栏目可以使用此模型', 'product', 'content_product', 1, '1', '1'),
(3, '图片欣赏', '图片展示类型的栏目可以使用此模型', 'picture', 'content_picture', 1, '1', '1'),
(4, '文件下载', '文件下载类型栏目可以使用此模型', 'download', 'content_download', 1, '1', '1'),
(5, '视频播放', '视频播放的栏目可选择此模型', 'video', 'content_video', 1, '1', '1'),
(6, '系统管理员', '系统管理员模型', 'manager', 'user_manager', 2, '1', '1'),
(7, '个人会员', '个人会员模型', 'person', 'user_person', 2, '1', '1'),
(8, '在线留言', '提交留言和显示留言列表', 'guestbook', 'guestbook', 3, '1', '0'),
(9, '人才招聘', '职位列表、职位详情、提交简历', 'job', 'job', 4, '1', '0'),
(10, '营销网络', '营销网络', 'salenet', 'salenet', 5, '1', '0'),
(11, '在线投票', '投票调查', 'vote_subject', 'vote_subject', 6, '1', '0'),
(12, '在线问答', '问答模块', 'ask', 'ask', 7, '1', '0'),
(13, '活动报名', '活动报名', 'activity', 'activity', 8, '1', '0'),
(14, '电子报刊', '电子报刊', 'magazine', 'content_magazine', 9, '1', '1'),
(15, '电子杂志', '电子杂志', 'press', 'press', 10, '1', '1');


DROP TABLE IF EXISTS `#@__model_field`;
CREATE TABLE IF NOT EXISTS `#@__model_field` (
  `fieldid` mediumint(8) unsigned NOT NULL auto_increment,
  `modelid` tinyint(4) unsigned NOT NULL COMMENT '所属模型ID',
  `field` varchar(20) NOT NULL COMMENT '数据库字段名',
  `name` varchar(30) NOT NULL COMMENT '字段表单显示名称',
  `tips` text NOT NULL COMMENT '提示信息',
  `css` varchar(50) DEFAULT NULL COMMENT '元素的css样式名称',
  `parent_css` varchar(50) DEFAULT NULL COMMENT '父容器css',
  `minlength` int(10) unsigned DEFAULT NULL COMMENT '最小长度',
  `maxlength` int(10) unsigned DEFAULT NULL COMMENT '最大长度',
  `required` enum('0','1') NOT NULL DEFAULT '0' COMMENT '必填?',
  `pattern` varchar(255) DEFAULT NULL COMMENT '正则验证表达式',
  `errortips` varchar(255) DEFAULT NULL COMMENT '表单错误提示',
  `formtype` varchar(20) DEFAULT NULL COMMENT '表单类型',
  `setting` text DEFAULT NULL COMMENT '配置',
  `dbname` varchar(64) DEFAULT NULL COMMENT '挂靠副表表名',
  `dbkey` varchar(32) DEFAULT NULL COMMENT '挂靠副表显示值',
  `dbvalue` varchar(32) DEFAULT NULL COMMENT '挂靠副表实际值',
  `formattribute` varchar(255) DEFAULT NULL COMMENT '表单元素属性',
  `systype` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '0=自定义字段、1=主表默认字段、2=主键字段',
  `sort` mediumint(8) unsigned NOT NULL DEFAULT '1' COMMENT '排序',
  `status` enum('0','1') NOT NULL DEFAULT '1' COMMENT '1=启用、0=禁用',
  `card` smallint(1) DEFAULT '1' COMMENT '所属选项卡',
  `listshow` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否在列表中显示',
  PRIMARY KEY  (`fieldid`),
  KEY `modelid` (`modelid`,`status`),
  KEY `field` (`field`,`modelid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='模型字段表';

INSERT INTO `#@__model_field` (`modelid`, `field`, `name`, `tips`, `css`, `parent_css`, `minlength`, `maxlength`, `required`, `pattern`, `errortips`, `formtype`, `setting`, `formattribute`, `systype`, `sort`, `status`, `card`, `listshow`) VALUES
(1, 'cid', 'ID', '', NULL, NULL, NULL, NULL, '0', NULL, '', 'id', '', NULL, 2, 1, '1', 1,0),
(1, 'catid', '所属栏目', '选择到发布到的栏目，默认当前栏目', NULL, NULL, 0, 0, '1', NULL, '请选择栏目', 'catid', 'array (\n  ''defaultvalue'' => '''',\n)', NULL, 1, 1, '1', 1,0),
(1, 'classify_select', '分类管理', '分类目录', NULL, NULL, 0, 0, '0', NULL, '', 'select', 'NULL', NULL, 1, 1, '0', 1,0),
(1, 'title', '标题', '字数长度在1~60个字间', 'input', NULL, 0, 255, '1', NULL, '标题在1~60个字间', 'title', 'array (\n  ''size'' => ''50'',\n)', NULL, 1, 1, '1', 1,0),
(1, 'style', '字型与颜色', '可以选择标题在页面上显示的颜色和字型，未定义则是页面默认定义', NULL, NULL, 0, 0, '0', NULL, '', 'style', 'array (\n  ''style'' => \n  array (\n    ''color'' => '''',\n  ),\n)', NULL, 1, 1, '0', 1,0),
(1, 'resource', '文章来源', '信息的来源，如新浪网', 'input', NULL, 0, 255, '0', NULL, '', 'input', 'array (\n  ''size'' => ''35'',\n  ''defaultvalue'' => ''本站'',\n  ''ispassword'' => ''0'',\n)', NULL, 1, 1, '1', 1,0),
(1, 'thumb', '缩略图', '可从本机上传或者从站内选择', 'input', NULL, 0, 255, '0', NULL, '', 'thumb', 'array (\r\n  ''size'' => ''50'',\r\n  ''defaultvalue'' => '''',\r\n  ''upload_maxsize'' => ''512'',\r\n  ''upload_allowext'' => ''gif|jpg|jpeg|png|bmp'',\r\n  ''isthumb'' => ''0'',\r\n  ''thumb_width'' => ''150'',\r\n  ''thumb_height'' => ''150'',\r\n  ''iswatermark'' => ''0'',\r\n  ''water_path'' => ''images/download.gif'',\r\n)', NULL, 1, 1, '1', 1,0),
(1, 'attr', '文档属性', '', NULL, NULL, 0, 0, '0', NULL, '', 'attr', 'array (\n  ''show'' => \n  array (\n    0 => ''top'',\n    1 => ''hot'',\n    2 => ''scroll'',\n  ),\n)', NULL, 1, 1, '1', 1,0),
(1, 'brcode','二维码图片','自动生成二维码图片','','','0','255','0','','','input','array (\n  \'size\' => \'50\',\n  \'defaultvalue\' => \'\',\n  \'ispassword\' => \'0\',\n)','','1','1','1','1',0),
(1, 'description', '摘要', '默认可以自动截取前200字作为摘要', 'textarea', NULL, 0, 200, '0', NULL, '不能超过200个字', 'textarea', 'array (\n  ''rows'' => ''5'',\n  ''cols'' => ''60'',\n  ''defaultvalue'' => '''',\n)', NULL, 1, 1, '1', 1,0),
(1, 'content', '正文内容', '主体详细内容', NULL, NULL, 0, 0, '0', NULL, '', 'editor', 'array (\r\n  ''toolbar'' => ''advanced'',\r\n  ''width'' => ''550'',\r\n  ''height'' => ''250'',\r\n  ''keywork-link'' => ''0'',\r\n  ''defaultvalue'' => '''',\r\n)', NULL, 1, 1, '1', 1,0),
(1, 'status', '状态', '默认发布，也可以暂时不发布', NULL, NULL, 0, 255, '1', NULL, '', 'box', 'array (\n  ''option'' => ''发布|9\r\n待审|0'',\n  ''boxtype'' => ''radio'',\n  ''fieldtype'' => ''TINYINT'',\n  ''defaultvalue'' => ''9'',\n)', NULL, 1, 1, '1', 1,0),
(1, 'tag', 'TAG', '以空格隔开，输出会有自动提示', 'input', NULL, 0, 255, '0', NULL, '请正确填写【TAG】', 'tag', 'array (\n  ''size'' => ''60'',\n)', NULL, 1, 1, '1', 2,0),
(1, 'seotitle', 'SEO标题', '默认同标题，也可自定义', 'input', NULL, 0, 255, '0', NULL, '不能超过100个字', 'input', 'array (\n  ''size'' => ''60'',\n  ''defaultvalue'' => '''',\n  ''ispassword'' => ''0'',\n)', NULL, 1, 1, '1', 2,0),
(1, 'seokeywords', 'SEO关键字', '默认同TAG，也可自定义', 'textarea', NULL, 0, 250, '0', NULL, '不能超过200个字', 'textarea', 'array (\n  ''rows'' => ''5'',\n  ''cols'' => ''60'',\n  ''defaultvalue'' => '''',\n)', NULL, 1, 1, '1', 2,0),
(1, 'seodescription', 'SEO描述', '默认同摘要，也可自定义', 'textarea', NULL, 0, 250, '0', NULL, '不能超过250个字', 'textarea', 'array (\n  ''rows'' => ''5'',\n  ''cols'' => ''60'',\n  ''defaultvalue'' => '''',\n)', NULL, 1, 1, '1', 2,0),
(1, 'url', '链接地址', '默认自动生成，不包括".html"后缀，不可改', 'input', NULL, 0, 255, '0', NULL, '不能是纯数字', 'input', 'array (\n  ''size'' => ''60'',\n  ''defaultvalue'' => '''',\n  ''ispassword'' => ''0'',\n)', NULL, 1, 1, '1', 2,0),
(1, 'sort', '排序', '默认为 1，越小越靠前', 'input', NULL, 0, 255, '0', NULL, '', 'input', 'array (\n  ''size'' => ''6'',\n  ''defaultvalue'' => ''1'',\n  ''ispassword'' => ''0'',\n)', NULL, 1, 1, '0', 2,0),
(1, 'template', '模板', '默认使用栏目模板，也可以从模板库中选择', 'input', NULL, 0, 255, '0', NULL, '', 'template', 'array (\n  ''size'' => ''50'',\n)', NULL, 1, 1, '0', 2,0),
(1, 'fulltitle', '完整标题', '默认同标题', 'input', NULL, 0, 100, '0', NULL, '在1~100个字间', 'input', 'array (\n  ''size'' => ''60'',\n  ''defaultvalue'' => '''',\n  ''ispassword'' => ''0'',\n)', NULL, 1, 1, '0', 2,0),
(1, 'create_time', '添加时间', '默认当前时间', NULL, NULL, 0, 50, '0', NULL, '格式错误', 'datetime', 'array (\n  ''format'' => ''Y-m-d H:i:s'',\n  ''defaulttype'' => ''1'',\n)', NULL, 1, 1, '1', 2,0),
(1, 'update_time', '更新时间', '默认最后更新时间', NULL, NULL, 0, 50, '0', NULL, '格式错误', 'datetime', 'array (\n  ''format'' => ''Y-m-d H:i:s'',\n  ''defaulttype'' => ''1'',\n)', NULL, 1, 1, '1', 2,0),
(2, 'cid', 'ID', '', NULL, NULL, NULL, NULL, '0', NULL, '', 'id', '', NULL, 2, 1, '1', 1,0),
(2, 'catid', '所属栏目', '选择到发布到的栏目，默认当前栏目', NULL, NULL, 0, 0, '1', NULL, '请选择栏目', 'catid', 'array (\n  ''defaultvalue'' => '''',\n)', NULL, 1, 1, '1', 1,0),
(2, 'title', '产品名称', '字数长度在1~60个字间', 'input', NULL, 1, 60, '1', NULL, '标题在1~60个字间', 'title', 'array (\n  ''size'' => ''50'',\n)', NULL, 1, 1, '1', 1,0),
(2, 'style', '字型与颜色', '可以选择标题在页面上显示的颜色和字型，未定义则是页面默认定义', NULL, NULL, 0, 0, '0', NULL, '', 'style', 'array (\n  ''style'' => \n  array (\n    ''color'' => '''',\n  ),\n)', NULL, 1, 1, '0', 1,0),
(2, 'thumb', '缩略图', '可从本机上传或者从站内选择', 'input', NULL, 0, 255, '0', NULL, '', 'thumb', 'array (\n  ''size'' => ''50'',\n  ''defaultvalue'' => '''',\n  ''upload_maxsize'' => ''512'',\n  ''upload_allowext'' => ''gif|jpg|jpeg|png|bmp'',\n  ''isthumb'' => ''0'',\n  ''thumb_width'' => ''150'',\n  ''thumb_height'' => ''150'',\n  ''iswatermark'' => ''0'',\n  ''water_path'' => ''images/watermark.png'',\n)', NULL, 1, 1, '0', 1,0),
(2, 'big_pic', '显示图片', '在单个页面中显示的大图，图片上传后会自动生成缩略图', 'input', NULL, 0, 255, '0', NULL, '', 'thumb', 'array (\r\n  ''size'' => ''50'',\r\n  ''defaultvalue'' => '''',\r\n  ''upload_maxsize'' => ''2048'',\r\n  ''upload_allowext'' => ''gif|jpg|jpeg|png|bmp'',\r\n  ''isthumb'' => ''1'',\r\n  ''thumb_width'' => ''150'',\r\n  ''thumb_height'' => ''150'',\r\n  ''iswatermark'' => ''1'',\r\n  ''water_path'' => ''images/watermark.png'',\r\n)', NULL, 1, 1, '1', 1,0),
(2, 'images', '产品组图', '产品图片', NULL, NULL, 0, 255, '0', NULL, '', 'images', 'array (\n  ''upload_maxsize'' => ''20480'',\n  ''upload_allowext'' => ''gif|jpg|jpeg|png|bmp'',\n  ''isthumb'' => ''0'',\n  ''thumb_width'' => ''150'',\n  ''thumb_height'' => ''150'',\n  ''iswatermark'' => ''0'',\n  ''water_path'' => ''images/watermark.png'',\n)', NULL, 1, 1, '1', 1,0),
(2, 'size', '产品型号', '产品型号', 'input', NULL, 0, 255, '0', NULL, '', 'input', 'array (\n  ''size'' => ''30'',\n  ''defaultvalue'' => '''',\n  ''ispassword'' => ''0'',\n)', NULL, 1, 1, '1', 1,0),
(2, 'unit', '产品单位', '产品单位', NULL, NULL, 0, 255, '0', NULL, '', 'box', 'array (\n  ''option'' => ''个|个\r\n件|件\r\n台|台\r\n条|条\r\n张|张\r\n本|本\r\n只|只\r\n箱|箱\r\n瓶|瓶\r\n吨|吨\r\nKg|Kg\r\nm|m'',\n  ''boxtype'' => ''select'',\n  ''fieldtype'' => ''CHAR'',\n  ''defaultvalue'' => ''个'',\n)', NULL, 1, 1, '1', 1,0),
(2, 'market_price', '市场价', '产品的市场价格', 'input', NULL, 0, 255, '0', NULL, '', 'input', 'array (\n  ''size'' => ''4'',\n  ''defaultvalue'' => '''',\n  ''ispassword'' => ''0'',\n)', NULL, 1, 1, '1', 1,0),
(2, 'price', '优惠价', '我们的优惠价格', 'input', NULL, 0, 255, '0', NULL, '', 'input', 'array (\n  ''size'' => ''4'',\n  ''defaultvalue'' => '''',\n  ''ispassword'' => ''0'',\n)', NULL, 1, 1, '1', 1,0),
(2, 'attr', '文档属性', '', NULL, NULL, 0, 0, '0', NULL, '', 'attr', 'array (\n  ''show'' => \n  array (\n    0 => ''top'',\n    1 => ''hot'',\n    2 => ''scroll'',\n  ),\n)', NULL, 1, 1, '1', 1,0),
(2, 'brcode','二维码图片','自动生成二维码图片','','','0','255','0','','','input','array (\n  \'size\' => \'50\',\n  \'defaultvalue\' => \'\',\n  \'ispassword\' => \'0\',\n)','','1','1','1','1',0),
(2, 'description', '摘要', '默认可以自动截取前200字作为摘要', 'textarea', NULL, 0, 200, '0', NULL, '不能超过200个字', 'textarea', 'array (\n  ''rows'' => ''5'',\n  ''cols'' => ''60'',\n  ''defaultvalue'' => '''',\n)', NULL, 1, 1, '1', 1,0),
(2, 'content', '详细介绍', '主体详细内容', NULL, NULL, 0, 0, '0', NULL, '', 'editor', 'array (\n  ''toolbar'' => ''advanced'',\n  ''width'' => ''550'',\n  ''height'' => ''250'',\n  ''keywork-link'' => ''0'',\n  ''defaultvalue'' => '''',\n)', NULL, 1, 1, '1', 1,0),
(2, 'status', '状态', '默认发布或暂时不发布', NULL, NULL, 0, 255, '1', NULL, '', 'box', 'array (\n  ''option'' => ''发布|9\r\n待审|0'',\n  ''boxtype'' => ''radio'',\n  ''fieldtype'' => ''TINYINT'',\n  ''defaultvalue'' => ''9'',\n)', NULL, 1, 1, '1', 1,0),
(2, 'tag', 'TAG', '以空格隔开，输出会有自动提示', 'input', NULL, 0, 255, '0', NULL, '请正确填写【TAG】', 'tag', 'array (\n  ''size'' => ''50'',\n)', NULL, 1, 1, '1', 2,0),
(2, 'seotitle', 'SEO标题', '默认同标题，也可自定义', 'input', NULL, 0, 100, '0', NULL, '不能超过100个字', 'input', 'array (\n  ''size'' => ''50'',\n  ''defaultvalue'' => '''',\n  ''ispassword'' => ''0'',\n)', NULL, 1, 1, '1', 2,0),
(2, 'seokeywords', 'SEO关键字', '默认同TAG，也可自定义', 'textarea', NULL, 0, 250, '0', NULL, '不能超过200个字', 'textarea', 'array (\n  ''rows'' => ''6'',\n  ''cols'' => ''60'',\n  ''defaultvalue'' => '''',\n)', NULL, 1, 1, '1', 2,0),
(2, 'seodescription', 'SEO描述', '默认同摘要，也可自定义', 'textarea', NULL, 0, 250, '0', NULL, '不能超过250个字', 'textarea', 'array (\n  ''rows'' => ''6'',\n  ''cols'' => ''60'',\n  ''defaultvalue'' => '''',\n)', NULL, 1, 1, '1', 2,0),
(2, 'url', '链接地址', '默认自动生成，不包括".html"后缀', 'input', NULL, 0, 0, '0', NULL, '不能是纯数字', 'input', 'array (\n  ''size'' => ''50'',\n  ''defaultvalue'' => '''',\n  ''ispassword'' => ''0'',\n)', NULL, 1, 1, '1', 2,0),
(2, 'sort', '排序', '默认为 1，越小越靠前', 'input', NULL, 0, 0, '0', NULL, '', 'input', 'array (\r\n  ''size'' => ''6'',\r\n  ''defaultvalue'' => '''',\r\n)', NULL, 1, 1, '0', 2,0),
(2, 'template', '模板', '默认使用栏目模板，也可以从模板库中选择', 'input', NULL, 0, 200, '0', NULL, '', 'template', 'array (\n  ''size'' => ''50'',\n)', NULL, 1, 1, '0', 2,0),
(2, 'fulltitle', '完整标题', '默认同标题', 'input', NULL, 0, 100, '0', NULL, '在1~100个字间', 'input', 'array (\n  ''size'' => ''60'',\n  ''defaultvalue'' => '''',\n  ''ispassword'' => ''0'',\n)', NULL, 1, 1, '0', 2,0),
(2, 'create_time', '添加时间', '默认当前时间', NULL, NULL, 0, 50, '0', NULL, '格式错误', 'datetime', 'array (\n  ''format'' => ''Y-m-d H:i:s'',\n  ''defaulttype'' => ''1'',\n)', NULL, 1, 1, '1', 2,0),
(2, 'update_time', '更新时间', '默认最后更新时间', NULL, NULL, 0, 50, '0', NULL, '格式错误', 'datetime', 'array (\n  ''format'' => ''Y-m-d H:i:s'',\n  ''defaulttype'' => ''1'',\n)', NULL, 1, 1, '1', 2,0),
(3, 'cid', 'ID', '', NULL, NULL, NULL, NULL, '0', NULL, '', 'id', '', NULL, 2, 1, '1', 1,0),
(3, 'catid', '所属栏目', '选择到发布到的栏目，默认当前栏目', '', NULL, 0, 0, '1', NULL, '请选择栏目', 'catid', 'array (\n  ''defaultvalue'' => '''',\n)', NULL, 1, 1, '1', 1,0),
(3, 'title', '图片名称', '字数长度在1~60个字间', 'input', NULL, 0, 255, '1', NULL, '网站名称在1~60个字间', 'title', 'array (\n  ''size'' => ''60'',\n)', NULL, 1, 1, '1', 1,0),
(3, 'style', '字型与颜色', '可以选择标题在页面上显示的颜色和字型，未定义则是页面默认定义', NULL, NULL, 0, 0, '0', NULL, '', 'style', 'array (\n  ''style'' => \n  array (\n    ''color'' => '''',\n  ),\n)', NULL, 1, 1, '0', 1,0),
(3, 'thumb', '缩略图', '可从本机上传或者从站内选择', 'input', NULL, 0, 255, '0', NULL, '', 'thumb', 'array (\r\n  ''size'' => ''50'',\r\n  ''defaultvalue'' => '''',\r\n  ''upload_maxsize'' => ''512'',\r\n  ''upload_allowext'' => ''gif|jpg|jpeg|png|bmp'',\r\n  ''isthumb'' => ''0'',\r\n  ''thumb_width'' => ''150'',\r\n  ''thumb_height'' => ''150'',\r\n  ''iswatermark'' => ''0'',\r\n  ''water_path'' => ''images/watermark.png'',\r\n)', NULL, 1, 1, '0', 1,0),
(3, 'big_pic', '显示图片', '在页面中显示的单个大图，图片上传后会自动生成缩略图', 'input', NULL, 0, 255, '0', NULL, '', 'thumb', 'array (\n  ''size'' => ''50'',\n  ''defaultvalue'' => '''',\n  ''upload_maxsize'' => ''2048'',\n  ''upload_allowext'' => ''gif|jpg|jpeg|png|bmp'',\n  ''isthumb'' => ''1'',\n  ''thumb_width'' => ''150'',\n  ''thumb_height'' => ''150'',\n  ''iswatermark'' => ''1'',\n  ''water_path'' => ''images/watermark.png'',\n)', NULL, 1, 1, '1', 1,0),
(3, 'images', '组图', '', NULL, NULL, 0, 255, '0', NULL, '', 'images', 'array (\n  ''upload_maxsize'' => ''20480'',\n  ''upload_allowext'' => ''gif|jpg|jpeg|png|bmp'',\n  ''isthumb'' => ''0'',\n  ''thumb_width'' => ''150'',\n  ''thumb_height'' => ''150'',\n  ''iswatermark'' => ''0'',\n  ''water_path'' => ''images/watermark.png'',\n)', NULL, 1, 1, '1', 1,0),
(3, 'attr', '文档属性', '', NULL, NULL, 0, 0, '0', NULL, '', 'attr', 'array (\n  ''show'' => \n  array (\n    0 => ''top'',\n    1 => ''hot'',\n    2 => ''scroll'',\n  ),\n)', NULL, 1, 1, '1', 1,0),
(3, 'brcode','二维码图片','自动生成二维码图片','','','0','255','0','','','input','array (\n  \'size\' => \'50\',\n  \'defaultvalue\' => \'\',\n  \'ispassword\' => \'0\',\n)','','1','1','1','1',0),
(3, 'description', '摘要', '默认可以自动截取前200字作为摘要', 'textarea', NULL, 0, 200, '0', NULL, '不能超过200个字', 'textarea', 'array (\n  ''rows'' => ''5'',\n  ''cols'' => ''60'',\n  ''defaultvalue'' => '''',\n)', NULL, 1, 1, '1', 1,0),
(3, 'content', '正文内容', '主体详细内容', NULL, NULL, 0, 0, '0', NULL, '', 'editor', 'array (\r\n  ''toolbar'' => ''advanced'',\r\n  ''width'' => ''550'',\r\n  ''height'' => ''250'',\r\n  ''keywork-link'' => ''0'',\r\n  ''defaultvalue'' => '''',\r\n)', NULL, 1, 1, '1', 1,0),
(3, 'status', '状态', '默认发布，也可以暂时不发布', NULL, NULL, 0, 255, '1', NULL, '', 'box', 'array (\n  ''option'' => ''发布|9\r\n待审|0'',\n  ''boxtype'' => ''radio'',\n  ''fieldtype'' => ''TINYINT'',\n  ''defaultvalue'' => ''9'',\n)', NULL, 1, 1, '1', 1,0),
(3, 'tag', 'TAG', '以空格隔开，输出会有自动提示', 'input', NULL, 0, 255, '0', NULL, '请正确填写【TAG】', 'tag', 'array (\n  ''size'' => ''60'',\n)', NULL, 1, 1, '1', 2,0),
(3, 'seotitle', 'SEO标题', '默认同标题，也可自定义', 'input', NULL, 0, 255, '0', NULL, '不能超过100个字', 'input', 'array (\n  ''size'' => ''60'',\n  ''defaultvalue'' => '''',\n  ''ispassword'' => ''0'',\n)', NULL, 1, 1, '1', 2,0),
(3, 'seokeywords', 'SEO关键字', '默认同TAG，也可自定义', 'textarea', NULL, 0, 250, '0', NULL, '不能超过200个字', 'textarea', 'array (\n  ''rows'' => ''1'',\n  ''cols'' => ''60'',\n  ''defaultvalue'' => '''',\n)', NULL, 1, 1, '1', 2,0),
(3, 'seodescription', 'SEO描述', '默认同摘要，也可自定义', 'textarea', NULL, 0, 250, '0', NULL, '不能超过250个字', 'textarea', 'array (\n  ''rows'' => ''5'',\n  ''cols'' => ''60'',\n  ''defaultvalue'' => '''',\n)', NULL, 1, 1, '1', 2,0),
(3, 'url', '链接地址', '默认自动生成，不包括".html"后缀，不可改', 'input', NULL, 0, 255, '0', NULL, '不能是纯数字', 'input', 'array (\n  ''size'' => ''60'',\n  ''defaultvalue'' => '''',\n  ''ispassword'' => ''0'',\n)', NULL, 1, 1, '1', 2,0),
(3, 'sort', '排序', '默认为 1，越小越靠前', 'input', NULL, 0, 255, '0', NULL, '', 'input', 'array (\n  ''size'' => ''6'',\n  ''defaultvalue'' => ''1'',\n  ''ispassword'' => ''0'',\n)', NULL, 1, 1, '0', 2,0),
(3, 'template', '模板', '默认使用栏目模板，也可以从模板库中选择', 'input', NULL, 0, 255, '0', NULL, '', 'template', 'array (\n  ''size'' => ''50'',\n)', NULL, 1, 1, '0', 2,0),
(3, 'fulltitle', '完整标题', '默认同标题', 'input', NULL, 0, 100, '0', NULL, '在1~100个字间', 'input', 'array (\n  ''size'' => ''60'',\n  ''defaultvalue'' => '''',\n  ''ispassword'' => ''0'',\n)', NULL, 1, 1, '0', 2,0),
(3, 'create_time', '添加时间', '默认当前时间', NULL, NULL, 0, 50, '0', NULL, '格式错误', 'datetime', 'array (\n  ''format'' => ''Y-m-d H:i:s'',\n  ''defaulttype'' => ''1'',\n)', NULL, 1, 1, '1', 2,0),
(3, 'update_time', '更新时间', '默认最后更新时间', NULL, NULL, 0, 50, '0', NULL, '格式错误', 'datetime', 'array (\n  ''format'' => ''Y-m-d H:i:s'',\n  ''defaulttype'' => ''1'',\n)', NULL, 1, 1, '1', 2,0),
(4, 'cid', 'ID', '', NULL, NULL, NULL, NULL, '0', NULL, '', 'id', '', NULL, 2, 1, '1', 1,0),
(4, 'catid', '所属栏目', '选择到发布到的栏目，默认当前栏目', '', NULL, 0, 0, '1', NULL, '请选择栏目', 'catid', 'array (\n  ''defaultvalue'' => '''',\n)', NULL, 1, 1, '1', 1,0),
(4, 'title', '标题', '字数长度在1~60个字间', 'input', NULL, 0, 255, '1', NULL, '标题在1~60个字间', 'title', 'array (\n  ''size'' => ''60'',\n)', NULL, 1, 1, '1', 1,0),
(4, 'style', '字型与颜色', '可以选择标题在页面上显示的颜色和字型，未定义则是页面默认定义', NULL, NULL, 0, 0, '0', NULL, '', 'style', 'array (\n  ''style'' => \n  array (\n    ''color'' => '''',\n  ),\n)', NULL, 1, 1, '0', 1,0),
(4, 'thumb', '缩略图', '可从本机上传或者从站内选择', 'input', NULL, 0, 255, '0', NULL, '', 'thumb', 'array (\n  ''size'' => ''50'',\n  ''defaultvalue'' => '''',\n  ''upload_maxsize'' => ''512'',\n  ''upload_allowext'' => ''gif|jpg|jpeg|png|bmp'',\n  ''isthumb'' => ''0'',\n  ''thumb_width'' => ''150'',\n  ''thumb_height'' => ''150'',\n  ''iswatermark'' => ''0'',\n  ''water_path'' => ''images/watermark.png'',\n)', NULL, 1, 1, '1', 1,0),
(4, 'downfile', '文件下载', '', 'input', NULL, 0, 0, '0', NULL, '', 'download', 'array (\n  ''upload_maxsize'' => ''10000'',\n  ''upload_allowext'' => ''zip|rar|doc|docx|xls|ppt|txt'',\n  ''size_input'' => ''info[filesize]'',\n  ''downloadtype'' => ''1'',\n  ''permissions'' => \n  array (\n    0 => ''administrator'',\n    1 => ''guest'',\n  ),\n)', NULL, 1, 1, '1', 1,0),
(4, 'type', '文件类型', '请选择文件的类型', NULL, NULL, 0, 255, '0', NULL, '', 'box', 'array (\n  ''option'' => ''rar|rar\r\npdf|pdf\r\ndoc|doc\r\nexcel|excel\r\nexe|exe\r\n其他|其他'',\n  ''boxtype'' => ''select'',\n  ''fieldtype'' => ''CHAR'',\n  ''defaultvalue'' => ''rar'',\n)', NULL, 1, 1, '1', 1,0),
(4, 'filesize', '文件大小', '', 'input', NULL, 0, 255, '0', NULL, '', 'input', 'array (\n  ''size'' => ''10'',\n  ''defaultvalue'' => ''未知'',\n  ''ispassword'' => ''0'',\n)', 'onfocus=&quot;if(this.value==''未知'') this.value='''';&quot; onblur=&quot;if(this.value=='''') this.value=''未知'';&quot;', 1, 1, '1', 1,0),
(4, 'version', '版本号', '', 'input', NULL, 0, 255, '0', NULL, '', 'input', 'array (\n  ''size'' => ''10'',\n  ''defaultvalue'' => '''',\n  ''ispassword'' => ''0'',\n)', NULL, 1, 1, '1', 1,0),
(4, 'language', '软件语言', '', NULL, NULL, 0, 255, '0', NULL, '', 'box', 'array (\n  ''option'' => ''英文|英文\r\n简体中文|简体中文\r\n繁体中文|繁体中文\r\n简繁中文|简繁中文\r\n多国语言|多国语言\r\n其他语言|其他语言'',\n  ''boxtype'' => ''select'',\n  ''fieldtype'' => ''VARCHAR'',\n  ''defaultvalue'' => ''简体中文'',\n)', NULL, 1, 1, '1', 1,0),
(4, 'systems', '软件平台', '软件运行平台', 'input', NULL, 0, 255, '0', NULL, '', 'input', 'array (\n  ''size'' => ''50'',\n  ''defaultvalue'' => ''Win2000/WinXP/Win2003'',\n  ''ispassword'' => ''0'',\n)', NULL, 1, 1, '1', 1,0),
(4, 'attr', '文档属性', '', NULL, NULL, 0, 0, '0', NULL, '', 'attr', 'array (\n  ''show'' => \n  array (\n    0 => ''top'',\n    1 => ''hot'',\n    2 => ''scroll'',\n  ),\n)', NULL, 1, 1, '1', 1,0),
(4, 'brcode','二维码图片','自动生成二维码图片','','','0','255','0','','','input','array (\n  \'size\' => \'50\',\n  \'defaultvalue\' => \'\',\n  \'ispassword\' => \'0\',\n)','','1','1','1','1',0),
(4, 'description', '摘要', '默认可以自动截取前200字作为摘要', 'textarea', NULL, 0, 200, '0', NULL, '不能超过200个字', 'textarea', 'array (\n  ''rows'' => ''5'',\n  ''cols'' => ''60'',\n  ''defaultvalue'' => '''',\n)', NULL, 1, 1, '1', 1,0),
(4, 'content', '正文内容', '主体详细内容', NULL, NULL, 0, 0, '0', NULL, '', 'editor', 'array (\r\n  ''toolbar'' => ''advanced'',\r\n  ''width'' => ''550'',\r\n  ''height'' => ''250'',\r\n  ''keywork-link'' => ''0'',\r\n  ''defaultvalue'' => '''',\r\n)', NULL, 1, 1, '1', 1,0),
(4, 'status', '状态', '默认发布，也可以暂时不发布', NULL, NULL, 0, 255, '1', NULL, '', 'box', 'array (\n  ''option'' => ''发布|9\r\n待审|0'',\n  ''boxtype'' => ''radio'',\n  ''fieldtype'' => ''TINYINT'',\n  ''defaultvalue'' => ''9'',\n)', NULL, 1, 1, '1', 1,0),
(4, 'tag', 'TAG', '以空格隔开，输出会有自动提示', 'input', NULL, 0, 255, '0', NULL, '请正确填写【TAG】', 'tag', 'array (\n  ''size'' => ''60'',\n)', NULL, 1, 1, '1', 2,0),
(4, 'seotitle', 'SEO标题', '默认同标题，也可自定义', 'input', NULL, 0, 255, '0', NULL, '不能超过100个字', 'input', 'array (\n  ''size'' => ''60'',\n  ''defaultvalue'' => '''',\n  ''ispassword'' => ''0'',\n)', NULL, 1, 1, '1', 2,0),
(4, 'seokeywords', 'SEO关键字', '默认同TAG，也可自定义', 'textarea', NULL, 0, 250, '0', NULL, '不能超过200个字', 'textarea', 'array (\n  ''rows'' => ''5'',\n  ''cols'' => ''60'',\n  ''defaultvalue'' => '''',\n)', NULL, 1, 1, '1', 2,0),
(4, 'seodescription', 'SEO描述', '默认同摘要，也可自定义', 'textarea', NULL, 0, 250, '0', NULL, '不能超过250个字', 'textarea', 'array (\n  ''rows'' => ''5'',\n  ''cols'' => ''60'',\n  ''defaultvalue'' => '''',\n)', NULL, 1, 1, '1', 2,0),
(4, 'url', '链接地址', '默认自动生成，不包括".html"后缀，不可改', 'input', NULL, 0, 255, '0', NULL, '不能是纯数字', 'input', 'array (\n  ''size'' => ''60'',\n  ''defaultvalue'' => '''',\n  ''ispassword'' => ''0'',\n)', NULL, 1, 1, '1', 2,0),
(4, 'sort', '排序', '默认为 1，越小越靠前', 'input', NULL, 0, 255, '0', NULL, '', 'input', 'array (\n  ''size'' => ''6'',\n  ''defaultvalue'' => ''1'',\n  ''ispassword'' => ''0'',\n)', NULL, 1, 1, '0', 2,0),
(4, 'template', '模板', '默认使用栏目模板，也可以从模板库中选择', 'input', NULL, 0, 255, '0', NULL, '', 'template', 'array (\n  ''size'' => ''50'',\n)', NULL, 1, 1, '0', 2,0),
(4, 'fulltitle', '完整标题', '默认同标题', 'input', NULL, 0, 100, '0', NULL, '在1~100个字间', 'input', 'array (\n  ''size'' => ''60'',\n  ''defaultvalue'' => '''',\n  ''ispassword'' => ''0'',\n)', NULL, 1, 1, '0', 2,0),
(4, 'create_time', '添加时间', '默认当前时间', NULL, NULL, 0, 50, '0', NULL, '格式错误', 'datetime', 'array (\n  ''format'' => ''Y-m-d H:i:s'',\n  ''defaulttype'' => ''1'',\n)', NULL, 1, 1, '1', 2,0),
(4, 'update_time', '更新时间', '默认最后更新时间', NULL, NULL, 0, 50, '0', NULL, '格式错误', 'datetime', 'array (\n  ''format'' => ''Y-m-d H:i:s'',\n  ''defaulttype'' => ''1'',\n)', NULL, 1, 1, '1', 2,0),
(5, 'cid', 'ID', '', NULL, NULL, NULL, NULL, '0', NULL, '', 'id', '', NULL, 2, 1, '1', 1,0),
(5, 'catid', '所属栏目', '选择到发布到的栏目，默认当前栏目', NULL, NULL, 0, 0, '1', NULL, '请选择栏目', 'catid', 'array (\n  ''defaultvalue'' => '''',\n)', NULL, 1, 1, '1', 1,0),
(5, 'title', '标题', '字数长度在1~60个字间', 'input', NULL, 0, 255, '1', NULL, '标题在1~60个字间', 'title', 'array (\n  ''size'' => ''60'',\n)', NULL, 1, 1, '1', 1,0),
(5, 'style', '字型与颜色', '可以选择标题在页面上显示的颜色和字型，未定义则是页面默认定义', NULL, NULL, 0, 0, '0', NULL, '', 'style', 'array (\n  ''style'' => \n  array (\n    ''color'' => '''',\n  ),\n)', NULL, 1, 1, '0', 1,0),
(5, 'thumb', '缩略图', '可从本机上传或者从站内选择', 'input', NULL, 0, 255, '0', NULL, '', 'thumb', 'array (\n  ''size'' => ''50'',\n  ''defaultvalue'' => '''',\n  ''upload_maxsize'' => ''512'',\n  ''upload_allowext'' => ''gif|jpg|jpeg|png|bmp'',\n  ''isthumb'' => ''0'',\n  ''thumb_width'' => ''150'',\n  ''thumb_height'' => ''150'',\n  ''iswatermark'' => ''0'',\n  ''water_path'' => ''images/watermark.png'',\n)', NULL, 1, 1, '1', 1,0),
(5, 'video', '视频地址', '视频地址，原始名称和实际名称用“|”进行分割开。', 'input', NULL, 0, 0, '0', NULL, '', 'video', 'array (\n  ''size'' => ''50'',\n  ''width'' => ''500'',\n  ''height'' => ''400'',\n  ''upload_maxsize'' => ''10000'',\n  ''upload_allowext'' => ''swf|flv|wmv|mp4'',\n  ''downloadtype'' => ''0'',\n)', NULL, 1, 1, '1', 1,0),
(5, 'attr', '文档属性', '', NULL, NULL, 0, 0, '0', NULL, '', 'attr', 'array (\n  ''show'' => \n  array (\n    0 => ''top'',\n    1 => ''hot'',\n    2 => ''scroll'',\n  ),\n)', NULL, 1, 1, '1', 1,0),
(5, 'brcode','二维码图片','自动生成二维码图片','','','0','255','0','','','input','array (\n  \'size\' => \'50\',\n  \'defaultvalue\' => \'\',\n  \'ispassword\' => \'0\',\n)','','1','1','1','1',0),
(5, 'description', '摘要', '默认可以自动截取前200字作为摘要', 'textarea', NULL, 0, 200, '0', NULL, '不能超过200个字', 'textarea', 'array (\n  ''rows'' => ''5'',\n  ''cols'' => ''60'',\n  ''defaultvalue'' => '''',\n)', NULL, 1, 1, '1', 1,0),
(5, 'content', '正文内容', '主体详细内容', NULL, NULL, 0, 0, '0', NULL, '', 'editor', 'array (\r\n  ''toolbar'' => ''advanced'',\r\n  ''width'' => ''550'',\r\n  ''height'' => ''250'',\r\n  ''keywork-link'' => ''0'',\r\n  ''defaultvalue'' => '''',\r\n)', NULL, 1, 1, '1', 1,0),
(5, 'status', '状态', '默认发布，也可以暂时不发布', NULL, NULL, 0, 255, '1', NULL, '', 'box', 'array (\n  ''option'' => ''发布|9\r\n待审|0'',\n  ''boxtype'' => ''radio'',\n  ''fieldtype'' => ''TINYINT'',\n  ''defaultvalue'' => ''9'',\n)', NULL, 1, 1, '1', 1,0),
(5, 'tag', 'TAG', '以空格隔开，输出会有自动提示', 'input', NULL, 0, 0, '0', NULL, '', 'tag', 'array (\n  ''size'' => ''30'',\n)', NULL, 1, 1, '1', 2,0),
(5, 'seotitle', 'SEO标题', '默认同标题，也可自定义', 'input', NULL, 0, 255, '0', NULL, '不能超过100个字', 'input', 'array (\n  ''size'' => ''60'',\n  ''defaultvalue'' => '''',\n  ''ispassword'' => ''0'',\n)', NULL, 1, 1, '1', 2,0),
(5, 'seokeywords', 'SEO关键字', '默认同TAG，也可自定义', 'textarea', NULL, 0, 250, '0', NULL, '不能超过200个字', 'textarea', 'array (\n  ''rows'' => ''5'',\n  ''cols'' => ''60'',\n  ''defaultvalue'' => '''',\n)', NULL, 1, 1, '1', 2,0),
(5, 'seodescription', 'SEO描述', '默认同摘要，也可自定义', 'textarea', NULL, 0, 250, '0', NULL, '不能超过250个字', 'textarea', 'array (\n  ''rows'' => ''5'',\n  ''cols'' => ''60'',\n  ''defaultvalue'' => '''',\n)', NULL, 1, 1, '1', 2,0),
(5, 'url', '链接地址', '默认自动生成，不包括".html"后缀，不可改', 'input', NULL, 0, 255, '0', NULL, '不能是纯数字', 'input', 'array (\n  ''size'' => ''60'',\n  ''defaultvalue'' => '''',\n  ''ispassword'' => ''0'',\n)', NULL, 1, 1, '1', 2,0),
(5, 'sort', '排序', '默认为 1，越小越靠前', 'input', NULL, 0, 255, '0', NULL, '', 'input', 'array (\n  ''size'' => ''6'',\n  ''defaultvalue'' => ''1'',\n  ''ispassword'' => ''0'',\n)', NULL, 1, 1, '0', 2,0),
(5, 'template', '模板', '默认使用栏目模板，也可以从模板库中选择', 'input', NULL, 0, 255, '0', NULL, '', 'template', 'array (\n  ''size'' => ''50'',\n)', NULL, 1, 1, '0', 2,0),
(5, 'fulltitle', '完整标题', '默认同标题', 'input', NULL, 0, 100, '0', NULL, '在1~100个字间', 'input', 'array (\n  ''size'' => ''60'',\n  ''defaultvalue'' => '''',\n  ''ispassword'' => ''0'',\n)', NULL, 1, 1, '0', 2,0),
(5, 'create_time', '添加时间', '默认当前时间', NULL, NULL, 0, 50, '0', NULL, '格式错误', 'datetime', 'array (\n  ''format'' => ''Y-m-d H:i:s'',\n  ''defaulttype'' => ''1'',\n)', NULL, 1, 1, '1', 2,0),
(5, 'update_time', '更新时间', '默认最后更新时间', NULL, NULL, 0, 50, '0', NULL, '格式错误', 'datetime', 'array (\n  ''format'' => ''Y-m-d H:i:s'',\n  ''defaulttype'' => ''1'',\n)', NULL, 1, 1, '1', 2,0),
(6, 'user_id', '用户ID', '', NULL, NULL, NULL, NULL, '0', NULL, '', 'id', '', NULL, 2, 1, '1', 1,0),
(6, 'username', '管理员名', '管理员的登录名称，5~20个字符，只能包含字母和数字，不能与其他管理员名重复', 'input', NULL, 5, 20, '1', NULL, '【管理员名】必须且不能与其他管理员名重复，只能包含字母和数字', 'input', 'array (\n  ''size'' => ''20'',\n  ''defaultvalue'' => '''',\n  ''ispassword'' => ''0'',\n)', NULL, 1, 1, '1', 1,0),
(6, 'password', '管理员密码', '管理员密码，5~20个字符', 'input', NULL, 5, 20, '1', NULL, '【管理员密码】必填，5~20个字符', 'input', 'array (\n  ''size'' => ''20'',\n  ''defaultvalue'' => '''',\n  ''ispassword'' => ''1'',\n)', NULL, 1, 1, '1', 1,0),
(6, 'repassword', '确认密码', '再次输入密码，防止输入错误', 'input', NULL, 5, 20, '1', NULL, '【确认密码】必填且两次输入的密码应一致', 'input', 'array (\n  ''size'' => ''20'',\n  ''defaultvalue'' => '''',\n  ''ispassword'' => ''1'',\n)', NULL, 1, 1, '1', 1,0),
(6, 'nickname', '昵称', '管理员的昵称', 'input', NULL, 0, 255, '0', NULL, '', 'input', 'array (\r\n  ''size'' => ''20'',\r\n  ''defaultvalue'' => '''',\r\n  ''ispassword'' => ''0'',\r\n)', NULL, 1, 1, '1', 1,0),
(6, 'email', '电子邮件', '自助找回密码唯一方式，不可改', 'input email', NULL, 8, 64, '1', NULL, '【电子邮件】必须填写正确且没有被使用', 'input', 'array (\n  ''size'' => ''40'',\n  ''defaultvalue'' => '''',\n  ''ispassword'' => ''0'',\n)', NULL, 1, 1, '1', 1,0),
(6, 'remark', '备注', '', 'textarea', NULL, 0, 0, '0', NULL, '', 'textarea', 'array (\n  ''rows'' => ''4'',\n  ''cols'' => ''50'',\n  ''defaultvalue'' => '''',\n)', NULL, 1, 1, '1', 1,0),
(6, 'status', '状态', '锁定管理员无法进行任何授权操作', NULL, NULL, 0, 255, '0', NULL, '', 'box', 'array (\n  ''option'' => ''正常|1\r\n锁定|0'',\n  ''boxtype'' => ''radio'',\n  ''fieldtype'' => ''CHAR'',\n  ''defaultvalue'' => ''1'',\n)', NULL, 1, 1, '1', 1,0),
(6, 'realname', '真实姓名', '', 'input', NULL, 0, 255, '0', NULL, '', 'input', 'array (\n  ''size'' => ''20'',\n  ''defaultvalue'' => '''',\n  ''ispassword'' => ''0'',\n)', NULL, 1, 1, '1', 2,0),
(6, 'sex', '性别', '', NULL, NULL, 0, 255, '0', NULL, '', 'box', 'array (\n  ''option'' => ''男|1\r\n女|2'',\n  ''boxtype'' => ''radio'',\n  ''fieldtype'' => ''CHAR'',\n  ''defaultvalue'' => ''1'',\n)', NULL, 1, 1, '1', 2,0),
(7, 'user_id', '用户ID', '', NULL, NULL, NULL, NULL, '0', NULL, '', 'id', '', NULL, 2, 1, '1', 1,0),
(7, 'username', '会员名', '会员的登录名称，5~20个字符，只能包含字母和数字，不能与其他会员名重复', 'input', NULL, 5, 20, '1', NULL, '【会员名】必须且不能与其他会员名重复，只能包含字母和数字', 'input', 'array (\n  ''size'' => ''20'',\n  ''defaultvalue'' => '''',\n  ''ispassword'' => ''0'',\n)', NULL, 1, 1, '1', 1,0),
(7, 'password', '会员密码', '会员密码，5~20个字符', 'input', NULL, 5, 20, '1', NULL, '【会员密码】必填，5~20个字符', 'input', 'array (\n  ''size'' => ''20'',\n  ''defaultvalue'' => '''',\n  ''ispassword'' => ''1'',\n)', NULL, 1, 1, '1', 1,0),
(7, 'repassword', '确认密码', '再次输入密码，防止输入错误', 'input', NULL, 5, 20, '1', NULL, '【确认密码】必填且两次输入的密码应一致', 'input', 'array (\n  ''size'' => ''20'',\n  ''defaultvalue'' => '''',\n  ''ispassword'' => ''1'',\n)', NULL, 1, 1, '1', 1,0),
(7, 'nickname', '昵称', '会员的昵称', 'input', NULL, 0, 255, '0', NULL, '', 'input', 'array (\r\n  ''size'' => ''20'',\r\n  ''defaultvalue'' => '''',\r\n  ''ispassword'' => ''0'',\r\n)', NULL, 1, 1, '1', 1,0),
(7, 'email', '电子邮件', '自助找回密码唯一方式，不可改', 'input email', NULL, 8, 64, '1', NULL, '【电子邮件】必须填写正确且没有被使用', 'input', 'array (\n  ''size'' => ''40'',\n  ''defaultvalue'' => '''',\n  ''ispassword'' => ''0'',\n)', NULL, 1, 1, '1', 1,0),
(7, 'remark', '备注', '', 'textarea', NULL, 0, 0, '0', NULL, '', 'textarea', 'array (\n  ''rows'' => ''4'',\n  ''cols'' => ''50'',\n  ''defaultvalue'' => '''',\n)', NULL, 1, 1, '1', 1,0),
(7, 'status', '状态', '锁定会员无法进行任何授权操作', NULL, NULL, 0, 255, '0', NULL, '', 'box', 'array (\n  ''option'' => ''正常|1\r\n锁定|0'',\n  ''boxtype'' => ''radio'',\n  ''fieldtype'' => ''CHAR'',\n  ''defaultvalue'' => ''1'',\n)', NULL, 1, 1, '1', 1,0),
(7, 'realname', '真实姓名', '', 'input', NULL, 0, 255, '0', NULL, '', 'input', 'array (\n  ''size'' => ''20'',\n  ''defaultvalue'' => '''',\n  ''ispassword'' => ''0'',\n)', NULL, 1, 1, '1', 2,0),
(7, 'sex', '性别', '', NULL, NULL, 0, 255, '0', NULL, '', 'box', 'array (\n  ''option'' => ''男|1\r\n女|2'',\n  ''boxtype'' => ''radio'',\n  ''fieldtype'' => ''CHAR'',\n  ''defaultvalue'' => ''1'',\n)', NULL, 1, 1, '1', 2,0),
(14, 'seotitle', 'SEO标题', '默认同标题，也可自定义', 'input', NULL, 0, 255, '0', NULL, '不能超过100个字', 'input', 'array (\n  ''size'' => ''60'',\n  ''defaultvalue'' => '''',\n  ''ispassword'' => ''0'',\n)', NULL, 1, 16, '1', 2,0),
(14, 'tag', 'TAG', '以空格隔开，输出会有自动提示', 'input', NULL, 0, 255, '0', NULL, '请正确填写【TAG】', 'tag', 'array (\n  ''size'' => ''60'',\n)', NULL, 1, 1, '1', 2,0),
(14, 'content', '正文内容', '主体详细内容', '', '', 0, 0, '0', '', '', 'editor', 'array (\n  ''toolbar'' => ''advanced'',\n  ''width'' => ''550'',\n  ''height'' => ''250'',\n  ''keywork-link'' => ''0'',\n  ''defaultvalue'' => '''',\n)', '', 1, 8, '1', 1,0),
(14, 'status', '状态', '默认发布，也可以暂时不发布', NULL, NULL, 0, 255, '1', NULL, '', 'box', 'array (\n  ''option'' => ''发布|9\r\n待审|0'',\n  ''boxtype'' => ''radio'',\n  ''fieldtype'' => ''TINYINT'',\n  ''defaultvalue'' => ''9'',\n)', NULL, 1, 10, '1', 1,0),
(14, 'attr', '文档属性', '', NULL, NULL, 0, 0, '0', NULL, '', 'attr', 'array (\n  ''show'' => \n  array (\n    0 => ''top'',\n    1 => ''hot'',\n    2 => ''scroll'',\n  ),\n)', NULL, 1, 5, '1', 1,0),
(14, 'brcode','二维码图片','自动生成二维码图片','','','0','255','0','','','input','array (\n  \'size\' => \'50\',\n  \'defaultvalue\' => \'\',\n  \'ispassword\' => \'0\',\n)','','1','7','1','1',0),
(14, 'description', '摘要', '默认可以自动截取前200字作为摘要', 'textarea', NULL, 0, 200, '0', NULL, '不能超过200个字', 'textarea', 'array (\n  ''rows'' => ''5'',\n  ''cols'' => ''60'',\n  ''defaultvalue'' => '''',\n)', NULL, 1, 7, '1', 1,0),
(14, 'thumb', '缩略图', '可从本机上传或者从站内选择', 'input', '', 0, 255, '0', '', '', 'thumb', 'array (\n  ''size'' => ''50'',\n  ''defaultvalue'' => '''',\n  ''upload_maxsize'' => ''512'',\n  ''upload_allowext'' => ''gif|jpg|jpeg|png|bmp'',\n  ''isthumb'' => ''1'',\n  ''thumb_width'' => ''150'',\n  ''thumb_height'' => ''150'',\n  ''iswatermark'' => ''0'',\n  ''water_path'' => ''images/download.gif'',\n)', '', 1, 4, '1', 1,0),
(14, 'title', '标题', '字数长度在1~60个字间', 'input', NULL, 0, 255, '1', NULL, '标题在1~60个字间', 'title', 'array (\n  ''size'' => ''50'',\n)', NULL, 1, 3, '1', 1,0),
(14, 'resource', '文章来源', '信息的来源，如新浪网', 'input', NULL, 0, 255, '0', NULL, '', 'input', 'array (\n  ''size'' => ''35'',\n  ''defaultvalue'' => ''本站'',\n  ''ispassword'' => ''0'',\n)', NULL, 1, 6, '1', 1,0),
(14, 'fulltitle', '完整标题', '默认同标题', 'input', NULL, 0, 100, '0', NULL, '在1~100个字间', 'input', 'array (\n  ''size'' => ''60'',\n  ''defaultvalue'' => '''',\n  ''ispassword'' => ''0'',\n)', NULL, 1, 1, '0', 2,0),
(14, 'template', '模板', '默认使用栏目模板，也可以从模板库中选择', 'input', NULL, 0, 255, '0', NULL, '', 'template', 'array (\n  ''size'' => ''50'',\n)', NULL, 1, 1, '0', 2,0),
(14, 'sort', '排序', '默认为 1，越小越靠前', 'input', NULL, 0, 255, '0', NULL, '', 'input', 'array (\n  ''size'' => ''6'',\n  ''defaultvalue'' => ''1'',\n  ''ispassword'' => ''0'',\n)', NULL, 1, 12, '0', 2,0),
(14, 'style', '字型与颜色', '可以选择标题在页面上显示的颜色和字型，未定义则是页面默认定义', NULL, NULL, 0, 0, '0', NULL, '', 'style', 'array (\n  ''style'' => \n  array (\n    ''color'' => '''',\n  ),\n)', NULL, 1, 8, '0', 1,0),
(14, 'classify_select', '分类管理', '分类目录', NULL, NULL, 0, 0, '0', NULL, '', 'select', 'NULL', NULL, 1, 11, '0', 1,0),
(14, 'cid', 'ID', '', '', '', 0, 0, '0', '', '', 'id', NULL, '', 2, 1, '1', 1,0),
(14, 'catid', '所属栏目', '选择到发布到的栏目，默认当前栏目', '', '', 0, 0, '1', '', '请选择栏目', 'catid', 'array (\n  ''defaultvalue'' => '''',\n)', '', 1, 2, '1', 1,0),
(14, 'seokeywords', 'SEO关键字', '默认同TAG，也可自定义', 'textarea', NULL, 0, 250, '0', NULL, '不能超过200个字', 'textarea', 'array (\n  ''rows'' => ''5'',\n  ''cols'' => ''60'',\n  ''defaultvalue'' => '''',\n)', NULL, 1, 18, '1', 2,0),
(14, 'seodescription', 'SEO描述', '默认同摘要，也可自定义', 'textarea', NULL, 0, 250, '0', NULL, '不能超过250个字', 'textarea', 'array (\n  ''rows'' => ''5'',\n  ''cols'' => ''60'',\n  ''defaultvalue'' => '''',\n)', NULL, 1, 17, '1', 2,0),
(14, 'url', '链接地址', '默认自动生成，不包括".html"后缀，不可改', 'input', NULL, 0, 255, '0', NULL, '不能是纯数字', 'input', 'array (\n  ''size'' => ''60'',\n  ''defaultvalue'' => '''',\n  ''ispassword'' => ''0'',\n)', NULL, 1, 20, '1', 2,0),
(14, 'create_time', '添加时间', '默认当前时间', NULL, NULL, 0, 50, '0', NULL, '格式错误', 'datetime', 'array (\n  ''format'' => ''Y-m-d'',\n  ''defaulttype'' => ''1'',\n)', NULL, 1, 21, '1', 2,0),
(14, 'update_time', '更新时间', '默认最后更新时间', NULL, NULL, 0, 50, '0', NULL, '格式错误', 'datetime', 'array (\n  ''format'' => ''Y-m-d'',\n  ''defaulttype'' => ''1'',\n)', NULL, 1, 22, '1', 2,0),
(15, 'pid', 'ID', '', '', '', 0, 0, '0', '', '', 'id', NULL, '', 2, 1, '1', 1,0),
(15, 'catid', '所属栏目', '选择到发布到的栏目，默认当前栏目', '', '', 0, 0, '1', '', '请选择栏目', 'catid', 'array (\n  ''defaultvalue'' => '''',\n)', '', 1, 2, '1', 1,0),
(15, 'title', '标题', '字数长度在1~60个字间', 'input', NULL, 0, 255, '1', NULL, '标题在1~60个字间', 'title', 'array (\n  ''size'' => ''50'',\n)', NULL, 1, 3, '1', 1,0),
(15, 'thumb', '缩略图', '可从本机上传或者从站内选择', 'input', '', 0, 255, '0', '', '', 'thumb', 'array (\n  ''size'' => ''50'',\n  ''defaultvalue'' => '''',\n  ''upload_maxsize'' => ''512'',\n  ''upload_allowext'' => ''gif|jpg|jpeg|png|bmp'',\n  ''isthumb'' => ''1'',\n  ''thumb_width'' => ''150'',\n  ''thumb_height'' => ''150'',\n  ''iswatermark'' => ''0'',\n  ''water_path'' => ''images/download.gif'',\n)', '', 1, 4, '1', 1,0),
(15, 'resource', '文章来源', '信息的来源，如新浪网', 'input', NULL, 0, 255, '0', NULL, '', 'input', 'array (\n  ''size'' => ''35'',\n  ''defaultvalue'' => ''本站'',\n  ''ispassword'' => ''0'',\n)', NULL, 1, 6, '1', 1,0),
(15, 'attr', '文档属性', '', NULL, NULL, 0, 0, '0', NULL, '', 'attr', 'array (\n  ''show'' => \n  array (\n    0 => ''top'',\n    1 => ''hot'',\n    2 => ''scroll'',\n  ),\n)', NULL, 1, 5, '1', 1,0),
(15, 'brcode','二维码图片','自动生成二维码图片','','','0','255','0','','','input','array (\n  \'size\' => \'50\',\n  \'defaultvalue\' => \'\',\n  \'ispassword\' => \'0\',\n)','','1','7','1','1',0),
(15, 'description', '摘要', '默认可以自动截取前200字作为摘要', 'textarea', NULL, 0, 200, '0', NULL, '不能超过200个字', 'textarea', 'array (\n  ''rows'' => ''5'',\n  ''cols'' => ''60'',\n  ''defaultvalue'' => '''',\n)', NULL, 1, 7, '1', 1,0),
(15, 'content', '正文内容', '主体详细内容', '', '', 0, 0, '0', '', '', 'editor', 'array (\n  ''toolbar'' => ''advanced'',\n  ''width'' => ''550'',\n  ''height'' => ''250'',\n  ''keywork-link'' => ''0'',\n  ''defaultvalue'' => '''',\n)', '', 1, 8, '1', 1,0),
(15, 'sort', '排序', '默认为 1，越小越靠前', 'input', NULL, 0, 255, '0', NULL, '', 'input', 'array (\n  ''size'' => ''6'',\n  ''defaultvalue'' => ''1'',\n  ''ispassword'' => ''0'',\n)', NULL, 1, 12, '0', 2,0),
(15, 'status', '状态', '默认发布，也可以暂时不发布', NULL, NULL, 0, 255, '1', NULL, '', 'box', 'array (\n  ''option'' => ''发布|9\r\n待审|0'',\n  ''boxtype'' => ''radio'',\n  ''fieldtype'' => ''TINYINT'',\n  ''defaultvalue'' => ''9'',\n)', NULL, 1, 10, '1', 1,0),
(15, 'template', '模板', '默认使用栏目模板，也可以从模板库中选择', 'input', NULL, 0, 255, '0', NULL, '', 'template', 'array (\n  ''size'' => ''50'',\n)', NULL, 1, 1, '0', 2,0),
(15, 'classify_select', '分类管理', '分类目录', NULL, NULL, 0, 0, '0', NULL, '', 'select', 'NULL', NULL, 1, 11, '0', 1,0),
(15, 'create_time', '添加时间', '默认当前时间', '', '', 0, 50, '0', '', '格式错误', 'datetime', 'array (\n  ''format'' => ''Y-m-d H:i:s'',\n  ''defaulttype'' => ''1'',\n)', '', 1, 21, '1', 2,0),
(15, 'update_time', '更新时间', '默认最后更新时间', '', '', 0, 50, '0', '', '格式错误', 'datetime', 'array (\n  ''format'' => ''Y-m-d H:i:s'',\n  ''defaulttype'' => ''1'',\n)', '', 1, 22, '1', 2,0),
(15, 'up', '封面图片', 'flash封面图片', 'input', '', 0, 255, '0', '', '', 'thumb', 'array (\n  ''size'' => '''',\n  ''defaultvalue'' => '''',\n  ''upload_maxsize'' => ''1024'',\n  ''upload_allowext'' => ''gif|jpg|jpeg|png|bmp'',\n  ''isthumb'' => ''0'',\n  ''thumb_width'' => ''150'',\n  ''thumb_height'' => ''150'',\n  ''iswatermark'' => ''0'',\n  ''water_path'' => ''images/watermark.png'',\n)', '', 0, 4, '1', 1,0),
(15, 'down', '封底图片', 'flash封底图片', 'input', '', 0, 0, '0', '', '', 'thumb', 'array (\n  ''size'' => '''',\n  ''defaultvalue'' => '''',\n  ''upload_maxsize'' => ''1024'',\n  ''upload_allowext'' => ''gif|jpg|jpeg|png|bmp'',\n  ''isthumb'' => ''0'',\n  ''thumb_width'' => ''150'',\n  ''thumb_height'' => ''150'',\n  ''iswatermark'' => ''0'',\n  ''water_path'' => ''images/watermark.png'',\n)', '', 0, 4, '1', 1,0),
(15, 'music', '背景音乐', 'flash背景音乐', 'input', '', 0, 0, '0', '', '', 'file', 'array (\n  ''size'' => ''50'',\n  ''upload_maxsize'' => ''5048'',\n  ''upload_allowext'' => ''mp3|wma'',\n  ''downloadtype'' => ''0'',\n)', '', 0, 4, '1', 1,0),
(15, 'images', '内容图片', 'flash的内容图片', '', '', 0, 255, '0', '', '', 'images', 'array (\n  ''upload_maxsize'' => ''1024'',\n  ''upload_allowext'' => ''gif|jpg|jpeg|png|bmp'',\n  ''isthumb'' => ''1'',\n  ''thumb_width'' => ''150'',\n  ''thumb_height'' => ''150'',\n  ''iswatermark'' => ''0'',\n  ''water_path'' => ''images/watermark.png'',\n)', '', 0, 4, '1', 1,0),
(15, 'seokeywords', 'SEO关键字', '默认同TAG，也可自定义', 'textarea', '', 0, 250, '0', '', '不能超过200个字', 'textarea', 'array (\n  ''rows'' => ''5'',\n  ''cols'' => ''60'',\n  ''defaultvalue'' => '''',\n)', '', 1, 10, '1', 2,0),
(15, 'seotitle', 'SEO标题', '默认同标题，也可自定义', 'input', '', 0, 255, '0', '', '不能超过100个字', 'input', 'array (\n  ''size'' => ''60'',\n  ''defaultvalue'' => '''',\n  ''ispassword'' => ''0'',\n)', '', 1, 9, '1', 2,0),
(15, 'seodescription', 'SEO描述', '默认同摘要，也可自定义', 'textarea', '', 0, 250, '0', '', '不能超过250个字', 'textarea', 'array (\n  ''rows'' => ''5'',\n  ''cols'' => ''60'',\n  ''defaultvalue'' => '''',\n)', '', 1, 11, '1', 2,0),
(15, 'url', '链接地址', '默认自动生成，不包括".html"后缀，不可改', 'input', NULL, 0, 255, '0', NULL, '不能是纯数字', 'input', 'array (\n  ''size'' => ''60'',\n  ''defaultvalue'' => '''',\n  ''ispassword'' => ''0'',\n)', NULL, 1, 11, '1', 2,0);






DROP TABLE IF EXISTS `#@__module`;
CREATE TABLE IF NOT EXISTS `#@__module` (
  `moduleid` smallint(4) NOT NULL auto_increment,
  `controller` varchar(50) NOT NULL COMMENT '控制器名称',
  `name` varchar(20) NOT NULL COMMENT '模块名称',
  `tablename` varchar(50) DEFAULT NULL COMMENT '模块主记录数据表名称',
  `description` varchar(200) DEFAULT NULL COMMENT '描述',
  `sort` smallint(4) NOT NULL DEFAULT '1' COMMENT '排序',
  `status` enum('0','1') NOT NULL DEFAULT '0' COMMENT '状态 0=禁用 1=启用',
  `extendable` enum('0','1') NOT NULL DEFAULT '0' COMMENT '是否可扩展',
  `issystem` enum('0','1') NOT NULL DEFAULT '0' COMMENT '是否固定模块',
  `ishtml` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否可静态',
  PRIMARY KEY  (`moduleid`),
  UNIQUE KEY `action` (`controller`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='前台模块表' AUTO_INCREMENT=9 ;

INSERT INTO `#@__module` (`moduleid`, `controller`, `name`, `tablename`, `description`, `sort`, `status`, `extendable`, `issystem`, `ishtml`) VALUES
(1, 'fcontent', '内容展示', 'content', '内容展示', 1, '1', '1', '1', 1),
(2, 'fuser', '用户管理', 'user', '前台会员及后台管理员管理', 2, '1', '1', '1', 1),
(3, 'fguestbook', '留言板', 'guestbook', '留言板', 3, '1', '0', '0', 0),
(4, 'fjob', '人才招聘', 'job', '人才招聘', 4, '1', '0', '0', 1),
(5, 'fsalenet', '营销网络', 'salenet', '营销网络、介绍公司营销网络分布。', 5, '1', '0', '0', 1),
(6, 'fvote', '投票调查', 'vote_subject', '投票调查', 6, '1', '0', '0', 1),
(7, 'fask', '在线问答', 'ask', '问答模块', 7, '1', '0', '0', 0),
(8, 'factivity', '活动报名', 'activity', '活动报名模块', 8, '1', '0', '0', 0),
(9, 'fmagazine', '电子报刊', 'magazine', '电子报刊', 9, '1', '1', '0', 0),
(10, 'fpress', '电子杂志', 'press', '电子杂志', 10, '1', '1', '0', 0),
(11, 'fsidetable', '副表挂靠', 'sidetable', '单表挂靠功能', 11, '1', '1', '0', 0);


DROP TABLE IF EXISTS `#@__order`;
CREATE TABLE IF NOT EXISTS `#@__order` (
  `orderid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `ordernum` char(20) NOT NULL COMMENT '订单号',
  `keyid` int(32) NOT NULL COMMENT '额外标识符',
  `ordername` text NOT NULL COMMENT '订单名称',
  `price` decimal(8,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '单价',
  `number` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT '订购总数量',
  `total` float DEFAULT NULL COMMENT '总价',
  `linkman` char(20) NOT NULL COMMENT '联系人',
  `telephone` char(20) NOT NULL COMMENT '联系电话',
  `mobile` char(15) NOT NULL COMMENT '手机号码',
  `address` char(200) NOT NULL COMMENT '邮寄地址',
  `postcode` int(6) DEFAULT NULL COMMENT '邮编',
  `message` char(255) DEFAULT NULL COMMENT '买家留言',
  `remark` char(255) DEFAULT NULL COMMENT '备注',
  `userid` mediumint(8) unsigned DEFAULT '0' COMMENT '用户ID',
  `username` char(20) DEFAULT NULL COMMENT '用户名',
  `ip` char(15) NOT NULL COMMENT '订购IP',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '-1=待确认、0=待付款订单、1=已付款订单、2=已发货订单、 9=交易成功订单、 -9=交易关闭订单',
  `create_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '下单时间',
  `update_time` int(10) NOT NULL COMMENT '修改时间',
  PRIMARY KEY (`orderid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='订单记录';


DROP TABLE IF EXISTS `#@__order_log`;
CREATE TABLE IF NOT EXISTS `#@__order_log` (
  `logid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `orderid` int(10) unsigned DEFAULT '0' COMMENT '订单ID',
  `laststatus` char(2) DEFAULT '' COMMENT '操作前订单状态',
  `status` char(2) DEFAULT '' COMMENT '操作后订单状态',
  `note` char(255) NOT NULL COMMENT '操作记录',
  `userid` mediumint(8) unsigned DEFAULT '0' COMMENT '操作员ID',
  `username` char(20) DEFAULT NULL COMMENT '操作员用户名',
  `ip` char(15) NOT NULL COMMENT '操作IP',
  `create_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '记录时间',
  PRIMARY KEY (`logid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='订单操作日志记录';


DROP TABLE IF EXISTS `#@__page`;
CREATE TABLE IF NOT EXISTS `#@__page` (
  `pageid` smallint(5) NOT NULL auto_increment COMMENT '主键ID',
  `catid` smallint(4) unsigned DEFAULT NULL COMMENT '所属栏目',
  `user_id` int(11) NOT NULL COMMENT '内容管理者ID',
  `username` varchar(50) NOT NULL COMMENT '内容发布者用户名',
  `content` mediumtext COMMENT '单页详细内容',
  `create_time` int(11) DEFAULT NULL COMMENT '创建时间',
  `update_time` int(10) unsigned DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY  (`pageid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='单页面表' AUTO_INCREMENT=1 ;


DROP TABLE IF EXISTS `#@__press`;
CREATE TABLE IF NOT EXISTS `#@__press` (
  `pid` int(11) NOT NULL auto_increment COMMENT '主键ID',
  `sort` int(11) NOT NULL COMMENT '排序',
  `title` varchar(11) NOT NULL COMMENT '标题',
  `attr` varchar(20) NOT NULL COMMENT '属性',
  `thumb` varchar(45) NOT NULL COMMENT '缩略图',
  `description` varchar(255) NOT NULL COMMENT '简介',
  `content` text NOT NULL COMMENT '内容',
  `create_time` int(11) NOT NULL COMMENT '创建时间',
  `update_time` int(11) NOT NULL COMMENT '修改时间',
  `seotitle` varchar(100) NOT NULL,
  `seokeywords` varchar(200) NOT NULL,
  `seodescription` varchar(200) NOT NULL,
  `url` varchar(45) NOT NULL COMMENT '链接',
  `status` int(11) NOT NULL COMMENT '状态',
  `catid` int(11) NOT NULL COMMENT '栏目编号',
  `up` varchar(200) default NULL COMMENT '封面图片',
  `down` varchar(200) default NULL COMMENT '封底图片',
  `music` varchar(250) default NULL COMMENT '背景音乐',
  `images` text COMMENT '内容图片',
  `brcode` varchar(255) default NULL COMMENT '二维码图片',
  PRIMARY KEY  (`pid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT '电子杂志主表' AUTO_INCREMENT=1 ;


DROP TABLE IF EXISTS `#@__province`;
CREATE TABLE IF NOT EXISTS `#@__province` (
  `id` int(4) NOT NULL,
  `name` varchar(20) NOT NULL,
  `keys` varchar(10) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `fangfa_province` (`id`, `name`, `keys`) VALUES
(1, '北京市', 'B'),
(2, '天津市', 'T'),
(3, '上海市', 'S'),
(4, '重庆市', 'C'),
(5, '河北省', 'H'),
(6, '山西省', 'S'),
(7, '台湾省', 'T'),
(8, '辽宁省', 'L'),
(9, '吉林省', 'J'),
(10, '黑龙江省', 'H'),
(11, '江苏省', 'J'),
(12, '浙江省', 'Z'),
(13, '安徽省', 'A'),
(14, '福建省', 'F'),
(15, '江西省', 'J'),
(16, '山东省', 'S'),
(17, '河南省', 'H'),
(18, '湖北省', 'H'),
(19, '湖南省', 'H'),
(20, '广东省', 'G'),
(21, '甘肃省', 'G'),
(22, '四川省', 'S'),
(24, '贵州省', 'G'),
(25, '海南省', 'H'),
(26, '云南省', 'Y'),
(27, '青海省', 'Q'),
(28, '陕西省', 'S'),
(29, '广西壮族自治区', 'G'),
(30, '西藏自治区', 'X'),
(31, '宁夏回族自治区', 'N'),
(32, '新疆维吾尔自治区', 'X'),
(33, '内蒙古自治区', 'N'),
(34, '澳门特别行政区', 'A'),
(35, '香港特别行政区', 'X');


DROP TABLE IF EXISTS `#@__role`;
CREATE TABLE IF NOT EXISTS `#@__role` (
  `role_id` smallint(4) unsigned NOT NULL auto_increment,
  `modelid` tinyint(4) unsigned NOT NULL DEFAULT '0' COMMENT '会员模型ID',
  `name` varchar(20) NOT NULL COMMENT '角色名',
  `nickname` varchar(20) NOT NULL COMMENT '角色别名',
  `status` tinyint(1) unsigned DEFAULT '1' COMMENT '状态',
  `remark` varchar(255) DEFAULT NULL COMMENT '备注',
  `isadmin` tinyint(1) unsigned DEFAULT '0' COMMENT '是否为管理员',
  PRIMARY KEY  (`role_id`),
  UNIQUE KEY `name` (`name`),
  KEY `status` (`status`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;

INSERT INTO `#@__role` (`role_id`, `modelid`, `name`, `nickname`, `status`, `remark`, `isadmin`) VALUES
(1, 6, 'administrator', '超级管理员', 1, '超级管理员', 1),
(2, 7, 'member', '普通会员', 1, '普通会员', 0);


DROP TABLE IF EXISTS `#@__role_user`;
CREATE TABLE IF NOT EXISTS `#@__role_user` (
  `role_id` smallint(4) unsigned NOT NULL COMMENT '角色ID',
  `user_id` int(10) unsigned NOT NULL COMMENT '用户ID',
  KEY `role_id_index` (`role_id`),
  KEY `user_id_index` (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='用户-角色关联表';


DROP TABLE IF EXISTS `#@__salenet`;
CREATE TABLE IF NOT EXISTS `#@__salenet` (
  `sid` int(11) NOT NULL auto_increment,
  `title` varchar(50) NOT NULL default '' COMMENT '标题',
  `name` varchar(250) NOT NULL,
  `address` varchar(250) NOT NULL,
  `province` int(11) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `scatid` smallint(6) NOT NULL,
  `catid` smallint(5) unsigned NOT NULL COMMENT '栏目ID',
  `type` int(1) NOT NULL default '1' COMMENT '类型（1为国内，2为国外）',
  `content` longtext NOT NULL COMMENT '页面内容',
  `create_at` int(11) NOT NULL COMMENT '创建时间',
  `update_at` int(11) NOT NULL COMMENT '最近更新时间',
  `status` tinyint(11) NOT NULL default '0' COMMENT '是否通过审核',
  `user_id` int(10) unsigned NOT NULL default '0' COMMENT '用户ID',
  `username` varchar(50) NOT NULL COMMENT '用户名',
  `url` char(100) NOT NULL COMMENT '链接',
  `template` varchar(50) default NULL COMMENT '模板名称',
  `sort` tinyint(4) unsigned NOT NULL default '1' COMMENT '排序',
  PRIMARY KEY  (`sid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='营销网络' AUTO_INCREMENT=7 ;


DROP TABLE IF EXISTS `#@__scategory`;
CREATE TABLE IF NOT EXISTS `#@__scategory` (
  `scategoryid` mediumint(9) NOT NULL AUTO_INCREMENT,
  `catid` mediumint(9) NOT NULL,
  `name` varchar(255) NOT NULL,
  `parentid` mediumint(9) NOT NULL DEFAULT '0',
  `sort` int(11) NOT NULL DEFAULT '0',
  `status` tinyint(4) NOT NULL DEFAULT '1',
  `create_time` int(10) NOT NULL,
  `update_time` int(10) DEFAULT NULL,
  PRIMARY KEY (`scategoryid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='分类的地区表';


DROP TABLE IF EXISTS `#@__session`;
CREATE TABLE IF NOT EXISTS `#@__session` (
  `sid` char(32) NOT NULL,
  `expiry` int(11) unsigned NOT NULL COMMENT '过期时间',
  `value` varchar(255) DEFAULT NULL COMMENT 'SESSION值',
  PRIMARY KEY (`sid`)
) ENGINE=MEMORY DEFAULT CHARSET=utf8 COMMENT='session表';


DROP TABLE IF EXISTS `#@__setting`;
CREATE TABLE IF NOT EXISTS `#@__setting` (
  `var` varchar(60) NOT NULL COMMENT '变量名',
  `value` text NOT NULL COMMENT '变量值',
  `key` varchar(20) NOT NULL DEFAULT 'custom' COMMENT '标识',
  `type` tinyint(1) NOT NULL DEFAULT '1' COMMENT '参数类型',
  `remark` varchar(50) DEFAULT NULL COMMENT '备注',
  PRIMARY KEY  (`var`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='参数配置';

INSERT INTO `#@__setting` (`var`, `value`, `key`, `type`, `remark`) VALUES
('url_html_suffix', '.html', 'sysset', 1, 'url后缀'),
('pagesize', '30', 'sysset', 1, '分页中每页记录数'),
('create_html', '0', 'sysset', 1, '网站是否生成HTML实体文件'),
('site_title_separator', '-', 'sysset', 1, '网站标题关键字之间的分隔符'),
('siteurl', 'http://www.fangfa.net', 'sysset', 1, '网站URL地址，不包括最后的“/”'),
('seotitle', '-', 'sysset', 1, '网站seotitle'),
('seokeywords', '-', 'sysset', 1, '网站seokeywords'),
('seodescription', '-', 'sysset', 1, '网站seodescription'),
('copyright', '-', 'sysset', 1, '版权信息'),
('icpno', '蜀ICP备 12345678号', 'sysset', 1, '备案号'),
('companyname', '-', 'sysset', 1, '公司名称'),
('companylinkman', '-', 'sysset', 1, '公司联系人'),
('companytel', '028-', 'sysset', 1, '联系电话'),
('companyfax', '-', 'sysset', 1, '传真'),
('companyemail', '028-', 'sysset', 1, '公司邮箱'),
('companyaddress', '-', 'sysset', 1, '公司地址'),
('mail_type', '1', 'sysset', 1, '系统发送邮件的方式：1=SMTP，2=通过mail函数发送，3=通过SOCKET连接SMTP服务器发送'),
('mail_server', 'smtp.qq.com', 'sysset', 1, 'SMTP服务器地址'),
('mail_port', '25', 'sysset', 1, 'SMTP服务器服务端口'),
('mail_user', 'test@qq.com', 'sysset', 1, '使用SMTP服务发送邮件时的用户名'),
('mail_password', 'test', 'sysset', 1, '使用SMTP服务发送邮件时的密码'),
('upload_attachment_allowext', 'doc|docx|xls|ppt|wps|zip|rar|txt', 'sysset', 1, '允许上传的附件类型'),
('upload_maxsize', '1024', 'sysset', 1, '默认允许上传的文件大小'),
('upload_images_allowext', 'jpg|jpeg|gif|bmp|png', 'sysset', 1, '允许的上传的图片类型'),
('upload_dir', 'public/uploads/', 'sysset', 1, '文件或者图片上传路径'),
('upload_url', 'uploads/', 'sysset', 1, '文件或者图片上传目录'),
('upload_thumb_isthumb', '0', 'sysset', 1, '是否生成缩略图'),
('upload_thumb_width', '150', 'sysset', 1, '全局缩略图宽度'),
('upload_thumb_height', '150', 'sysset', 1, '全局缩略图高度'),
('upload_water_iswatermark', '0', 'sysset', 1, '上传的图片是否加水印'),
('watermark_minwidth', '100', 'sysset', 1, '添加水印的图片最小宽度'),
('watermark_minheight', '100', 'sysset', 1, '添加水印的图片最小高度'),
('upload_water_path', 'images/watermark.png', 'sysset', 1, '水印图片的路径'),
('upload_water_trans', '50', 'sysset', 1, '水印图片透明度'),
('watermark_quality', '90', 'sysset', 1, '水印图片质量'),
('upload_water_place', '9', 'sysset', 1, '水印添加位置'),
('upload_file_rule', 'times', 'sysset', 1, '文件名生成规则、对编辑器不起作用'),
('editor_type', 'tiny_mce', 'sysset', 1, '编辑器类型'),
('auto_ping', '0', 'sysset', 1, '是否发布内容后自动ping'),
('ping_sites', 'http://blogsearch.google.com/ping/RPC2', 'sysset', 1, 'Ping地址'),
('filter_word', '去死|贱人|我操|我日|反党|反共|共产党|法轮功|强奸|黑社会|疆独|藏独|K粉|冰毒|枪支|共铲党', 'sysset', 1, '敏感词，使用“|”隔开'),
('user_open_register', '0', 'user', 1, '是否开放用户注册'),
('user_default_role_id', '2', 'user', 1, '前台用户注册的默认角色ID'),
('user_need_active', '0', 'user', 1, '前台注册用户是否需要激活'),
('user_passport_on', '0', 'user', 1, '是否开启UC同步'),
('user_auth_cookie_key', '123456', 'user', 1, '解密用户端cookie使用的密钥'),
('is_brcode','1','sysset','1','启用二维码功能'),
('brcode_size','4','sysset','1','二维码图片大小'),
('is_cut', '0', 'sysset', 1, '启用图片裁剪功能'),
('cut_size', '475', 'sysset', 1, '图片裁剪缩放比例'),
('is_comment', '0', 'sysset', 1, '启用评论功能');


DROP TABLE IF EXISTS `#@__tag`;
CREATE TABLE IF NOT EXISTS `#@__tag` (
  `tagid` int(11) NOT NULL auto_increment COMMENT 'tagID',
  `name` varchar(10) NOT NULL COMMENT 'tag值',
  `count` tinyint(4) NOT NULL DEFAULT '0' COMMENT '使用统计',
  `hits` int(11) NOT NULL DEFAULT '0' COMMENT 'tag点击量',
  PRIMARY KEY  (`tagid`),
  UNIQUE KEY `name` (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='tag表';


DROP TABLE IF EXISTS `#@__template`;
CREATE TABLE IF NOT EXISTS `#@__template` (
  `path` char(50) NOT NULL COMMENT '相对主题的路径',
  `name` char(20) DEFAULT NULL COMMENT '模板名称',
  `note` char(200) DEFAULT NULL COMMENT '模板说明',
  PRIMARY KEY (`path`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='模板表';


DROP TABLE IF EXISTS `#@__user`;
CREATE TABLE IF NOT EXISTS `#@__user` (
  `user_id` int(10) unsigned NOT NULL auto_increment,
  `username` varchar(50) NOT NULL COMMENT '用户名',
  `password` varchar(64) NOT NULL COMMENT '密码',
  `email` varchar(50) NOT NULL COMMENT '电子邮件',
  `nickname` varchar(50) DEFAULT NULL COMMENT '昵称',
  `remark` varchar(255) DEFAULT NULL COMMENT '备注',
  `status` tinyint(1) DEFAULT '1' COMMENT '状态',
  `verify` varchar(32) DEFAULT NULL COMMENT '验证串',
  `isadmin` tinyint(1) unsigned DEFAULT '0' COMMENT '是否为管理员',
  `last_login_time` int(11) unsigned DEFAULT '0' COMMENT '最后登录时间',
  `last_login_ip` varchar(15) DEFAULT NULL COMMENT '最后登录ip',
  `login_count` mediumint(8) unsigned DEFAULT '0' COMMENT '登录统计',
  `create_time` int(11) unsigned NOT NULL COMMENT '记录创建时间',
  `update_time` int(11) unsigned NOT NULL COMMENT '记录更新时间',
  PRIMARY KEY  (`user_id`),
  UNIQUE KEY `username_index` (`username`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='用户表';


DROP TABLE IF EXISTS `#@__user_manager`;
CREATE TABLE IF NOT EXISTS `#@__user_manager` (
  `user_id` int(10) NOT NULL DEFAULT '0' COMMENT '用户ID',
  `realname` varchar(255) NOT NULL DEFAULT '' COMMENT '真实姓名',
  `sex` char(10) DEFAULT '1' COMMENT '性别',
  PRIMARY KEY (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='用户模型扩展表之一';


DROP TABLE IF EXISTS `#@__user_person`;
CREATE TABLE IF NOT EXISTS `#@__user_person` (
  `user_id` int(10) NOT NULL DEFAULT '0' COMMENT '用户ID',
  `realname` varchar(255) NOT NULL DEFAULT '' COMMENT '真实姓名',
  `sex` char(10) DEFAULT '1' COMMENT '性别',
  PRIMARY KEY (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='用户模型扩展表之一';


DROP TABLE IF EXISTS `#@__vote_data`;
CREATE TABLE IF NOT EXISTS `#@__vote_data` (
  `user_id` mediumint(8) unsigned DEFAULT '0',
  `username` char(20) NOT NULL,
  `subjectid` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '投票时间',
  `ip` char(15) NOT NULL COMMENT '投票ip',
  `data` text NOT NULL COMMENT '投票值',
  KEY `subjectid` (`subjectid`),
  KEY `userid` (`user_id`),
  KEY `ip` (`ip`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='投票数据表';


DROP TABLE IF EXISTS `#@__vote_option`;
CREATE TABLE IF NOT EXISTS `#@__vote_option` (
  `optionid` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `subjectid` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `option` varchar(255) NOT NULL COMMENT '选项值',
  `image` varchar(100) NOT NULL COMMENT '选项附加图',
  `sort` tinyint(2) unsigned NOT NULL DEFAULT '0' COMMENT '排序',
  PRIMARY KEY (`optionid`),
  KEY `subjectid` (`subjectid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='投票选项表';


DROP TABLE IF EXISTS `#@__vote_subject`;
CREATE TABLE IF NOT EXISTS `#@__vote_subject` (
  `subjectid` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `catid` mediumint(9) NOT NULL,
  `subject` char(255) NOT NULL,
  `ismultiple` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '0投票，1问卷',
  `ischeckbox` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '0单选 1多选',
  `credit` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT '会员奖励分数',
  `create_time` int(10) unsigned NOT NULL DEFAULT '0',
  `update_time` int(10) NOT NULL,
  `fromdate` date NOT NULL DEFAULT '0000-00-00',
  `todate` date NOT NULL DEFAULT '0000-00-00',
  `interval` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '投票时间间隔',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '是否显示',
  `parentid` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `description` text NOT NULL COMMENT '描述',
  `sort` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT '排序',
  `enablecheckcode` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '否是启用验证码',
  `allowguest` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '是否允许游客投票',
  `groupidsvote` char(100) NOT NULL COMMENT '允许投票的用户组',
  `groupidsview` char(100) NOT NULL COMMENT '允许查看结果的用户组',
  `maxval` tinyint(2) unsigned NOT NULL DEFAULT '0' COMMENT '多选最多选择',
  `minval` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '多选最少选择',
  `userid` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '创建人ID',
  `allowview` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '是否允许查看结果',
  `optionnumber` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '选项数量',
  `votenumber` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '总投票数',
  PRIMARY KEY (`subjectid`),
  KEY `enabled` (`status`),
  KEY `fromdate` (`fromdate`,`todate`),
  KEY `todate` (`todate`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='投票标题表';


DROP TABLE IF EXISTS `#@__widget`;
CREATE TABLE IF NOT EXISTS `#@__widget` (
  `wid` tinyint(4) unsigned NOT NULL auto_increment,
  `name` varchar(20) NOT NULL COMMENT '挂件名称',
  `appname` varchar(20) NOT NULL COMMENT '挂件所属应用',
  `controller` varchar(30) NOT NULL COMMENT '挂件所属控制器',
  `action` varchar(20) NOT NULL COMMENT '挂件应用的动作',
  `block` varchar(100) DEFAULT NULL COMMENT '要显示的区块',
  `status` enum('0','1') NOT NULL DEFAULT '0' COMMENT '1=启用、0=禁用',
  `sort` smallint(4) NOT NULL DEFAULT '1' COMMENT '排序',
  PRIMARY KEY  (`wid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='挂件列表' AUTO_INCREMENT=7 ;

INSERT INTO `#@__widget` (`wid`, `name`, `appname`, `controller`, `action`, `block`, `status`, `sort`) VALUES
(1, '基本信息', 'admin', 'fset', 'set', 'system', '1', 1),
(2, '网站设置', 'admin', 'fset', 'set', 'website', '1', 2),
(3, '邮件设置', 'admin', 'fset', 'set', 'mail', '1', 3),
(4, '附件设置', 'admin', 'fset', 'set', 'attachment', '1', 4),
(5, 'FTP设置', 'admin', 'fset', 'set', 'ftp', '1', 5),
(6, '撰写设置', 'admin', 'fset', 'set', 'write', '1', 6);


DROP TABLE IF EXISTS `#@__brcode`;
CREATE TABLE IF NOT EXISTS `#@__brcode` (                                                    
  `id` smallint(5) unsigned NOT NULL auto_increment,                           
  `title` varchar(255) NOT NULL COMMENT '标题',                              
  `url` varchar(255) NOT NULL COMMENT '二维码地址',                       
  `size` tinyint(1) NOT NULL default '1' COMMENT '二维码尺寸',            
  `content` text COMMENT '二维码内容',                                    
  `user_id` int(10) NOT NULL COMMENT '添加者ID',                            
  `username` varchar(50) default NULL COMMENT '用户名',                     
  `created` int(10) unsigned default NULL COMMENT '添加时间',              
  `status` tinyint(1) NOT NULL default '0' COMMENT '1',                        
  PRIMARY KEY  (`id`)                                                        
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='二维码列表';