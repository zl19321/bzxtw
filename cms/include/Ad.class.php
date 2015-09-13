<?php
// +----------------------------------------------------------------------
// | FangFa Net [ http://www.fangfa.net ]
// +----------------------------------------------------------------------
// | File: Ad.class.php
// +----------------------------------------------------------------------
// | Date: 2010 09:52:44
// +----------------------------------------------------------------------
// | Author: fangfa <1364119331@qq.com>
// +----------------------------------------------------------------------
// | 文件描述: 根据广告ID生成广告代码
// +----------------------------------------------------------------------
class Ad {
	/**
	 * 广告设置项
	 *
	 * @var array
	 */
	private $_setting = '';


	/**
	 * 广告类型
	 *
	 * @var string
	 */
	private $_type = '';

	/**
	 * 生成的AD代码
	 *
	 * @var string
	 */
	private $_code = '';

	/**
	 * 构造函数，初始化话AdModel对象实例
	 *
	 */
	function __construct($type,$setting) {
		$this->_type = $type;
		$this->_setting = $setting;
		$this->_code = '';
	}

	/**
	 * 获取广告代码
	 *
	 * @param string $type
	 * @param array $setting
	 * @return string
	 */
	public function get($type,$setting) {
		$type && $this->_type = $type;
		$setting && $this->_setting = $setting;
		if (method_exists($this,$this->_type)) {
			$this->{$this->_type}($this->_setting);
		}
		return $this->_code;
	}

	/**
	 * 生成数字banner代码
	 *
	 * @param string $setting
	 */
	private function banner($setting) {
		$files = array();
		$links = array();
		if (!empty($setting['bimage'])) {
			$upload_dir = C ('UPLOAD_DIR') ;
			foreach ($setting['bimage'] as $k=>$v) {
				if (!empty($v)) {
					if (false === strpos($v,"http://")) {
						$files[] = __ROOT__.'/'.$upload_dir.$v;
					} else {
						$files[] = $v;
					}
				}
			}
			$files = implode('|',$files);
		}
		if (!empty($setting['blink'])) {
			foreach ($setting['blink'] as $k=>$v) {
				if (!empty($v)) $links[] = $v;
			}
			$links = implode('|',$links);
		}
		$html = "
		<script type=\"text/javascript\">
		<!--
		var swf_width={$setting['width']};
		var swf_height={$setting['height']};
		var config='5|0xffffff|0x666666|80|0xffffff|0xB4D333|0x000000';
		//-- config 参数设置 -- 自动播放时间(秒)|文字颜色|文字背景色|文字背景透明度|按键数字颜色|当前按键颜色|普通按键色彩 --
		var files='',links='', texts='';
		files = '{$files}';
		links = '{$links}';
		/*texts = '标题|标题|标题'*/
		document.write('<object classid=\"clsid:d27cdb6e-ae6d-11cf-96b8-444553540000\" codebase=\"http://fpdownload.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,0,0\" width=\"'+ swf_width +'\" height=\"'+ swf_height +'\">');
		document.write('<param name=\"movie\" value=\"".__ROOT__."/public/ads/Dinfocus.swf\" />');
		document.write('<param name=\"quality\" value=\"high\" />');
		document.write('<param name=\"menu\" value=\"false\" />');
		document.write('<param name=wmode value=\"opaque\" />');
		document.write('<param name=\"FlashVars\" value=\"config='+config+'&bcastr_flie='+files+'&bcastr_link='+links+'&bcastr_title='+texts+'\" />');
		document.write('<embed src=\"".__ROOT__."/public/ads/Dinfocus.swf\" wmode=\"opaque\" FlashVars=\"config='+config+'&bcastr_flie='+files+'&bcastr_link='+links+'&bcastr_title='+texts+'& menu=\"false\" quality=\"high\" width=\"'+ swf_width +'\" height=\"'+ swf_height +'\" type=\"application/x-shockwave-flash\" pluginspage=\"http://www.macromedia.com/go/getflashplayer\" />');
		document.write('</object>');
		//-->
		</script>
		";
		$this->_code = $html;
	}


