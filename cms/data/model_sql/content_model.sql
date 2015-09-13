--
-- 创建模型的扩展表 
-- {DB_PREFIX} 代表数据表前缀   
-- {EXTTABLE} 代表扩展表名称   
-- {MODELID} 表示创建的模型ID  
-- 
-- 这些都会被系统自动替换
--

CREATE TABLE IF NOT EXISTS `{DB_PREFIX}content_{EXTTABLE}` (
  `cid` INT(10) unsigned NOT NULL COMMENT '主表ID',
  `fulltitle` varchar(60) DEFAULT NULL COMMENT '完整标题',
  `content` mediumtext COMMENT '详细内容',
  PRIMARY KEY (`cid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='内容模型扩展表之一';

--
-- 向`model_field`中添加模型必须的主键字段信息以及系统字段
--
INSERT INTO `{DB_PREFIX}model_field` (`modelid`, `field`, `name`, `tips`, `css`, `parent_css`, `minlength`, `maxlength`, `required`, `pattern`, `errortips`, `formtype`, `setting`, `formattribute`, `systype`, `sort`, `status`, `card`) VALUES
({MODELID}, 'cid', 'ID', '', NULL, NULL, NULL, NULL, '0', NULL, '', 'id', '', NULL, 2, 1, '1', 1),
({MODELID}, 'catid', '所属栏目', '选择到发布到的栏目，默认当前栏目', NULL, NULL, 0, 0, '1', NULL, '请选择栏目', 'catid', 'array (\n  ''defaultvalue'' => '''',\n)', NULL, 1, 1, '1', 1),
({MODELID}, 'title', '标题', '字数长度在1~60个字间', 'input', NULL, 0, 255, '1', NULL, '标题在1~60个字间', 'title', 'array (\n  ''size'' => ''50'',\n)', NULL, 1, 1, '1', 1),
({MODELID}, 'style', '字型与颜色', '可以选择标题在页面上显示的颜色和字型，未定义则是页面默认定义', NULL, NULL, 0, 0, '0', NULL, '', 'style', 'array (\n  ''style'' => \n  array (\n    ''color'' => '''',\n  ),\n)', NULL, 1, 1, '0', 1),
({MODELID}, 'thumb', '缩略图', '可从本机上传或者从站内选择', 'input', NULL, 0, 255, '0', NULL, '', 'thumb', 'array (\r\n  ''size'' => ''50'',\r\n  ''defaultvalue'' => '''',\r\n  ''upload_maxsize'' => ''512'',\r\n  ''upload_allowext'' => ''gif|jpg|jpeg|png|bmp'',\r\n  ''isthumb'' => ''0'',\r\n  ''thumb_width'' => ''150'',\r\n  ''thumb_height'' => ''150'',\r\n  ''iswatermark'' => ''0'',\r\n  ''water_path'' => ''images/download.gif'',\r\n)', NULL, 1, 1, '1', 1),
({MODELID}, 'attr', '文档属性', '', NULL, NULL, 0, 0, '0', NULL, '', 'attr', 'array (\n  ''show'' => \n  array (\n    0 => ''top'',\n    1 => ''hot'',\n    2 => ''scroll'',\n  ),\n)', NULL, 1, 1, '1', 1),
({MODELID}, 'description', '摘要', '默认可以自动截取前200字作为摘要', 'textarea', NULL, 0, 200, '0', NULL, '不能超过200个字', 'textarea', 'array (\n  ''rows'' => ''5'',\n  ''cols'' => ''60'',\n  ''defaultvalue'' => '''',\n)', NULL, 1, 1, '1', 1),
({MODELID}, 'content', '正文内容', '主体详细内容', NULL, NULL, 0, 0, '0', NULL, '', 'editor', 'array (\r\n  ''toolbar'' => ''advanced'',\r\n  ''width'' => ''750'',\r\n  ''height'' => ''250'',\r\n  ''keywork-link'' => ''1'',\r\n  ''defaultvalue'' => '''',\r\n)', NULL, 1, 1, '1', 1),
({MODELID}, 'status', '状态', '默认发布，也可以暂时不发布', NULL, NULL, 0, 255, '1', NULL, '', 'box', 'array (\n  ''option'' => ''发布|9\r\n待审|0'',\n  ''boxtype'' => ''radio'',\n  ''fieldtype'' => ''TINYINT'',\n  ''defaultvalue'' => ''9'',\n)', NULL, 1, 1, '1', 1),
({MODELID}, 'tag', 'TAG', '以空格隔开，输出会有自动提示', 'input', NULL, 0, 255, '0', NULL, '请正确填写【TAG】', 'tag', 'array (\n  ''size'' => ''60'',\n)', NULL, 1, 1, '1', 2),
({MODELID}, 'seotitle', 'SEO标题', '默认同标题，也可自定义', 'input', NULL, 0, 255, '0', NULL, '不能超过100个字', 'input', 'array (\n  ''size'' => ''60'',\n  ''defaultvalue'' => '''',\n  ''ispassword'' => ''0'',\n)', NULL, 1, 1, '1', 2),
({MODELID}, 'seokeywords', 'SEO关键字', '默认同TAG，也可自定义', 'textarea', NULL, 0, 250, '0', NULL, '不能超过200个字', 'textarea', 'array (\n  ''rows'' => ''5'',\n  ''cols'' => ''60'',\n  ''defaultvalue'' => '''',\n)', NULL, 1, 1, '1', 2),
({MODELID}, 'seodescription', 'SEO描述', '默认同摘要，也可自定义', 'textarea', NULL, 0, 250, '0', NULL, '不能超过250个字', 'textarea', 'array (\n  ''rows'' => ''5'',\n  ''cols'' => ''60'',\n  ''defaultvalue'' => '''',\n)', NULL, 1, 1, '1', 2),
({MODELID}, 'url', '链接地址', '默认自动生成，不包括".html"后缀，不可改', 'input', NULL, 0, 255, '0', NULL, '不能是纯数字', 'input', 'array (\n  ''size'' => ''60'',\n  ''defaultvalue'' => '''',\n  ''ispassword'' => ''0'',\n)', NULL, 1, 1, '1', 2),
({MODELID}, 'sort', '排序', '默认为 1，越小越靠前', 'input', NULL, 0, 255, '0', NULL, '', 'input', 'array (\n  ''size'' => ''6'',\n  ''defaultvalue'' => ''1'',\n  ''ispassword'' => ''0'',\n)', NULL, 1, 1, '0', 2),
({MODELID}, 'template', '模板', '默认使用栏目模板，也可以从模板库中选择', 'input', NULL, 0, 255, '0', NULL, '', 'template', 'array (\n  ''size'' => ''50'',\n)', NULL, 1, 1, '0', 2),
({MODELID}, 'fulltitle', '完整标题', '默认同标题', 'input', NULL, 0, 100, '0', NULL, '在1~100个字间', 'input', 'array (\n  ''size'' => ''60'',\n  ''defaultvalue'' => '''',\n  ''ispassword'' => ''0'',\n)', NULL, 1, 1, '0', 2),
({MODELID}, 'create_time', '添加时间', '默认当前时间', NULL, NULL, 0, 50, '0', NULL, '格式错误', 'datetime', 'array (\n  ''format'' => ''Y-m-d'',\n  ''defaulttype'' => ''1'',\n)', NULL, 1, 1, '1', 2),
({MODELID}, 'update_time', '更新时间', '默认最后更新时间', NULL, NULL, 0, 50, '0', NULL, '格式错误', 'datetime', 'array (\n  ''format'' => ''Y-m-d'',\n  ''defaulttype'' => ''1'',\n)', NULL, 1, 1, '1', 2);
