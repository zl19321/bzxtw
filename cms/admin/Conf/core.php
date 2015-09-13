<?php
// +----------------------------------------------------------------------
// | FangFa Net [ http://www.fangfa.net ]
// +----------------------------------------------------------------------
// | File: core.php
// +----------------------------------------------------------------------
// | Date: 2010-5-6
// +----------------------------------------------------------------------
// | Author: fangfa <1364119331@qq.com>
// +----------------------------------------------------------------------
// | 文件描述: 定义项目默认要加载的类文件
// +----------------------------------------------------------------------



// 加载的核心文件列表
return array(
    THINK_PATH.'Lib/Think/Exception/ThinkException.class.php',  // 异常处理类
    THINK_PATH.'Lib/Think/Core/Log.class.php',    // 日志处理类
    THINK_PATH.'Lib/Think/Core/App.class.php',   // 应用程序类
    THINK_PATH.'Lib/Think/Core/Action.class.php', // 控制器类
    THINK_PATH.'Lib/Think/Core/View.class.php',  // 视图类
    APP_PATH.'Common/alias.php',  // 加载别名
);
?>