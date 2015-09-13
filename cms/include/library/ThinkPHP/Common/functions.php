<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2009 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
// $Id$

/**
 +------------------------------------------------------------------------------
 * Think公共函数库
 +------------------------------------------------------------------------------
 * @category   Think
 * @package  Common
 * @author   liu21st <liu21st@gmail.com>
 * @version  $Id$
 +------------------------------------------------------------------------------
 */

// URL组装 支持不同模式和路由
function U($url,$params=array(),$redirect=false,$suffix=true) {
    if(0===strpos($url,'/'))
        $url   =  substr($url,1);
    if(!strpos($url,'://')) // 没有指定项目名 使用当前项目名
        $url   =  APP_NAME.'://'.$url;
    if(stripos($url,'@?')) { // 给路由传递参数
        $url   =  str_replace('@?','@think?',$url);
    }elseif(stripos($url,'@')) { // 没有参数的路由
        $url   =  $url.MODULE_NAME;
    }
    // 分析URL地址
    $array   =  parse_url($url);
    $app      =  isset($array['scheme'])?   $array['scheme']  :APP_NAME;
    $route    =  isset($array['user'])?$array['user']:'';
    if(defined('GROUP_NAME') && strcasecmp(GROUP_NAME,C('DEFAULT_GROUP')))
        $group=  GROUP_NAME;
    if(isset($array['path'])) {
        $action  =  substr($array['path'],1);
        if(!isset($array['host'])) {
            // 没有指定模块名
            $module = MODULE_NAME;
        }else{// 指定模块
            if(strpos($array['host'],'-')) {
                list($group,$module) = explode('-',$array['host']);
            }else{
                $module = $array['host'];
            }
        }
    }else{ // 只指定操作
        $module = MODULE_NAME;
        $action   =  $array['host'];
    }
    if(isset($array['query'])) {
        parse_str($array['query'],$query);
        $params = array_merge($query,$params);
    }

    if(C('URL_DISPATCH_ON') && C('URL_MODEL')>0) {
        $depr = C('URL_PATHINFO_MODEL')==2?C('URL_PATHINFO_DEPR'):'/';
        $str    =   $depr;
        foreach ($params as $var=>$val)
            $str .= $var.$depr.$val.$depr;
        $str = substr($str,0,-1);
        $group   = isset($group)?$group.$depr:'';
        if(!empty($route)) {
            $url    =   str_replace(APP_NAME,$app,__APP__).'/'.$group.$route.$str;
        }else{
            $url    =   str_replace(APP_NAME,$app,__APP__).'/'.$group.$module.$depr.$action.$str;
        }
        if($suffix && C('URL_HTML_SUFFIX'))
            $url .= C('URL_HTML_SUFFIX');
    }else{
    	if(version_compare(PHP_VERSION,'5.3','<')) {
    		$params =   http_build_query($params);
    	} else {
    		$params =   htmlspecialchars_decode(http_build_query($params));
    	}

        if(isset($group)) {
            $url    =   str_replace(APP_NAME,$app,__APP__).'?'.C('VAR_GROUP').'='.$group.'&'.C('VAR_MODULE').'='.$module.'&'.C('VAR_ACTION').'='.$action.'&'.$params;
        }else{
            $url    =   str_replace(APP_NAME,$app,__APP__).'?'.C('VAR_MODULE').'='.$module.'&'.C('VAR_ACTION').'='.$action.'&'.$params;
        }
    }
    /*去掉会生成末尾带有不必要的&的URL的BUG,主要是为了url的美观*/
    $lastChar = substr($url,-1,1);
    if ($lastChar == '&' || $lastChar == '?') {
    	$url = substr($url,0,strlen($url)-1);
    }
    /*end */
    if($redirect)
        redirect($url);
    else
        return $url;
}

/**
 +----------------------------------------------------------
 * 字符串命名风格转换
 * type
 * =0 将Java风格转换为C的风格
 * =1 将C风格转换为Java的风格
 +----------------------------------------------------------
 * @access protected
 +----------------------------------------------------------
 * @param string $name 字符串
 * @param integer $type 转换类型
 +----------------------------------------------------------
 * @return string
 +----------------------------------------------------------
 */
function parse_name($name,$type=0) {
    if($type) {
        return ucfirst(preg_replace("/_([a-zA-Z])/e", "strtoupper('\\1')", $name));
    }else{
        $name = preg_replace("/[A-Z]/", "_\\0", $name);
        return strtolower(trim($name, "_"));
    }
}

// 错误输出
function halt($error) {
    if(IS_CLI)   exit ($error);
    $e = array();
    if(C('APP_DEBUG')){
        //调试模式下输出错误信息
        if(!is_array($error)) {
            $trace = debug_backtrace();
            $e['message'] = $error;
            $e['file'] = $trace[0]['file'];
            $e['class'] = $trace[0]['class'];
            $e['function'] = $trace[0]['function'];
            $e['line'] = $trace[0]['line'];
            $traceInfo='';
            $time = date("y-m-d H:i:m");
            foreach($trace as $t)
            {
                $traceInfo .= '['.$time.'] '.$t['file'].' ('.$t['line'].') <br/>';
//                $traceInfo .= $t['class'].$t['type'].$t['function'].'(';
//                $traceInfo .= implode(', ', $t['args']);
//                $traceInfo .=")<br/>";
            }
            $e['trace']  = $traceInfo;
        }else {
            $e = $error;
        }
        // 包含异常页面模板
        include C('TMPL_EXCEPTION_FILE');
    }
    else
    {
        //否则定向到错误页面
        $error_page =   C('ERROR_PAGE');
        if(!empty($error_page)){
            redirect($error_page);
        }else {
            if(C('SHOW_ERROR_MSG'))
                $e['message'] =  is_array($error)?$error['message']:$error;
            else
                $e['message'] = C('ERROR_MESSAGE');
            // 包含异常页面模板
            include C('TMPL_EXCEPTION_FILE');
        }
    }
    exit;
}

