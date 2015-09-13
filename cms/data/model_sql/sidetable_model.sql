--
-- 创建模型的扩展表 
-- {DB_PREFIX} 代表数据表前缀   
-- {EXTTABLE} 代表扩展表名称   
-- {MODELID} 表示创建的模型ID  
-- 
-- 这些都会被系统自动替换
--

CREATE TABLE IF NOT EXISTS `{DB_PREFIX}sidetable_{EXTTABLE}` (
  `id` INT(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主表ID',
  `sort` INT(10) DEFAULT NULL COMMENT '排序',
  `catid` INT(10) DEFAULT NULL COMMENT '栏目ID ',
  `name` char(80) NOT NULL DEFAULT '' COMMENT '标题',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='副表挂靠';

--
-- 向`model_field`中添加模型必须的主键字段信息以及系统字段
--
INSERT INTO `{DB_PREFIX}model_field` (`modelid`, `field`, `name`, `tips`, `css`, `parent_css`, `minlength`, `maxlength`, `required`, `pattern`, `errortips`, `formtype`, `setting`, `formattribute`, `systype`, `sort`, `status`, `card`, `listshow`) VALUES
({MODELID}, 'id', 'ID', '', NULL, NULL, NULL, NULL, '0', NULL, '', 'id', '', NULL, 2, 1, '1', 1, 1),
({MODELID}, 'catid', '所属栏目', '选择到发布到的栏目，默认当前栏目', NULL, NULL, 0, 0, '1', NULL, '请选择栏目', 'catid', 'array (\n  ''defaultvalue'' => '''',\n)', NULL, 1, 1, '1', 1, 1),
({MODELID}, 'name', '标题', '', 'input', NULL, 0, 60, '1', NULL, '标题在1~60个字间', 'title','array (\n  ''size'' => ''50'',\n)', NULL, 1, 1, '1', 1, 1),
({MODELID}, 'sort', '排序', '选项排列顺序', NULL, NULL, 0, 0, '1', NULL, '请填写排序', 'number', 'array (\n  ''defaultvalue'' => ''1'',\n)', NULL, 0, 1, '1', 1, 1);





