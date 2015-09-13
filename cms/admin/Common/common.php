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
// | 文件描述: 后台常用函数
// +----------------------------------------------------------------------

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
 * 包含后台模板公共头部
 */
function theader (){
	include(TMPL_PATH.C('DEFAULT_THEME').'/public/header.php');
}

/**
 * 包含后台模板公共底部
 */
function tfooter() {
	include(TMPL_PATH.C('DEFAULT_THEME').'/public/footer.php');
}



/**
 * 递归获取文件夹下的所有文件,不包括目录
 */
function getFiles($dir)
{
	if (!is_dir($dir)) {
		return false;
	}
	
	static $files ;
	$d = dir($dir);
	while (false !== ($entry = $d->read())) {
		if ($entry != '.' && $entry != '..') {
			if (filetype($dir . '/' . $entry) == 'file') {
				$files[] = $dir . '/' . $entry;
			} elseif (filetype($dir . '/' . $entry) == 'dir') {
				getFiles($dir . '/' . $entry);
			}
		}
	}	
	return $files;
}

/**
 * 内容发布 SEO ping挂件
 *	$page_uri 需要检查更新的页面URI
 *  $tags  标签
 */
function seo_ping($content_data)
{
	$content_data = unserialize($content_data);
	$cfg_setting = F('config.cache', '', ALL_CACHE_PATH);
	
	/**
	 * server ping服务器地址
	 */
	if (empty($cfg_setting['PING_SITES']) || @$cfg_setting['AUTO_PING'] != 1 || empty($content_data['url'])) {
		return false;
	}
	$ping_sites = preg_replace("|(\s)+|", '$1', $cfg_setting['PING_SITES']);
	$ping_sites = trim($ping_sites);
	
	$category = F('category_'.$content_data['catid'], '', DATA_CACHE_PATH);
	$sitename = $cfg_setting['SEOTITLE'];
	$siteurl = 'http://' . $_SERVER['HTTP_HOST'] . '/' . __ROOT__;
	$rssurl = $siteurl . 'rss.xml';
	$content_url = rtrim($siteurl, '/\\') . '/' . trim($category['url'], '/\\') . '/' . $content_data['url'];
	$tags = str_replace(array(',', '，'), '|', (key_exists('seokeywords', $content_data) ? str_replace('{skeywords}', $cfg_setting['SEOKEYWORDS'], $content_data['seokeywords']) : $cfg_setting['SEOKEYWORDS']));

	if ( !empty($ping_sites) ) {
		Vendor('class-IXR');
		$ping_sites = explode("\n", $ping_sites);
		foreach ( (array) $ping_sites as $site ) {
			$client = new IXR_Client($site);
			$client->timeout = 3;
			$client->useragent .= ' -- Fangfa/1.0';
			$client->debug = false;
			
			//参数说明:调用方法名,博客名称,博客网站地址,博客网站的订阅地址,需要检查更新的页面URL,需要检查更新的页面URL
			if ( !$client->query('weblogUpdates.extendedPing', $sitename, $siteurl, $rssurl, $content_url, $tags) ) // then try a normal ping
				$client->query('weblogUpdates.ping', $sitename, $siteurl);
		}
	}
	
	return true;
}

/**
 * 删除内容管理生成的静态文件
 */
function clear_html($content_data)
{
	$content_data = unserialize($content_data);
	$category = F('category_'.$content_data['catid'], '', DATA_CACHE_PATH);
	
	$category_base_path = FANGFACMS_ROOT . trim($category['url'], '/\\') . '/';
	$content_path = $category_base_path . $content_data['url'];
	
	if (is_dir($category_base_path)) {
		//删除当前内容栏目静态文件
		$d = dir($category_base_path);
		while (false !== ($entry = $d->read())) {  //循环删除栏目主页文件，包括栏目分页文件
			if (preg_match('/^index(_\d*)?\.'.substr(C('TMPL_TEMPLATE_SUFFIX'), 1).'$/', $entry)) {
				@unlink($category_base_path . $entry);
			}
		}
		
		//删除当前内容详细页
		if (is_file($content_path)) {
			$path_info = pathinfo($content_path);
			$d = dir($path_info['dirname']);
			while (false !== ($entry = $d->read())) {  //循环删除栏目主页文件，包括栏目分页文件
				if (preg_match('/^' . $path_info['filename'] . '(_\d*)?\.'.substr(C('TMPL_TEMPLATE_SUFFIX'), 1).'$/', $entry)) {
					@unlink($path_info['dirname'] . '/' . $entry);
				}
			}
		}
	}
}

/**
 +----------------------------------------------------------
 * 生成二维码图片
 +----------------------------------------------------------
 * @param string $content 二维码内容
 +----------------------------------------------------------
 * @param int  $size  图片生成尺寸
 +----------------------------------------------------------
 * @return string
 +----------------------------------------------------------
 */
function generate_brcode($content, $size = 4){
    if(empty($content)) return false;
    
	require_once FANGFACMS_ROOT."admin/phpqrcode/qrlib.php";
    $errorCorrectionLevel = 'L'; //纠错
    $matrixPointSize      = min(max((int)$size, 1), 10); //图片尺寸大小
    static $rrand         = 0; $rrand++;
	$save_path            = FANGFACMS_ROOT . C ( 'UPLOAD_DIR' );
	$png_temp_dir         = $save_path.'brcode/'.date ('Y/m');

	if (! is_dir ( $png_temp_dir ))  mk_dir ( $png_temp_dir );
    $file_name = $png_temp_dir."/".times().$rrand.$matrixPointSize.'.png'; //生成的二维码图片路径
	
	QRcode::png($content, $file_name , $errorCorrectionLevel, $matrixPointSize, 2);   
	return 	$saveName = str_replace($save_path , '', $file_name);//二维码存放路径
}