// URL重定向
function redirect($url,$time=0,$msg='')
{
    //多行URL地址支持
    $url = str_replace(array("\n", "\r"), '', $url);
    if(empty($msg))
        $msg    =   "系统将在{$time}秒之后自动跳转到{$url}！";
    if (!headers_sent()) {
        // redirect
        if(0===$time) {
            header("Location: ".$url);
        }else {
            header("refresh:{$time};url={$url}");
            echo($msg);
        }
        exit();
    }else {
        $str    = "<meta http-equiv='Refresh' content='{$time};URL={$url}'>";
        if($time!=0)
            $str   .=   $msg;
        exit($str);
    }
}

// 自定义异常处理
function throw_exception($msg,$type='ThinkException',$code=0)
{
    if(IS_CLI)   exit($msg);
    if(class_exists($type,false))
        throw new $type($msg,$code,true);
    else
        halt($msg);        // 异常类型不存在则输出错误信息字串
}

// 区间调试开始
function debug_start($label='')
{
    $GLOBALS[$label]['_beginTime'] = microtime(TRUE);
    if ( MEMORY_LIMIT_ON )  $GLOBALS[$label]['_beginMem'] = memory_get_usage();
}

// 区间调试结束，显示指定标记到当前位置的调试
function debug_end($label='')
{
    $GLOBALS[$label]['_endTime'] = microtime(TRUE);
    echo '<div style="text-align:center;width:100%">Process '.$label.': Times '.number_format($GLOBALS[$label]['_endTime']-$GLOBALS[$label]['_beginTime'],6).'s ';
    if ( MEMORY_LIMIT_ON )  {
        $GLOBALS[$label]['_endMem'] = memory_get_usage();
        echo ' Memories '.number_format(($GLOBALS[$label]['_endMem']-$GLOBALS[$label]['_beginMem'])/1024).' k';
    }
    echo '</div>';
}

// 浏览器友好的变量输出
function dump($var, $echo=true,$label=null, $strict=true)
{
    $label = ($label===null) ? '' : rtrim($label) . ' ';
    if(!$strict) {
        if (ini_get('html_errors')) {
            $output = print_r($var, true);
            $output = "<pre>".$label.htmlspecialchars($output,ENT_QUOTES)."</pre>";
        } else {
            $output = $label . " : " . print_r($var, true);
        }
    }else {
        ob_start();
        var_dump($var);
        $output = ob_get_clean();
        if(!extension_loaded('xdebug')) {
            $output = preg_replace("/\]\=\>\n(\s+)/m", "] => ", $output);
            $output = '<pre>'. $label. htmlspecialchars($output, ENT_QUOTES). '</pre>';
        }
    }
    if ($echo) {
        echo($output);
        return null;
    }else
        return $output;
}

// 取得对象实例 支持调用类的静态方法
function get_instance_of($name,$method='',$args=array())
{
    static $_instance = array();
    $identify   =   empty($args)?$name.$method:$name.$method.to_guid_string($args);
    if (!isset($_instance[$identify])) {
        if(class_exists($name)){
            $o = new $name();
            if(method_exists($o,$method)){
                if(!empty($args)) {
                    $_instance[$identify] = call_user_func_array(array(&$o, $method), $args);
                }else {
                    $_instance[$identify] = $o->$method();
                }
            }
            else
                $_instance[$identify] = $o;
        }
        else
            halt(L('_CLASS_NOT_EXIST_').':'.$name);
    }
    return $_instance[$identify];
}

/**
 +----------------------------------------------------------
 * 系统自动加载ThinkPHP基类库和当前项目的model和Action对象
 * 并且支持配置自动加载路径
 +----------------------------------------------------------
 * @param string $name 对象类名
 +----------------------------------------------------------
 * @return void
 +----------------------------------------------------------
 */
function __autoload($name)
{
    // 检查是否存在别名定义
    if(alias_import($name)) return ;
    // 自动加载当前项目的Actioon类和Model类
    if(substr($name,-5)=="Model") {
        require_cache(LIB_PATH.'Model/'.$name.'.class.php');
    }elseif(substr($name,-6)=="Action"){
        require_cache(LIB_PATH.'Action/'.$name.'.class.php');
    }else {
        // 根据自动加载路径设置进行尝试搜索
        if(C('APP_AUTOLOAD_PATH')) {
            $paths  =   explode(',',C('APP_AUTOLOAD_PATH'));
            foreach ($paths as $path){
                if(import($path.$name)) {
                    // 如果加载类成功则返回
                    return ;
                }
            }
        }
    }
    return ;
}

