--
-- 创建模型的扩展表
-- {DB_PREFIX} 代表数据表前缀
-- {TABLENAME} 代表数据表除前缀外的名称
-- {EXTTABLE} 代表扩展表名称
-- {MODELID} 表示创建的模型ID
-- 
-- 这些都会被系统自动替换
--

CREATE TABLE IF NOT EXISTS `{DB_PREFIX}{TABLENAME}` (
  `user_id` int(10) DEFAULT '0' COMMENT '用户ID',
  PRIMARY KEY (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='用户模型扩展表之一';
