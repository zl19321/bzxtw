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
 * Smarty模板引擎解析类
 +------------------------------------------------------------------------------
 * @category   Think
 * @package  Think
 * @subpackage  Util
 * @author liu21st <liu21st@gmail.com>
 * @version  $Id$
 +------------------------------------------------------------------------------
 */
class TemplateSmarty
{
    /**
     +----------------------------------------------------------
     * 渲染模板输出
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param string $templateFile 模板文件名
     * @param array $var 模板变量
     * @param string $charset 模板输出字符集
     +----------------------------------------------------------
     * @return void
     +----------------------------------------------------------
     */
    public function fetch($templateFile,$var,$charset) {
        $templateFile=substr($templateFile,strlen(TMPL_PATH));
        vendor('Smarty.Smarty#class');
        $tpl = new Smarty();
        if(C('TMPL_ENGINE_CONFIG')) {
            $config  =  C('TMPL_ENGINE_CONFIG');
            foreach ($config as $key=>$val){
                $tpl->{$key}   =  $val;
            }
        }else{
            $tpl->caching = C('TMPL_CACHE_ON');
            $tpl->template_dir = TMPL_PATH;
            $tpl->compile_dir = CACHE_PATH ;
            $tpl->cache_dir = TEMP_PATH ;
        }
        //register_function
        $tpl->register_function('U', array(& $this, '_U'));
        $tpl->assign($var);
        $tpl->display($templateFile);
    }
    
    /**
     * 添加对现有U函数的支持
     */
    public function _U($params) {
    	$url = isset($params['url']) ? $params['url'] : '';
    	unset($params['url']);
    	$param = isset($params['param']) ? $params['param'] : array();
    	unset($params['param']);
    	$redirect = isset($params['redirect']) ? $params['redirect'] : false;
    	unset($params['redirect']);
    	$suffix = isset($params['suffix']) ? $params['suffix'] : false;
    	unset($params['suffix']);
    	return U($url,$param,$redirect,$suffix);
    }
    
        
}
?>