// 优化的require_once
function require_cache($filename)
{
    static $_importFiles = array();
    $filename   =  realpath($filename);
    if (!isset($_importFiles[$filename])) {
        if(file_exists_case($filename)){
            require_once $filename;
            $_importFiles[$filename] = true;
        }
        else
        {
            $_importFiles[$filename] = false;
        }
    }
    return $_importFiles[$filename];
}

// 区分大小写的文件存在判断
function file_exists_case($filename) {
    if(is_file($filename)) {
        if(IS_WIN && C('APP_FILE_CASE')) {
            if(basename(realpath($filename)) != basename($filename))
                return false;
        }
        return true;
    }
    return false;
}

/**
 +----------------------------------------------------------
 * 导入所需的类库 同java的Import
 * 本函数有缓存功能
 +----------------------------------------------------------
 * @param string $class 类库命名空间字符串
 * @param string $baseUrl 起始路径
 * @param string $ext 导入的文件扩展名
 +----------------------------------------------------------
 * @return boolen
 +----------------------------------------------------------
 */
function import($class,$baseUrl = '',$ext='.class.php')
{
    static $_file = array();
    static $_class = array();
    $class    =   str_replace(array('.','#'), array('/','.'), $class);
    if('' === $baseUrl && false === strpos($class,'/')) {
        // 检查别名导入
        return alias_import($class);
    }    //echo('<br>'.$class.$baseUrl);
    if(isset($_file[$class.$baseUrl]))
        return true;
    else
        $_file[$class.$baseUrl] = true;
    $class_strut = explode("/",$class);
    if(empty($baseUrl)) {
        if('@'==$class_strut[0] || APP_NAME == $class_strut[0] ) {
            //加载当前项目应用类库
            $baseUrl   =  dirname(LIB_PATH);
            $class =  str_replace(array(APP_NAME.'/','@/'),LIB_DIR.'/',$class);
        }elseif(in_array(strtolower($class_strut[0]),array('think','org','com'))) {
            //加载ThinkPHP基类库或者公共类库
            // think 官方基类库 org 第三方公共类库 com 企业公共类库
            $baseUrl =  THINK_PATH.'/Lib/';
        }else {
            // 加载其他项目应用类库
            $class    =   substr_replace($class, '', 0,strlen($class_strut[0])+1);
            $baseUrl =  APP_PATH.'/../'.$class_strut[0].'/'.LIB_DIR.'/';
        }
    }
    if(substr($baseUrl, -1) != "/")    $baseUrl .= "/";
    $classfile = $baseUrl . $class . $ext;
    if($ext == '.class.php' && is_file($classfile)) {
        // 冲突检测
        $class = basename($classfile,$ext);
//        if(isset($_class[$class])) {
//        	throw_exception(L('_CLASS_CONFLICT_').':'.$_class[$class].' '.$classfile);
//        }
        $_class[$class] = $classfile;
    }
    //导入目录下的指定类库文件
    return require_cache($classfile);
}

/**
 +----------------------------------------------------------
 * 基于命名空间方式导入函数库
 * load('@.Util.Array')
 +----------------------------------------------------------
 * @param string $name 函数库命名空间字符串
 * @param string $baseUrl 起始路径
 * @param string $ext 导入的文件扩展名
 +----------------------------------------------------------
 * @return void
 +----------------------------------------------------------
 */
function load($name,$baseUrl='',$ext='.php') {
    $name    =   str_replace(array('.','#'), array('/','.'), $name);
    if(empty($baseUrl)) {
        if(0 === strpos($name,'@/')) {
            //加载当前项目函数库
            $baseUrl   =  APP_PATH.'/Common/';
            $name =  substr($name,2);
        }else{
            //加载ThinkPHP 系统函数库
            $baseUrl =  THINK_PATH.'/Common/';
        }
    }
    if(substr($baseUrl, -1) != "/")    $baseUrl .= "/";
    include $baseUrl . $name . $ext;
}

// 快速导入第三方框架类库
// 所有第三方框架的类库文件统一放到 系统的Vendor目录下面
// 并且默认都是以.php后缀导入
function vendor($class,$baseUrl = '',$ext='.php')
{
    if(empty($baseUrl))  $baseUrl    =   VENDOR_PATH;
    return import($class,$baseUrl,$ext);
}

// 快速定义和导入别名
function alias_import($alias,$classfile='') {
    static $_alias   =  array();
    if('' !== $classfile) {
        // 定义别名导入
        $_alias[$alias]  = $classfile;
        return ;
    }
    if(is_string($alias)) {
        if(isset($_alias[$alias]))
            return require_cache($_alias[$alias]);
    }elseif(is_array($alias)){
        foreach ($alias as $key=>$val)
            $_alias[$key]  =  $val;
        return ;
    }
    return false;
}

/**
 +----------------------------------------------------------
 * D函数用于实例化Model
 +----------------------------------------------------------
 * @param string name Model名称
 * @param string app Model所在项目
 +----------------------------------------------------------
 * @return Model
 +----------------------------------------------------------
 */
