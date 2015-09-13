<?php
// +----------------------------------------------------------------------
// | FangFa Net [ http://www.fangfa.net ]
// +----------------------------------------------------------------------
// | File: index.php
// +----------------------------------------------------------------------
// | Date: Wed Nov 11 09:56:14 CST 2010
// +----------------------------------------------------------------------
// | Author: fangfa <1364119331@qq.com>
// +----------------------------------------------------------------------
// | 文件描述: 系统的常量定义文件
// +----------------------------------------------------------------------

define ( 'INCLUDE_PATH', FANGFACMS_ROOT . 'include/' );
// 定义ThinkPHP框架路径
define ( 'THINK_PATH', INCLUDE_PATH . 'library/ThinkPHP/' );
//总缓存目录
define ( 'ALL_CACHE_PATH', FANGFACMS_ROOT . 'data/' );
//Runtime路径
define ( 'RUNTIME_PATH', ALL_CACHE_PATH . APP_NAME . '/runtime/' );
//库文件夹
define ( 'LIBRARY', INCLUDE_PATH . 'library/' );
//路径定义文件位置
define ( 'PATH_DEFINE_FILE', APP_PATH . '/Conf/paths.php' );
//数据库备份路径
define ( 'DB_BACKUP_PATH', ALL_CACHE_PATH . 'dbbackup/' );
//缓存路径设置 (仅对File方式缓存有效)
define ( 'DATA_CACHE_PATH', ALL_CACHE_PATH . 'cache/' );
//项目数据文件目录
define ( 'DATA_PATH', ALL_CACHE_PATH . 'cache/' );
//第三方库文件夹
define ( 'VENDOR_PATH', INCLUDE_PATH );
// 为了方便导入第三方类库 设置Vendor目录到include_path
set_include_path ( get_include_path () . PATH_SEPARATOR . VENDOR_PATH );
//是否生成核心编译缓以及设置对编译缓存的内容是否进行去空白和注释，调试状态下不生成
IN_DEBUG && define ( 'NO_CACHE_RUNTIME', true );
IN_DEBUG && define ( 'STRIP_RUNTIME_SPACE', false );