	/**
	 * 生成数字banner代码
	 *
	 * @param string $setting
	 */
	private function banner_t($setting) {
		$files = array();
		$links = array();
		if (!empty($setting['bimage'])) {

			foreach ($setting['bimage'] as $k=>$v) {

			}
		}
		$html = "";
		if (empty($GLOBALS['loadFiles']['cycle.all'])) {
		    $loadFiles = '<script type="text/javascript" src="'.WEB_PUBLIC_PATH. '/js/jquery-1.4.4.min.js"></script><script type="text/javascript" src="'.WEB_PUBLIC_PATH. '/js/jquery.cycle.all.js"></script>';
		    $html .= $loadFiles;
		    $GLOBALS['loadFiles']['cycle.all'] = $loadFiles;
		}
//		dump($setting);
		if (is_array($setting['bimage']) && !empty($setting['bimage'])) {
		    $upload_dir = C ('UPLOAD_DIR') ;
		    $bhtml = '';
		    $shtml = '';
		    foreach ($setting['bimage'] as $k=>$v) {
		        if (!empty($v)) {
		            //大图
					if (substr($v,0,7)!="http://") {
						$bhtml .= '<li><a href="'.$setting['blink'][$k].'" title="'.$setting['btext'][$k].'" target="_blank"><img src="'.__ROOT__.'/'.$upload_dir.$v.'"></a></li>';
					} else {
						$bhtml .= '<li><a href="'.$setting['blink'][$k].'" title="'.$setting['btext'][$k].'" target="_blank"><img src="'.$v.'"></a></li>';
					}
					//小图
					if (substr($setting['simage'][$k],0,7)!="http://") {
						$shtml .= '<li><a href="javascript:;" title="'.$setting['btext'][$k].'" target="_blank"><img src="'.__ROOT__.'/'.$upload_dir.$setting['simage'][$k].'"></a></li>';
					} else {
						$shtml .= '<li><a href="javascript:;" title="'.$setting['btext'][$k].'" target="_blank"><img src="'.$setting['simage'][$k].'"></a></li>';
					}
				}
		    }
		}
		$html .= '
		<div class="banner">
		<ul id="bImage">
		  '.$bhtml.'
	    </ul>
		<ul id="sImage">
		  '.$shtml.'
		</ul>
		</div>
		';
		$this->_code = $html;
	}

	/**
	 * 生成Flash广告代码
	 *
	 * @param unknown_type $setting
	 */
	private function flash($setting) {
		$file = &$setting['flashurl'];
		if (false === strpos($file,'http://')) {
			$file = __ROOT__.'/'.C ('UPLOAD_DIR').$file;
		}
		$html = "
		<object classid=\"clsid:d27cdb6e-ae6d-11cf-96b8-444553540000\" codebase=\"http://fpdownload.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,0,0\" width=\"{$setting['flashwidth']}\" height=\"{$setting['flashheight']}\">
		<param name=\"movie\" value=\"{$file}\" />
		<param name=\"quality\" value=\"high\" />
		<param name=\"wmode\" value=\"opaque\" />
		<embed src=\"{$file}\" wmode=\"opaque\" quality=\"high\" width=\"{$setting['flashwidth']}\" height=\"{$setting['flashheight']}\" type=\"application/x-shockwave-flash\" pluginspage=\"http://www.macromedia.com/go/getflashplayer\" />
		</object>
        ";
		$this->_code = $html;
	}

	/**
	 * 生成文本广告代码
	 *
	 * @param unknown_type $setting
	 */
	private function html($setting) {
		$this->_code = $setting['html'];
	}

	/**
	 * 生成图片广告代码
	 *
	 * @param array $setting
	 */
	private function image($setting) {
		if (false === strpos($setting['image'],'http://')) {
			$setting['image'] = __ROOT__.'/'.C ('UPLOAD_DIR').$setting['image'];
		} else {
			$setting['image'] = $setting['image'];
		}
		$this->_code = '';
		if (!empty($setting['link'])) {
			$this->_code .= '<a href="'.$setting['link'].'" target="_blank">';
		}
		$this->_code .= '<img ';
		if($setting['imagewidth']>0) $this->_code .= ' width="'.$setting['imagewidth'].'" ';
		if($setting['imageheight']>0) $this->_code .= ' height="'.$setting['imageheight'].'" ';
		$this->_code .= ' src="'.$setting['image'].'" alt="'.$setting['alt'].'" />';
		if (!empty($setting['link'])) {
			$this->_code .= '</a>';
		}
	}

	/**
	 * 生成文字链接广告代码
	 *
	 * @param array $setting
	 */
	private function link($setting) {
		$this->_code = '<a href="'.$setting['url'].'" target="_blank">'.$setting['text'].'</a>';
	}

}