function D($name='',$app='')
{
    static $_model = array();
    if(empty($name)) return new Model;
    if(empty($app))   $app =  C('DEFAULT_APP');
    if(isset($_model[$app.$name]))
        return $_model[$app.$name];
    $OriClassName = $name;
    if(strpos($name,C('APP_GROUP_DEPR'))) {
        $array   =  explode(C('APP_GROUP_DEPR'),$name);
        $name = array_pop($array);
        $className =  $name.'Model';
        import($app.'.Model.'.implode('.',$array).'.'.$className);
    }else{
        $className =  $name.'Model';
        import($app.'.Model.'.$className);
    }
    if(class_exists($className)) {
        $model = new $className();
    }else {
        $model  = new Model($name);
    }
    $_model[$app.$OriClassName] =  $model;
    return $model;
}

/**
 +----------------------------------------------------------
 * M函数用于实例化一个没有模型文件的Model
 +----------------------------------------------------------
 * @param string name Model名称
 +----------------------------------------------------------
 * @return Model
 +----------------------------------------------------------
 */
function M($name='',$class='Model') {
    static $_model = array();
    if(!isset($_model[$name.'_'.$class]))
        $_model[$name.'_'.$class]   = new $class($name);
    return $_model[$name.'_'.$class];
}

/**
 +----------------------------------------------------------
 * A函数用于实例化Action
 +----------------------------------------------------------
 * @param string name Action名称
 * @param string app Model所在项目
 +----------------------------------------------------------
 * @return Action
 +----------------------------------------------------------
 */
function A($name,$app='@')
{
    static $_action = array();
    if(isset($_action[$app.$name]))
        return $_action[$app.$name];
    $OriClassName = $name;
    if(strpos($name,C('APP_GROUP_DEPR'))) {
        $array   =  explode(C('APP_GROUP_DEPR'),$name);
        $name = array_pop($array);
        $className =  $name.'Action';
        import($app.'.Action.'.implode('.',$array).'.'.$className);
    }else{
        $className =  $name.'Action';
        import($app.'.Action.'.$className);
    }
    if(class_exists($className)) {
        $action = new $className();
        $_action[$app.$OriClassName] = $action;
        return $action;
    }else {
        return false;
    }
}

// 远程调用模块的操作方法
function R($module,$action,$app='@') {
    $class = A($module,$app);
    if($class)
        return call_user_func(array(&$class,$action));
    else
        return false;
}

// 获取和设置语言定义(不区分大小写)
function L($name=null,$value=null) {
    static $_lang = array();
    // 空参数返回所有定义
    if(empty($name)) return $_lang;
    // 判断语言获取(或设置)
    // 若不存在,直接返回全大写$name
    if (is_string($name) )
    {
        if (is_null($value))
            return isset($_lang[$name]) ? $_lang[$name] : $name;
        $_lang[$name] = $value;// 语言定义
        return;
    }
    // 批量定义
    if (is_array($name))
        $_lang = array_merge($_lang,array_change_key_case($name,CASE_UPPER));
    return;
}

// 获取配置值
function C($name=null,$value=null)
{
    static $_config = array();
    // 无参数时获取所有
    if(empty($name)) return $_config;
    // 优先执行设置获取或赋值
    if (is_string($name))
    {
        if (!strpos($name,'.')) {
            $name = strtolower($name);
            if (is_null($value))
                return isset($_config[$name])? $_config[$name] : null;
            $_config[$name] = $value;
            return;
        }
        // 二维数组设置和获取支持
        $name = explode('.',$name);
        $name[0]   = strtolower($name[0]);
        if (is_null($value))
            return isset($_config[$name[0]][$name[1]]) ? $_config[$name[0]][$name[1]] : null;
        $_config[$name[0]][$name[1]] = $value;
        return;
    }
    // 批量设置
    if(is_array($name))
        return $_config = array_merge($_config,array_change_key_case($name));
    return null;// 避免非法参数
}

// 处理标签
function tag($name,$params=array()) {
    $tags   =  C('_tags_.'.$name);
    if($tags) {
        foreach ($tags   as $key=>$call){
            if(is_callable($call)) {
            	$result = call_user_func_array($call,$params);
            } else {  //TODO 根据参数，载入hook目录中的文件，进行调用

            }
        }
        return $result;
    }
    return false;
}

// 执行行为
function B($name) {
    $class = $name.'Behavior';
    require_cache(LIB_PATH.'Behavior/'.$class.'.class.php');
    $behavior   =  new $class();
    $behavior->run();
}

// 渲染输出Widget
function W($name,$data=array(),$return=false) {
    $class = $name.'Widget';
    require_cache(LIB_PATH.'Widget/'.$class.'.class.php');
    if(!class_exists($class))
        throw_exception(L('_CLASS_NOT_EXIST_').':'.$class);
    $widget  =  Think::instance($class);
    $content = $widget->render($data);
    if($return)
        return $content;
    else
        echo $content;
}

// 全局缓存设置和读取
function S($name,$value='',$expire='',$type='') {
    static $_cache = array();
    alias_import('Cache');
    //取得缓存对象实例
    $cache  = Cache::getInstance($type);
    if('' !== $value) {
        if(is_null($value)) {
            // 删除缓存
            $result =   $cache->rm($name);
            if($result)   unset($_cache[$type.'_'.$name]);
            return $result;
        }else{
            // 缓存数据
            $cache->set($name,$value,$expire);
            $_cache[$type.'_'.$name]     =   $value;
        }
        return ;
    }
    if(isset($_cache[$type.'_'.$name]))
        return $_cache[$type.'_'.$name];
    // 获取缓存数据
    $value      =  $cache->get($name);
    $_cache[$type.'_'.$name]     =   $value;
    return $value;
}

