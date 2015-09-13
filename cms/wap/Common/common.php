<?php

// +----------------------------------------------------------------------

// | FangFa Net [ http://www.fangfa.net ]

// +----------------------------------------------------------------------

// | File: common.php

// +----------------------------------------------------------------------

// | Date: Wed Apr 21 16:51:08 CST 2010

// +----------------------------------------------------------------------

// | Author: fangfa <1364119331@qq.com>

// +----------------------------------------------------------------------

// | 文件描述: 前常用函数

// +----------------------------------------------------------------------

defined('IN') or die('Access Denied!');



/**

 +----------------------------------------------------------

 * 检查字符串是否是UTF8编码

 +----------------------------------------------------------

 * @param string $string 字符串

 +----------------------------------------------------------

 * @return Boolean

 +----------------------------------------------------------

 */



function is_utf8($string)

{

	return preg_match('%^(?:

		 [\x09\x0A\x0D\x20-\x7E]            # ASCII

	   | [\xC2-\xDF][\x80-\xBF]             # non-overlong 2-byte

	   |  \xE0[\xA0-\xBF][\x80-\xBF]        # excluding overlongs

	   | [\xE1-\xEC\xEE\xEF][\x80-\xBF]{2}  # straight 3-byte

	   |  \xED[\x80-\x9F][\x80-\xBF]        # excluding surrogates

	   |  \xF0[\x90-\xBF][\x80-\xBF]{2}     # planes 1-3

	   | [\xF1-\xF3][\x80-\xBF]{3}          # planes 4-15

	   |  \xF4[\x80-\x8F][\x80-\xBF]{2}     # plane 16

   )*$%xs', $string);

}







/**

 * 过滤额不安全字符串，防止跨站脚本攻击。

 * 主要用户前台

 * @param  $string 要过滤的数据，可以是数据合字符串

 * @param  $allowedtags 忽略的标签 例如：'<a><p><br><hr><h1><h2><h3><h4><h5><h6><font><u><i><b><strong><div><span><ol><ul><li><img><table><tr><td><map>'

 * @param  $disabledattributes 不允许的标签属性值

 **/

function filter_xss($string, $allowedtags = '', $disabledattributes = array('onabort', 'onactivate', 'onafterprint',

																			'onafterupdate', 'onbeforeactivate', 'onbeforecopy',

																			'onbeforecut', 'onbeforedeactivate', 'onbeforeeditfocus',

																			'onbeforepaste', 'onbeforeprint', 'onbeforeunload',

																			'onbeforeupdate', 'onblur', 'onbounce',

																			'oncellchange', 'onchange', 'onclick',

																			'oncontextmenu', 'oncontrolselect', 'oncopy',

																			'oncut', 'ondataavaible', 'ondatasetchanged',

																			'ondatasetcomplete', 'ondblclick', 'ondeactivate',

																			'ondrag', 'ondragdrop', 'ondragend',

																			'ondragenter', 'ondragleave', 'ondragover',

																			'ondragstart', 'ondrop', 'onerror',

																			'onerrorupdate', 'onfilterupdate', 'onfinish',

																			'onfocus', 'onfocusin', 'onfocusout',

																			'onhelp', 'onkeydown', 'onkeypress',

																			'onkeyup', 'onlayoutcomplete', 'onload',

																			'onlosecapture', 'onmousedown', 'onmouseenter',

																			'onmouseleave', 'onmousemove', 'onmoveout',

																			'onmouseover', 'onmouseup', 'onmousewheel',

																			'onmove', 'onmoveend', 'onmovestart',

																			'onpaste', 'onpropertychange', 'onreadystatechange',

																			'onreset', 'onresize', 'onresizeend',

																			'onresizestart', 'onrowexit', 'onrowsdelete',

																			'onrowsinserted', 'onscroll', 'onselect',

																			'onselectionchange', 'onselectstart', 'onstart',

																			'onstop', 'onsubmit', 'onunload'))

{

	if(is_array($string)) {

		foreach($string as $key => $val)

			$string[$key] = filter_xss($val, $allowedtags);

	} else {

		$string = preg_replace('/\s('.implode('|', $disabledattributes).').*?([\s\>])/', '\\2', preg_replace('/<(.*?)>/ie', "'<'.preg_replace(array('/javascript:[^\"\']*/i', '/(".implode('|', $disabledattributes).")[ \\t\\n]*=[ \\t\\n]*[\"\'][^\"\']*[\"\']/i', '/\s+/'), array('', '', ' '), stripslashes('\\1')) . '>'", strip_tags($string, $allowedtags)));

	}

	return $string;

}





/**

 * 404错误页面

 * @param string $msg

 */

function h404($msg) {

	send_http_status(404);

	include TMPL_PATH . 'system/404.php';

	exit ();

}



/**

 * 替换请求中的字符串

 * @param unknown_type $string

 */

