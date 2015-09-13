<?php
// +----------------------------------------------------------------------
// | FangFa Net [ http://www.fangfa.net ]
// +----------------------------------------------------------------------
// | File: defines.php
// +----------------------------------------------------------------------
// | Date: Thu Apr 22 15:53:17 CST 2010
// +----------------------------------------------------------------------
// | Author: fangfa <1364119331@qq.com>
// +----------------------------------------------------------------------
// | 文件描述: 后台常量文件
// +----------------------------------------------------------------------

//总缓存目录
define('ALL_CACHE_PATH',FANGFACMS_ROOT.'data/');
//Runtime路径
define('RUNTIME_PATH',ALL_CACHE_PATH.'install/');

//库文件夹
define('LIBRARY',INCLUDE_PATH.'library/');

//路径定义文件位置
define('PATH_DEFINE_FILE',APP_PATH.'Conf/paths.php');
//标签存放路径
define('DATA_TAG_PATH',ALL_CACHE_PATH.'tag/');
//数据库备份路径
define('DB_BACKUP_PATH',ALL_CACHE_PATH.'dbbackup/');
//数据缓存路径（model、category、）
define('DATA_CACHE_PATH',ALL_CACHE_PATH.'cache/');