// 快速文件数据读取和保存 针对简单类型数据 字符串、数组
function F($name,$value='',$path=DATA_PATH) {
    static $_cache = array();
    $filename   =   $path.$name.'.php';
    if('' !== $value) {
        if(is_null($value)) {
            // 删除缓存
            return unlink($filename);
        }else{
            // 缓存数据
            $dir   =  dirname($filename);
            // 目录不存在则创建
            if(!is_dir($dir))  mkdir($dir);
            return file_put_contents($filename,"<?php\nreturn ".var_export($value,true).";\n?>");
        }
    }
    if(isset($_cache[$name])) return $_cache[$name];
    // 获取缓存数据
    if(is_file($filename)) {
        $value   =  include $filename;
        $_cache[$name]   =   $value;
    }else{
        $value  =   false;
    }
    return $value;
}

// 根据PHP各种类型变量生成唯一标识号
function to_guid_string($mix)
{
    if(is_object($mix) && function_exists('spl_object_hash')) {
        return spl_object_hash($mix);
    }elseif(is_resource($mix)){
        $mix = get_resource_type($mix).strval($mix);
    }else{
        $mix = serialize($mix);
    }
    return md5($mix);
}

//[RUNTIME]
// 编译文件
function compile($filename,$runtime=false) {
    $content = file_get_contents($filename);
    if(true === $runtime)
        // 替换预编译指令
        $content = preg_replace('/\/\/\[RUNTIME\](.*?)\/\/\[\/RUNTIME\]/s','',$content);
    $content = substr(trim($content),5);
    if('?>' == substr($content,-2))
        $content = substr($content,0,-2);
    return $content;
}

// 去除代码中的空白和注释
function strip_whitespace($content) {
    $stripStr = '';
    //分析php源码
    $tokens =   token_get_all ($content);
    $last_space = false;
    for ($i = 0, $j = count ($tokens); $i < $j; $i++)
    {
        if (is_string ($tokens[$i]))
        {
            $last_space = false;
            $stripStr .= $tokens[$i];
        }
        else
        {
            switch ($tokens[$i][0])
            {
                //过滤各种PHP注释
                case T_COMMENT:
                case T_DOC_COMMENT:
                    break;
                //过滤空格
                case T_WHITESPACE:
                    if (!$last_space)
                    {
                        $stripStr .= ' ';
                        $last_space = true;
                    }
                    break;
                default:
                    $last_space = false;
                    $stripStr .= $tokens[$i][1];
            }
        }
    }
    return $stripStr;
}
// 根据数组生成常量定义
function array_define($array) {
    $content = '';
    foreach($array as $key=>$val) {
        $key =  strtoupper($key);
        if(in_array($key,array('THINK_PATH','APP_NAME','APP_PATH','RUNTIME_PATH','RUNTIME_ALLINONE','THINK_MODE')))
            $content .= 'if(!defined(\''.$key.'\')) ';
        if(is_int($val) || is_float($val)) {
            $content .= "define('".$key."',".$val.");";
        }elseif(is_bool($val)) {
            $val = ($val)?'true':'false';
            $content .= "define('".$key."',".$val.");";
        }elseif(is_string($val)) {
            $content .= "define('".$key."','".addslashes($val)."');";
        }
    }
    return $content;
}
//[/RUNTIME]

// 循环创建目录
function mk_dir($dir, $mode = 0755)
{
  if (is_dir($dir) || @mkdir($dir,$mode)) return true;
  if (!mk_dir(dirname($dir),$mode)) return false;
  return @mkdir($dir,$mode);
}

// 自动转换字符集 支持数组转换
function auto_charset($fContents,$from,$to){
    $from   =  strtoupper($from)=='UTF8'? 'utf-8':$from;
    $to       =  strtoupper($to)=='UTF8'? 'utf-8':$to;
    if( strtoupper($from) === strtoupper($to) || empty($fContents) || (is_scalar($fContents) && !is_string($fContents)) ){
        //如果编码相同或者非字符串标量则不转换
        return $fContents;
    }
    if(is_string($fContents) ) {
        if(function_exists('mb_convert_encoding')){
            return mb_convert_encoding ($fContents, $to, $from);
        }elseif(function_exists('iconv')){
            return iconv($from,$to,$fContents);
        }else{
            return $fContents;
        }
    }
    elseif(is_array($fContents)){
        foreach ( $fContents as $key => $val ) {
            $_key =     auto_charset($key,$from,$to);
            $fContents[$_key] = auto_charset($val,$from,$to);
            if($key != $_key )
                unset($fContents[$key]);
        }
        return $fContents;
    }
    else{
        return $fContents;
    }
}

// xml编码
function xml_encode($data,$encoding='utf-8',$root="think") {
    $xml = '<?xml version="1.0" encoding="'.$encoding.'"?>';
    $xml.= '<'.$root.'>';
    $xml.= data_to_xml($data);
    $xml.= '</'.$root.'>';
    return $xml;
}

function data_to_xml($data) {
    if(is_object($data)) {
        $data = get_object_vars($data);
    }
    $xml = '';
    foreach($data as $key=>$val) {
        is_numeric($key) && $key="item id=\"$key\"";
        $xml.="<$key>";
        $xml.=(is_array($val)||is_object($val))?data_to_xml($val):$val;
        list($key,)=explode(' ',$key);
        $xml.="</$key>";
    }
    return $xml;
}