function safe_replace($string) {

	$string = str_replace('%20','',$string);

	$string = str_replace('%27','',$string);

	$string = str_replace('*','',$string);

	$string = str_replace('"','&quot;',$string);

	$string = str_replace("'",'',$string);

	$string = str_replace("\"",'',$string);

	$string = str_replace('//','',$string);

	$string = str_replace(';','',$string);

	$string = str_replace('<','&lt;',$string);

	$string = str_replace('>','&gt;',$string);

	$string = str_replace('(','',$string);

	$string = str_replace(')','',$string);

	$string = str_replace("{",'',$string);

	$string = str_replace('}','',$string);

	return $string;

}



/**

 * 从数组中删除空白的元素（包括只有空白字符的元素）

 *

 * @param array $arr

 * @param boolean $trim

 */

function array_remove_empty(& $arr, $trim = true) {

    foreach ($arr as $key => $value) {

        if (is_array($value)) {

            array_remove_empty($arr[$key]);

        } else {

            $value = trim($value);

            if ($value == '') {

                unset($arr[$key]);

            } elseif ($trim) {

                $arr[$key] = $value;

            }

        }

    }

}



/**

 * 分析当前的访问地址，查找出对应的栏目以及控制器

 * @param

 */

function parse_module () {

	$var_module = C('VAR_MODULE');

	$var_action = C('VAR_ACTION');

	//去掉  "/xxx/index.php/"

	$php_self = safe_replace($_SERVER['PHP_SELF']);

	$query_script = substr($php_self,strlen(__ROOT__) + 11);    

	//修改时间 2011-11-1 mark ，用于url带参数效果。

	//$query_script = substr($_SERVER['REQUEST_URI'],strlen(__ROOT__)+1);



	$query_script = str_replace('//','/',$query_script);

	$url_suffix = C('URL_HTML_SUFFIX');

	if (!empty($query_script) && $query_script!=('index'.$url_suffix) ) {

		if (strpos($query_script,'/')) {  //分析url取得栏目文件夹名称，根据文件夹文件查找所属分类

			$param = explode('/',$query_script);

		} else {

			$param[0] = $query_script;

		}

		array_remove_empty($param);

		$_MobileMenu = D ('Mobilemenu');



		$category_data = $_MobileMenu->field("`id`")->where("`catdir`='{$param[0]}.wap'")->find();

		array_shift($param);

		if (strpos($query_script,$url_suffix)) {

			if (__ROOT__ == '') { //根目录

				$request_file = substr($_SERVER['REQUEST_URI'] ,1);  //请求的文件地址

			} else {  //非根目录

				$request_file = substr($_SERVER['REQUEST_URI'],strlen(__ROOT__)+1);  //请求的文件地址

			}

		} else {

			$request_file = rtrim(substr($_SERVER['REQUEST_URI'],strlen(__ROOT__)+1),'/'). '/index' . $url_suffix;  //请求的文件地址

		}



		define('ID',$category_data['id'] ? $category_data['id'] : 0);

		$category_data = F ('wap_'.$category_data['id'].'.class','',INCLUDE_PATH.'wap/');



		if ($category_data['controller']) {

			$_POST[$var_module] = $category_data['controller'];

			$_GET[$var_module] = $category_data['controller']; 

		} else {  //栏目不存在，载入路由设置进行分析

			Dispatcher::dispatch();

		}

	} else {

		$_POST[$var_module] = 'Findex';

		$_GET[$var_module] = 'Findex';

		$request_file = 'index' . $url_suffix; //请求的文件地址 ，首页

	}

	$request_file = str_replace('//','/',$request_file);

	define('REQUEST_FILE',$request_file);

	return ;

}





/**

 * 分页函数

 * @param int $num 总记录数

 * @param int $curpage 当前页数

 * @param strint $mpurl  URL链接的URL表示  其中必须包含'_{page}'，放在.html之前  '{page}'是页码占位符

 * @param int $perpage 每页要显示的条数

 * @param boolean $goto 是否显示下拉跳转

 * @param array $config 链接说明文字

 */