/**
 +----------------------------------------------------------
 * Cookie 设置、获取、清除 (支持数组或对象直接设置) 2009-07-9
 +----------------------------------------------------------
 * 1 获取cookie: cookie('name')
 * 2 清空当前设置前缀的所有cookie: cookie(null)
 * 3 删除指定前缀所有cookie: cookie(null,'think_') | 注：前缀将不区分大小写
 * 4 设置cookie: cookie('name','value') | 指定保存时间: cookie('name','value',3600)
 * 5 删除cookie: cookie('name',null)
 +----------------------------------------------------------
 * $option 可用设置prefix,expire,path,domain
 * 支持数组形式:cookie('name','value',array('expire'=>1,'prefix'=>'think_'))
 * 支持query形式字符串:cookie('name','value','prefix=tp_&expire=10000')
 */
function cookie($name,$value='',$option=null)
{
    // 默认设置
    $config = array(
        'prefix' => C('COOKIE_PREFIX'), // cookie 名称前缀
        'expire' => C('COOKIE_EXPIRE'), // cookie 保存时间
        'path'   => C('COOKIE_PATH'),   // cookie 保存路径
        'domain' => C('COOKIE_DOMAIN'), // cookie 有效域名
    );
    // 参数设置(会覆盖黙认设置)
    if (!empty($option)) {
        if (is_numeric($option))
            $option = array('expire'=>$option);
        elseif( is_string($option) )
            parse_str($option,$option);
        array_merge($config,array_change_key_case($option));
    }
    // 清除指定前缀的所有cookie
    if (is_null($name)) {
       if (empty($_COOKIE)) return;
       // 要删除的cookie前缀，不指定则删除config设置的指定前缀
       $prefix = empty($value)? $config['prefix'] : $value;
       if (!empty($prefix))// 如果前缀为空字符串将不作处理直接返回
       {
           foreach($_COOKIE as $key=>$val) {
               if (0 === stripos($key,$prefix)){
                    setcookie($_COOKIE[$key],'',time()-3600,$config['path'],$config['domain']);
                    unset($_COOKIE[$key]);
               }
           }
       }
       return;
    }
    $name = $config['prefix'].$name;
    if (''===$value){
        return isset($_COOKIE[$name]) ? unserialize($_COOKIE[$name]) : null;// 获取指定Cookie
    }else {
        if (is_null($value)) {
            setcookie($name,'',time()-3600,$config['path'],$config['domain']);
            unset($_COOKIE[$name]);// 删除指定cookie
        }else {
            // 设置cookie
            $expire = !empty($config['expire'])? time()+ intval($config['expire']):0;
            setcookie($name,serialize($value),$expire,$config['path'],$config['domain']);
            $_COOKIE[$name] = serialize($value);
        }
    }
}


// 获取客户端IP地址
function get_client_ip(){
   if (getenv("HTTP_CLIENT_IP") && strcasecmp(getenv("HTTP_CLIENT_IP"), "unknown"))
       $ip = getenv("HTTP_CLIENT_IP");
   else if (getenv("HTTP_X_FORWARDED_FOR") && strcasecmp(getenv("HTTP_X_FORWARDED_FOR"), "unknown"))
       $ip = getenv("HTTP_X_FORWARDED_FOR");
   else if (getenv("REMOTE_ADDR") && strcasecmp(getenv("REMOTE_ADDR"), "unknown"))
       $ip = getenv("REMOTE_ADDR");
   else if (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], "unknown"))
       $ip = $_SERVER['REMOTE_ADDR'];
   else
       $ip = "unknown";
   return($ip);
}

/**
 +----------------------------------------------------------
 * 字节格式化 把字节数格式为 B K M G T 描述的大小
 +----------------------------------------------------------
 * @return string
 +----------------------------------------------------------
 */
function byte_format($size, $dec=2)
{
	$a = array("B", "KB", "MB", "GB", "TB", "PB");
	$pos = 0;
	while ($size >= 1024) {
		 $size /= 1024;
		   $pos++;
	}
	return round($size,$dec)." ".$a[$pos];
}

/**
 +----------------------------------------------------------
 * 字符串截取，支持中文和其他编码
 +----------------------------------------------------------
 * @static
 * @access public
 +----------------------------------------------------------
 * @param string $str 需要转换的字符串
 * @param string $start 开始位置
 * @param string $length 截取长度
 * @param string $charset 编码格式
 * @param string $suffix 截断显示字符
 +----------------------------------------------------------
 * @return string
 +----------------------------------------------------------
 */
function msubstr($str, $start=0, $length, $charset="utf-8", $suffix=true)
{
    if(function_exists("mb_substr")) {
        $slice = mb_substr($str, $start, $length, $charset);
    } elseif(function_exists('iconv_substr')) {
        $slice = iconv_substr($str,$start,$length,$charset);
    } else {
        $re['utf-8']   = "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xff][\x80-\xbf]{3}/";
        $re['gb2312'] = "/[\x01-\x7f]|[\xb0-\xf7][\xa0-\xfe]/";
        $re['gbk']    = "/[\x01-\x7f]|[\x81-\xfe][\x40-\xfe]/";
        $re['big5']   = "/[\x01-\x7f]|[\x81-\xfe]([\x40-\x7e]|\xa1-\xfe])/";
        preg_match_all($re[$charset], $str, $match);
        $slice = join("",array_slice($match[0], $start, $length));
    }
    if($suffix && strlen($str) > strlen($slice)) $slice.="...";
    return $slice;
}

//初始化session设置
function init_sesstion() {
	$type = C ('SESSION_SAVE_TYPE');
	if ( strtolower($type) == 'file') {//文件类型
       $path = C('SESSION_SAVE_PATH');
	   $path = empty($path) ? '' : FANGFACMS_ROOT . $path;
	   Session::path($path);
    } elseif ( strtolower($type) == 'memcache') {//内存类型
        ini_set("session.save_handler", "memcache");
        ini_set("session.save_path", "tcp://".C ('MEMCACHE_SERVER').":".C ('MEMCACHE_PORT'));
    } elseif( strtolower($type) == 'mysql') {//数据库存储
		ini_set("session.save_handler","user");
		import('SessionSql',INCLUDE_PATH);
		SessionSql::init();
		$result = session_set_save_handler(array('SessionSql',"sess_open"),
                         array('SessionSql',"sess_close"),
                         array('SessionSql',"sess_read"),
                         array('SessionSql',"sess_write"),
                         array('SessionSql',"sess_destory"),
                         array('SessionSql',"sess_gc"));
	}
}

/**
 * 生成唯一的时间串、用户重命名上传文件
 */
function times() {
	return date('YmdHis',time()).rand(100,199);
}

/**
 * 将html文档装换为纯文本
 * @param $document
 */
function html2txt($document){
	$search = array('@<script[^>]*?>.*?</script>@si',  // Strip out javascript
	              '@<[\/\!]*?[^<>]*?>@si',            // Strip out HTML tags
	              '@<style[^>]*?>.*?</style>@siU',    // Strip style tags properly
	              '@<![\s\S]*?--[ \t\n\r]*>@'         // Strip multi-line comments including CDATA
	);
	$text = preg_replace($search, '', $document);
	return $text;
}

/**
 * 安全快速文件数据读取和保存 针对简单类型数据 字符串、数组
 *
 * 加入数据校验机制，防止大数据量访问情况下数据破损问题
 *
 * 用法同F () 函数完全一样
 *
 * @param string $name  缓存变量名
 * @param mixed $value  缓存数据
 * @param int $time  数据缓存生命周期   单位：秒
 * @param string $path  数据缓存路径
 * @return boolean or array
 */
function FC ($name,$value='',$time = 900,$path=DATA_PATH) {
	static $_cache = array();
    $filename   =   $path.$name.'.php';
    if('' !== $value) {
        if(is_null($value)) {
            // 删除缓存
            return unlink($filename);
        }else{  //写入缓存
            // 缓存数据
            $dir   =  dirname($filename);
            // 目录不存在则创建
            if(!is_dir($dir))  mkdir($dir);
            //加入数据校验机制
            $value = serialize($value);
	        $prefix = '<?php die(); ?> ';
	        $hash = sprintf('% 32d', crc32($value));
	        $value = $prefix . $hash . $value;
            return file_put_contents($filename,$value,LOCK_EX);
        }
    }
    if(isset($_cache[$name])) return $_cache[$name];
    // 获取缓存数据
    if(is_file($filename)) {
    	//判断数据有效期
    	$filetime = filemtime($filename);
    	if (time() >= $filetime + $time) {
    		if(isset($_cache[$name])) unset($_cache[$name]);
    		return false;
    	}
    	if(isset($_cache[$name])) return $_cache[$name];
    	//进行数据校验
    	$value = safe_file_get_contents($filename);
        $hash = substr($value, 16, 32);
        $value = substr($value, 48);
        if (crc32($value) != $hash || strlen($hash) != 32) {
            $value = false;
        } else {
        	$value = unserialize($value);
        	$_cache[$name]   =   $value;
        }
    }else{
        $value  =   false;
    }
    return $value;
}


/**
 * safe_file_get_contents() 用共享锁模式打开文件并读取内容，可以避免在并发写入造成的读取不完整问题
 *
 * @param string $filename
 *
 * @return mixed
 */
function safe_file_get_contents($filename) {
    $fp = fopen($filename, 'rb');
    if ($fp) {
        flock($fp, LOCK_SH);
        clearstatcache();
        $filesize = filesize($filename);
        if ($filesize > 0) {
            $data = fread($fp, $filesize);
        } else {
            $data = false;
        }
        flock($fp, LOCK_UN);
        fclose($fp);
        return $data;
    } else {
        return false;
    }
}


/**
 +----------------------------------------------------------
 * 把返回的数据集转换成Tree
 +----------------------------------------------------------
 * @access public
 +----------------------------------------------------------
 * @param array $list 要转换的数据集
 * @param string $pid parent标记字段
 * @param string $level level标记字段
 +----------------------------------------------------------
 * @return array
 +----------------------------------------------------------
 */