function multi($num, $curr_page, $mpurl, $perpage = 10, $goto = true,

			   $config = array(

							//'first'=> '首页',

							'pre' => '上一页',

							'next' => '下一页',

							//'last' => '末页',

							//'goto' => '转到 第  %s 页',

							//'total' => '当前%d/%d页 共有 <em>%d</em> 条记录' 
								)) {

	$multipage = '';

	if ($num > $perpage) {

		$page = 10;

		$offset = 2;

		$pages = ceil ( $num / $perpage );

		$from = $curr_page - $offset;

		$to = $curr_page + $page - $offset - 1;

		if ($page > $pages) {

			$from = 1;

			$to = $pages;

		} else {

			if ($from < 1) {

				$to = $curr_page + 1 - $from;

				$from = 1;

				if (($to - $from) < $page && ($to - $from) < $pages) {

					$to = $page;

				}

			} elseif ($to > $pages) {

				$from = $curr_page - $pages + $to;

				$to = $pages;

				if (($to - $from) < $page && ($to - $from) < $pages) {

					$from = $pages - $page + 1;

				}

			}

		}

		//统计信息

		$multipage .= '<div class="pageList">';

		$multipage .= sprintf('<ul class="l">'.$config['total'].'</ul>',$curr_page,$pages,$num);

		$multipage .= '<ul class="r">';

		//首页

		if ($curr_page>1) { // disabled="disabled"

			if (strpos($mpurl,'_{page}')) {  //静态url

				$multipage .= '<li><a href="'. str_replace ( '_{page}', '', $mpurl ).'" >'.$config['first'].'</a></li>';

			} elseif (strpos($mpurl,'{page}')) {  //动态url  类似：/search/?page={page}&q=keyword

				$multipage .= '<li><a href="'. str_replace ( '{page}', 1, $mpurl ).'" >'.$config['first'].'</a></li>';

			}

		} else {  // disabled="disabled"

		    if (strpos($mpurl,'_{page}')) {  //静态url

				$multipage .= '<li><a href="'. str_replace ( '_{page}', '', $mpurl ).'" disabled="disabled">'.$config['first'].'</a></li>';

			} elseif (strpos($mpurl,'{page}')) {  //动态url  类似：/search/?page={page}&q=keyword

				$multipage .= '<li><a href="'. str_replace ( '{page}', 1, $mpurl ).'" disabled="disabled">'.$config['first'].'</a></li>';

			}

		}

		//上一页

		if ($curr_page>1 && $pages>=$curr_page) {
			if ($curr_page>2) {

				$multipage .= '<li><a href="'. str_replace ( '{page}', ($curr_page-1), $mpurl ).'" >'.$config['pre'].'</a></li>';

			} else { //上一页是第一页

				if (strpos($mpurl,'_{page}')) {

					$multipage .= '<li><a href="'. str_replace ( '_{page}', '', $mpurl ).'" >'.$config['pre'].'</a></li>';

				} elseif (strpos($mpurl,'{page}')) {

					$multipage .= '<li><a href="'. str_replace ( '{page}', ($curr_page-1), $mpurl ).'" >'.$config['pre'].'</a></li>';

				}

			}

		} else { // disabled="disabled"

			if (strpos($mpurl,'_{page}')) {

				//$multipage .= '<li><a href="'. str_replace ( '_{page}', '', $mpurl ).'" disabled="disabled">'.$config['pre'].'</a></li>';

			} elseif (strpos($mpurl,'{page}')) {

				//$multipage .= '<li><a href="'. str_replace ( '{page}', ($curr_page-1), $mpurl ).'" disabled="disabled">'.$config['pre'].'</a></li>';

			}

		}

		//数字页码
	/*

		for($i = $from; $i <= $to; $i ++) {

			if ($i != $curr_page) {

				if ($i == 1) { //首页不带页码

					if (false !== strpos($mpurl,'_{page}')) {  //静态url

						$multipage .= '<li><a href="'. str_replace ( '_{page}', '', $mpurl ).'" >1</a></li>';

					} elseif (false !== strpos($mpurl,'{page}')) {  //动态url  类似：/search/?page={page}&q=keyword

						$multipage .= '<li><a href="'. str_replace ( '{page}', 1, $mpurl ).'" >1</a></li>';

					}

				} else {

					$multipage .= '<li><a href="'. str_replace ( '{page}', $i, $mpurl ).'" >'.$i.'</a></li>';

				}

			} else {  //当前页   disabled="disabled"

				$multipage .= '<li><a href="javascript:;" class="selected"  disabled="disabled">'.$i.'</a></li>';

			}

		}*/

		//下一页

		if ($curr_page>0 && $curr_page<$pages) {

			$multipage .= '<li><a href="'. str_replace ( '{page}', ($curr_page+1), $mpurl ).'">'.$config['next'].'</a></li>';

		} else {

		    //$multipage .= '<li><a href="'. str_replace ( '{page}', $curr_page, $mpurl ).'" disabled="disabled">'.$config['next'].'</a></li>';

		}

		//末页

		if ($curr_page<$pages) {

			$multipage .= '<li><a href="'. str_replace ( '{page}', $pages, $mpurl ).'">'.$config['last'].'</a></li>';

		} else {  //disabled

		    $multipage .= '<li><a href="'. str_replace ( '{page}', $pages, $mpurl ).'" disabled="disabled">'.$config['last'].'</a></li>';

		}

		//goto下拉框

		if ($goto && $pages>1) {

			$go_html .= '<span class="mulit_goto">'.$config['goto'];

			$select = '<select onchange="window.location=this.value">';

			for ($i = 1; $i<=$pages;$i++) {

				if ($i>2) {

					$select .= '<option value="'.str_replace ( '{page}', $i, $mpurl ).'">'.$i.'</option>';

				} else {

					if (strpos($mpurl,'_{page}')) {  //静态url

						$select .= '<option value="'.str_replace ( '_{page}', '', $mpurl ).'">'.$i.'</option>';

					} elseif (strpos($mpurl,'{page}')) {  //动态url

						$select .= '<option value="'.str_replace ( '{page}', 1, $mpurl ).'">'.$i.'</option>';

					}

				}

			}

			$select .= '</select>';

			$go_html .= '</span>';

			$multipage .= sprintf($go_html,$select);

		}

		$multipage .= '</ul>';

		$multipage .= '</div>';

	}

	return $multipage;

}