function list_to_tree($list, $pk='id',$pid = 'pid',$child = '_child',$root=0)
{
    // 创建Tree
    $tree = array();
    if(is_array($list)) {
        // 创建基于主键的数组引用
        $refer = array();
        foreach ($list as $key => $data) {
            $refer[$data[$pk]] =& $list[$key];
        }
        foreach ($list as $key => $data) {
            // 判断是否存在parent
            $parentId = $data[$pid];
            if ($root == $parentId) {
                $tree[] =& $list[$key];
            }else{
                if (isset($refer[$parentId])) {
                    $parent =& $refer[$parentId];
                    $parent[$child][] =& $list[$key];
                }
            }
        }
    }
    return $tree;
}

/**
 * 数组转换成tree，此函数要求必须有顶级元素（即含有parentid为0的元素）
 *
 * @param array $arr
 * @param int $fid
 * @param string $fparent
 * @param string $fchildrens
 * @param boolean $returnReferences
 */
function array_to_tree($arr, $fid, $fparent = 'parent_id', $fchildrens = 'childrens', $returnReferences = false) {
    $pkvRefs = array();
    foreach ($arr as $offset => $row) {
        $pkvRefs[$row[$fid]] =& $arr[$offset];
    }
    $tree = array();
    foreach ($arr as $offset => $row) {
        $parentId = $row[$fparent];
        if ($parentId) {
            if (!isset($pkvRefs[$parentId])) { continue; }
            $parent =& $pkvRefs[$parentId];
            $parent[$fchildrens][] =& $arr[$offset];
        } else {  //parentId为0的为顶级元素
            $tree[] =& $arr[$offset];
        }
    }
    //当拥有为0的顶级元素的时候，已经顺利排序
    //当没有顶级元素的时候，需要统计$arr中的元素；目前只能返回一个顶级元素的树。
    if(!empty($tree)) {
        if ($returnReferences) {
            return array('tree' => $tree, 'refs' => $pkvRefs);
        } else {
            return $tree;
        }
    } else {
        $count = array();
        foreach($arr as $k=>$v) {
            $count[$k] = count($v,COUNT_RECURSIVE);
        }
        $max = max($count);
        return array($arr[array_search($max,$count)]);
    }

}


/**
 +----------------------------------------------------------
 * 对查询结果集进行排序
 +----------------------------------------------------------
 * @access public
 +----------------------------------------------------------
 * @param array $list 查询结果
 * @param string $field 排序的字段名
 * @param array $sortby 排序类型
 * asc正向排序 desc逆向排序 nat自然排序
 +----------------------------------------------------------
 * @return array
 +----------------------------------------------------------
 */
function list_sort_by($list,$field, $sortby='asc') {
   if(is_array($list)){
       $refer = $resultSet = array();
       foreach ($list as $i => $data)
           $refer[$i] = &$data[$field];
       switch ($sortby) {
           case 'asc': // 正向排序
                asort($refer);
                break;
           case 'DESC':// 逆向排序
                arsort($refer);
                break;
           case 'nat': // 自然排序
                natcasesort($refer);
                break;
       }
       foreach ( $refer as $key=> $val)
           $resultSet[] = &$list[$key];
       return $resultSet;
   }
   return false;
}

/**
 +----------------------------------------------------------
 * 在数据列表中搜索
 +----------------------------------------------------------
 * @access public
 +----------------------------------------------------------
 * @param array $list 数据列表
 * @param mixed $condition 查询条件
 * 支持 array('name'=>$value) 或者 name=$value
 +----------------------------------------------------------
 * @return array
 +----------------------------------------------------------
 */
function list_search($list,$condition) {
    if(is_string($condition))
        parse_str($condition,$condition);
    // 返回的结果集合
    $resultSet = array();
    foreach ($list as $key=>$data){
        $find   =   false;
        foreach ($condition as $field=>$value){
            if(isset($data[$field])) {
                if(0 === strpos($value,'/')) {
                    $find   =   preg_match($value,$data[$field]);
                }elseif($data[$field]==$value){
                    $find = true;
                }
            }
        }
        if($find)
            $resultSet[]     =   &$list[$key];
    }
    return $resultSet;
}

// 发送Http状态信息
function send_http_status($code, $status) {
    static $_status = array(
        // Informational 1xx
        100 => 'Continue',
        101 => 'Switching Protocols',
        // Success 2xx
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        // Redirection 3xx
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Moved Temporarily ',  // 1.1
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        // 306 is deprecated but reserved
        307 => 'Temporary Redirect',
        // Client Error 4xx
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Requested Range Not Satisfiable',
        417 => 'Expectation Failed',
        // Server Error 5xx
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported',
        509 => 'Bandwidth Limit Exceeded'
    );
    if(array_key_exists($code,$_status)) {
        header('HTTP/1.1 '.$code.' '.$_status[$code]);
    }
}
/**
 * 将$message按<br />分割，然后装入ul > li中
 */
function br2li($message) {
	if (!empty($message)) {
		$message = explode('<br />',$message);
	}
	//去除空数组
	foreach ($message as $k=>$v) {
		if (empty($v)) {
			unset($message[$k]);
		} else {
			$message[$k] = '<li>' . $v . '</li>';
		}
	}
	return "<ul>\n" . implode("\n",$message) . "\n</ul>";
}

/**
 * 文件后缀
 *
 * @param string $filename
 */
function fileext($filename) {
	return strtolower(trim(substr(strrchr($filename, '.'), 1, 10)));
}
?>