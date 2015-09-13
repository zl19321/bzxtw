<?php
// +----------------------------------------------------------------------
// | FangFa Net [ http://www.fangfa.net ]
// +----------------------------------------------------------------------
// | File: Http.class.php
// +----------------------------------------------------------------------
// | Date: 2010 11:57:54
// +----------------------------------------------------------------------
// | Author: fangfa <1364119331@qq.com>
// +----------------------------------------------------------------------
// | 文件描述: Http工具类，提供了一系列的Http方法
// +----------------------------------------------------------------------
class Http {//类定义开始
	/**
	 * 发送方式
	 * POST or GET
	 * @var string
	 */
	static protected $method = 'GET';
	
	/**
	 * 发送的数据部分的cookie信息
	 *
	 * @var unknown_type
	 */
	static protected $cookie = '';
	
	/**
	 * 发送数据
	 *
	 * @var string
	 */
	static protected $post = '';
	
	/**
	 * 头信息
	 *
	 * @var string
	 */
	static protected $header = '';
	
	/**
	 * ContentType
	 *
	 * @var int
	 */
	static protected $ContentType;
	
	/**
	 * 错误代码
	 *
	 * @var int
	 */
	static protected $errno = 0;
	
	/**
	 * 错误字符串
	 *
	 * @var string
	 */
	static protected $errstr = '';
	
	/**
	 * 接收数据
	 *
	 * @var string
	 */
	static public $data;
		
	/**
	 * 向远程POST数据
	 * 
	 **/ 
	static public function post($url, $data = array(), $referer = '', $limit = 0, $timeout = 30){
		self::$method = 'POST';
		self::$ContentType = "Content-Type: application/x-www-form-urlencoded\r\n";
		if($data) {
			$post = '';
			foreach($data as $k=>$v) {
				$post .= $k.'='.rawurlencode($v).'&';
			}
			self::$post .= substr($post, 0, -1);
		}
		return self::request($url, $referer, $limit, $timeout);
	}

	/**
	 * 向远程GET数据
	 * 
	 **/
	static public function get($url, $referer = '', $limit = 0, $timeout = 30)	{
		self::$method = 'GET';
		return self::request($url, $referer, $limit, $timeout);
	}
	
	/**
	 * 想远程地址发送请求
	 * 
	 **/ 
	static protected function request($url, $referer = '', $limit = 0, $timeout = 30) {
		$matches = parse_url($url);		
		$host = $matches['host'];
		$path = $matches['path'] ? $matches['path'].($matches['query'] ? '?'.$matches['query'] : '') : '/';
		$port = $matches['port'] ? $matches['port'] : 80;
		$out = self::$method." $path HTTP/1.1\r\n";
		$out .= "Accept: */*\r\n";
		$out .= "Referer: $referer\r\n";
		$out .= "Accept-Language: zh-cn\r\n";
		$out .= "User-Agent: ".$_SERVER['HTTP_USER_AGENT']."\r\n";
		$out .= "Host: $host\r\n";
		if(self::$cookie) $out .= "Cookie: ".self::$cookie."\r\n";
		if(self::$method == 'POST')	{
			$out .= self::$ContentType;
			$out .= "Content-Length: ".strlen(self::$post)."\r\n";
			$out .= "Cache-Control: no-cache\r\n";
			$out .= "Connection: Close\r\n\r\n";
			$out .= self::$post;
		} else {
			$out .= "Connection: Close\r\n\r\n";
		}
		if($timeout > ini_get('max_execution_time')) @set_time_limit($timeout);		
		$fp = @fsockopen($host, $port, $errno, $errstr, $timeout);
		if(!$fp) {
			self::$errno = $errno;
			self::$errstr = $errstr;
			return false;
		} else {
			stream_set_blocking($fp, $block);
			stream_set_timeout($fp, $timeout);
			fwrite($fp, $out);
			self::$data = '';
			$status = stream_get_meta_data($fp);
			if(!$status['timed_out']) {
				$maxsize = min($limit, 1024000);
				if($maxsize == 0) $maxsize = 1024000;
				$start = false;
				while(!feof($fp)) {
					if($start) {
						$line = fread($fp, $maxsize);
						if(strlen(self::$data) > $maxsize) break;
						self::$data .= $line;
					} else {
						$line = fgets($fp);
						self::$header .= $line;
						if($line == "\r\n" || $line == "\n") $start = true;
					}
				}
			}
			fclose($fp);
			if (false !== self::is_ok()) {
				return self::$data;
			} else {
				return false;
			}
		}
	}
	
	/**
     +----------------------------------------------------------
     * 显示HTTP Header 信息
     +----------------------------------------------------------
     * @return string
     +----------------------------------------------------------
     */
	static public function get_header_info($header='',$echo=true) {
		ob_start();
		$headers   = getallheaders();
		if(!empty($header)) {
			$info = $headers[$header];
			echo($header.':'.$info."\n"); ;
		}else {
			foreach($headers as $key=>$val) {
				echo("$key:$val\n");
			}
		}
		$output = ob_get_clean();
		if ($echo) {
			echo (nl2br($output));
		}else {
			return $output;
		}

	}
	
	static public function get_header() {
		return self::$header;
	}

	static public function get_status() {
		preg_match("|^HTTP/1.1 ([0-9]{3}) (.*)|", self::$header, $m);
		return array($m[1], $m[2]);
	}

	function get_mime($file) {
		$ext = fileext($file);
		if($ext == '') return '';
		$mime_types =  array (
						  'acx' => 'application/internet-property-stream',
						  'ai' => 'application/postscript',
						  'aif' => 'audio/x-aiff',
						  'aifc' => 'audio/x-aiff',
						  'aiff' => 'audio/x-aiff',
						  'asp' => 'text/plain',
						  'aspx' => 'text/plain',
						  'asf' => 'video/x-ms-asf',
						  'asr' => 'video/x-ms-asf',
						  'asx' => 'video/x-ms-asf',
						  'au' => 'audio/basic',
						  'avi' => 'video/x-msvideo',
						  'axs' => 'application/olescript',
						  'bas' => 'text/plain',
						  'bcpio' => 'application/x-bcpio',
						  'bin' => 'application/octet-stream',
						  'bmp' => 'image/bmp',
						  'c' => 'text/plain',
						  'cat' => 'application/vnd.ms-pkiseccat',
						  'cdf' => 'application/x-cdf',
						  'cer' => 'application/x-x509-ca-cert',
						  'class' => 'application/octet-stream',
						  'clp' => 'application/x-msclip',
						  'cmx' => 'image/x-cmx',
						  'cod' => 'image/cis-cod',
						  'cpio' => 'application/x-cpio',
						  'crd' => 'application/x-mscardfile',
						  'crl' => 'application/pkix-crl',
						  'crt' => 'application/x-x509-ca-cert',
						  'csh' => 'application/x-csh',
						  'css' => 'text/css',
						  'dcr' => 'application/x-director',
						  'der' => 'application/x-x509-ca-cert',
						  'dir' => 'application/x-director',
						  'dll' => 'application/x-msdownload',
						  'dms' => 'application/octet-stream',
						  'doc' => 'application/msword',
						  'dot' => 'application/msword',
						  'dvi' => 'application/x-dvi',
						  'dxr' => 'application/x-director',
						  'eps' => 'application/postscript',
						  'etx' => 'text/x-setext',
						  'evy' => 'application/envoy',
						  'exe' => 'application/octet-stream',
						  'fif' => 'application/fractals',
						  'flr' => 'x-world/x-vrml',
						  'flv' => 'video/x-flv',
						  'gif' => 'image/gif',
						  'gtar' => 'application/x-gtar',
						  'gz' => 'application/x-gzip',
						  'h' => 'text/plain',
						  'hdf' => 'application/x-hdf',
						  'hlp' => 'application/winhlp',
						  'hqx' => 'application/mac-binhex40',
						  'hta' => 'application/hta',
						  'htc' => 'text/x-component',
						  'htm' => 'text/html',
						  'html' => 'text/html',
						  'htt' => 'text/webviewhtml',
						  'ico' => 'image/x-icon',
						  'ief' => 'image/ief',
						  'iii' => 'application/x-iphone',
						  'ins' => 'application/x-internet-signup',
						  'isp' => 'application/x-internet-signup',
						  'jfif' => 'image/pipeg',
						  'jpe' => 'image/jpeg',
						  'jpeg' => 'image/jpeg',
						  'jpg' => 'image/jpeg',
						  'js' => 'application/x-javascript',
						  'latex' => 'application/x-latex',
						  'lha' => 'application/octet-stream',
						  'lsf' => 'video/x-la-asf',
						  'lsx' => 'video/x-la-asf',
						  'lzh' => 'application/octet-stream',
						  'm13' => 'application/x-msmediaview',
						  'm14' => 'application/x-msmediaview',
						  'm3u' => 'audio/x-mpegurl',
						  'man' => 'application/x-troff-man',
						  'mdb' => 'application/x-msaccess',
						  'me' => 'application/x-troff-me',
						  'mht' => 'message/rfc822',
						  'mhtml' => 'message/rfc822',
						  'mid' => 'audio/mid',
						  'mny' => 'application/x-msmoney',
						  'mov' => 'video/quicktime',
						  'movie' => 'video/x-sgi-movie',
						  'mp2' => 'video/mpeg',
						  'mp3' => 'audio/mpeg',
						  'mpa' => 'video/mpeg',
						  'mpe' => 'video/mpeg',
						  'mpeg' => 'video/mpeg',
						  'mpg' => 'video/mpeg',
						  'mpp' => 'application/vnd.ms-project',
						  'mpv2' => 'video/mpeg',
						  'ms' => 'application/x-troff-ms',
						  'mvb' => 'application/x-msmediaview',
						  'nws' => 'message/rfc822',
						  'oda' => 'application/oda',
						  'p10' => 'application/pkcs10',
						  'p12' => 'application/x-pkcs12',
						  'p7b' => 'application/x-pkcs7-certificates',
						  'p7c' => 'application/x-pkcs7-mime',
						  'p7m' => 'application/x-pkcs7-mime',
						  'p7r' => 'application/x-pkcs7-certreqresp',
						  'p7s' => 'application/x-pkcs7-signature',
						  'pbm' => 'image/x-portable-bitmap',
						  'pdf' => 'application/pdf',
						  'pfx' => 'application/x-pkcs12',
						  'pgm' => 'image/x-portable-graymap',
						  'php' => 'text/plain',
						  'pko' => 'application/ynd.ms-pkipko',
						  'pma' => 'application/x-perfmon',
						  'pmc' => 'application/x-perfmon',
						  'pml' => 'application/x-perfmon',
						  'pmr' => 'application/x-perfmon',
						  'pmw' => 'application/x-perfmon',
						  'png' => 'image/png',
						  'pnm' => 'image/x-portable-anymap',
						  'pot,' => 'application/vnd.ms-powerpoint',
						  'ppm' => 'image/x-portable-pixmap',
						  'pps' => 'application/vnd.ms-powerpoint',
						  'ppt' => 'application/vnd.ms-powerpoint',
						  'prf' => 'application/pics-rules',
						  'ps' => 'application/postscript',
						  'pub' => 'application/x-mspublisher',
						  'qt' => 'video/quicktime',
						  'ra' => 'audio/x-pn-realaudio',
						  'ram' => 'audio/x-pn-realaudio',
						  'ras' => 'image/x-cmu-raster',
						  'rgb' => 'image/x-rgb',
						  'rmi' => 'audio/mid',
						  'roff' => 'application/x-troff',
						  'rtf' => 'application/rtf',
						  'rtx' => 'text/richtext',
						  'scd' => 'application/x-msschedule',
						  'sct' => 'text/scriptlet',
						  'setpay' => 'application/set-payment-initiation',
						  'setreg' => 'application/set-registration-initiation',
						  'sh' => 'application/x-sh',
						  'shar' => 'application/x-shar',
						  'sit' => 'application/x-stuffit',
						  'snd' => 'audio/basic',
						  'spc' => 'application/x-pkcs7-certificates',
						  'spl' => 'application/futuresplash',
						  'src' => 'application/x-wais-source',
						  'sst' => 'application/vnd.ms-pkicertstore',
						  'stl' => 'application/vnd.ms-pkistl',
						  'stm' => 'text/html',
						  'svg' => 'image/svg+xml',
						  'sv4cpio' => 'application/x-sv4cpio',
						  'sv4crc' => 'application/x-sv4crc',
						  'swf' => 'application/x-shockwave-flash',
						  't' => 'application/x-troff',
						  'tar' => 'application/x-tar',
						  'tcl' => 'application/x-tcl',
						  'tex' => 'application/x-tex',
						  'texi' => 'application/x-texinfo',
						  'texinfo' => 'application/x-texinfo',
						  'tgz' => 'application/x-compressed',
						  'tif' => 'image/tiff',
						  'tiff' => 'image/tiff',
						  'tr' => 'application/x-troff',
						  'trm' => 'application/x-msterminal',
						  'tsv' => 'text/tab-separated-values',
						  'txt' => 'text/plain',
						  'uls' => 'text/iuls',
						  'ustar' => 'application/x-ustar',
						  'vcf' => 'text/x-vcard',
						  'vrml' => 'x-world/x-vrml',
						  'wav' => 'audio/x-wav',
						  'wcm' => 'application/vnd.ms-works',
						  'wdb' => 'application/vnd.ms-works',
						  'wks' => 'application/vnd.ms-works',
						  'wmf' => 'application/x-msmetafile',
						  'wmv' => 'video/x-ms-wmv',
						  'wps' => 'application/vnd.ms-works',
						  'wri' => 'application/x-mswrite',
						  'wrl' => 'x-world/x-vrml',
						  'wrz' => 'x-world/x-vrml',
						  'xaf' => 'x-world/x-vrml',
						  'xbm' => 'image/x-xbitmap',
						  'xla' => 'application/vnd.ms-excel',
						  'xlc' => 'application/vnd.ms-excel',
						  'xlm' => 'application/vnd.ms-excel',
						  'xls' => 'application/vnd.ms-excel',
						  'xlt' => 'application/vnd.ms-excel',
						  'xlw' => 'application/vnd.ms-excel',
						  'xof' => 'x-world/x-vrml',
						  'xpm' => 'image/x-xpixmap',
						  'xwd' => 'image/x-xwindowdump',
						  'z' => 'application/x-compress',
						  'zip' => 'application/zip',
						);
		return isset($mime_types[$ext]) ? $mime_types[$ext] : '';
	}

	static public function is_ok() {
		$status = self::get_status();
		if(intval($status[0]) != 200)
		{
			self::$errno = $status[0];
			self::$errstr = $status[1];
			return false;
		}
		return true;
	}

	static public function errno() {
		return self::$errno;
	}

	static public function errmsg() {
		return self::$errstr;
	}
	
	/**
     +----------------------------------------------------------
     * 采集远程文件
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param string $remote 远程文件名
     * @param string $local 本地保存文件名
     +----------------------------------------------------------
     * @return mixed
     +----------------------------------------------------------
     */
	static public function curl_download($remote,$local) {
		$cp = curl_init($remote);
		$fp = fopen($local,"w");
		curl_setopt($cp, CURLOPT_FILE, $fp);
		curl_setopt($cp, CURLOPT_HEADER, 0);
		curl_exec($cp);
		curl_close($cp);
		fclose($fp);
	}
	
	/**
     +----------------------------------------------------------
     * 下载文件
     * 可以指定下载显示的文件名，并自动发送相应的Header信息
     * 如果指定了content参数，则下载该参数的内容
     +----------------------------------------------------------
     * @static
     * @access public
     +----------------------------------------------------------
     * @param string $filename 下载文件名
     * @param string $showname 下载显示的文件名
     * @param string $content  下载的内容
     * @param integer $expire  下载内容浏览器缓存时间
     +----------------------------------------------------------
     * @return void
     +----------------------------------------------------------
     * @throws ThinkExecption
     +----------------------------------------------------------
     */
	static public function download ($filename, $showname='',$content='',$expire=180) {
		if(is_file($filename)) {
			$length = filesize($filename);
		} elseif ($content != '') {
			$length = strlen($content);
		} else {
			throw_exception(L('下载文件不存在！'));
		}
		if(empty($showname)) {
			$showname = $filename;
		}
		$showname = basename($showname);
		if(!empty($filename)) {
			$type = mime_content_type($filename);
		}else{
			$type	 =	 "application/octet-stream";
		}
		//发送Http Header信息 开始下载
		header("Pragma: public");
		header("Cache-control: max-age=".$expire);
		//header('Cache-Control: no-store, no-cache, must-revalidate');
		header("Expires: " . gmdate("D, d M Y H:i:s",time()+$expire) . "GMT");
		header("Last-Modified: " . gmdate("D, d M Y H:i:s",time()) . "GMT");
		header("Content-Disposition: attachment; filename=".$showname);
		header("Content-Length: ".$length);
		header("Content-type: ".$type);
		header('Content-Encoding: none');
		header("Content-Transfer-Encoding: binary" );
		if($content == '' ) {
			readfile($filename);
		}else {
			echo($content);
		}
		exit();
	}

	

	/**
     * HTTP Protocol defined status codes
     * @param int $num
     */
	static function send_http_status($code) {
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
		302 => 'Found',  // 1.1
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
}//类定义结束
if( !function_exists ('mime_content_type')) {
    /**
     +----------------------------------------------------------
     * 获取文件的mime_content类型
     +----------------------------------------------------------
     * @return string
     +----------------------------------------------------------
     */
    function mime_content_type($filename)
    {
       static $contentType = array(
			'ai'	=> 'application/postscript',
				'aif'	=> 'audio/x-aiff',
				'aifc'	=> 'audio/x-aiff',
				'aiff'	=> 'audio/x-aiff',
				'asc'	=> 'application/pgp', //changed by skwashd - was text/plain
				'asf'	=> 'video/x-ms-asf',
				'asx'	=> 'video/x-ms-asf',
				'au'	=> 'audio/basic',
				'avi'	=> 'video/x-msvideo',
				'bcpio'	=> 'application/x-bcpio',
				'bin'	=> 'application/octet-stream',
				'bmp'	=> 'image/bmp',
				'c'	=> 'text/plain', // or 'text/x-csrc', //added by skwashd
				'cc'	=> 'text/plain', // or 'text/x-c++src', //added by skwashd
				'cs'	=> 'text/plain', //added by skwashd - for C# src
				'cpp'	=> 'text/x-c++src', //added by skwashd
				'cxx'	=> 'text/x-c++src', //added by skwashd
				'cdf'	=> 'application/x-netcdf',
				'class'	=> 'application/octet-stream',//secure but application/java-class is correct
				'com'	=> 'application/octet-stream',//added by skwashd
				'cpio'	=> 'application/x-cpio',
				'cpt'	=> 'application/mac-compactpro',
				'csh'	=> 'application/x-csh',
				'css'	=> 'text/css',
				'csv'	=> 'text/comma-separated-values',//added by skwashd
				'dcr'	=> 'application/x-director',
				'diff'	=> 'text/diff',
				'dir'	=> 'application/x-director',
				'dll'	=> 'application/octet-stream',
				'dms'	=> 'application/octet-stream',
				'doc'	=> 'application/msword',
				'dot'	=> 'application/msword',//added by skwashd
				'dvi'	=> 'application/x-dvi',
				'dxr'	=> 'application/x-director',
				'eps'	=> 'application/postscript',
				'etx'	=> 'text/x-setext',
				'exe'	=> 'application/octet-stream',
				'ez'	=> 'application/andrew-inset',
				'gif'	=> 'image/gif',
				'gtar'	=> 'application/x-gtar',
				'gz'	=> 'application/x-gzip',
				'h'	=> 'text/plain', // or 'text/x-chdr',//added by skwashd
				'h++'	=> 'text/plain', // or 'text/x-c++hdr', //added by skwashd
				'hh'	=> 'text/plain', // or 'text/x-c++hdr', //added by skwashd
				'hpp'	=> 'text/plain', // or 'text/x-c++hdr', //added by skwashd
				'hxx'	=> 'text/plain', // or 'text/x-c++hdr', //added by skwashd
				'hdf'	=> 'application/x-hdf',
				'hqx'	=> 'application/mac-binhex40',
				'htm'	=> 'text/html',
				'html'	=> 'text/html',
				'ice'	=> 'x-conference/x-cooltalk',
				'ics'	=> 'text/calendar',
				'ief'	=> 'image/ief',
				'ifb'	=> 'text/calendar',
				'iges'	=> 'model/iges',
				'igs'	=> 'model/iges',
				'jar'	=> 'application/x-jar', //added by skwashd - alternative mime type
				'java'	=> 'text/x-java-source', //added by skwashd
				'jpe'	=> 'image/jpeg',
				'jpeg'	=> 'image/jpeg',
				'jpg'	=> 'image/jpeg',
				'js'	=> 'application/x-javascript',
				'kar'	=> 'audio/midi',
				'latex'	=> 'application/x-latex',
				'lha'	=> 'application/octet-stream',
				'log'	=> 'text/plain',
				'lzh'	=> 'application/octet-stream',
				'm3u'	=> 'audio/x-mpegurl',
				'man'	=> 'application/x-troff-man',
				'me'	=> 'application/x-troff-me',
				'mesh'	=> 'model/mesh',
				'mid'	=> 'audio/midi',
				'midi'	=> 'audio/midi',
				'mif'	=> 'application/vnd.mif',
				'mov'	=> 'video/quicktime',
				'movie'	=> 'video/x-sgi-movie',
				'mp2'	=> 'audio/mpeg',
				'mp3'	=> 'audio/mpeg',
				'mpe'	=> 'video/mpeg',
				'mpeg'	=> 'video/mpeg',
				'mpg'	=> 'video/mpeg',
				'mpga'	=> 'audio/mpeg',
				'ms'	=> 'application/x-troff-ms',
				'msh'	=> 'model/mesh',
				'mxu'	=> 'video/vnd.mpegurl',
				'nc'	=> 'application/x-netcdf',
				'oda'	=> 'application/oda',
				'patch'	=> 'text/diff',
				'pbm'	=> 'image/x-portable-bitmap',
				'pdb'	=> 'chemical/x-pdb',
				'pdf'	=> 'application/pdf',
				'pgm'	=> 'image/x-portable-graymap',
				'pgn'	=> 'application/x-chess-pgn',
				'pgp'	=> 'application/pgp',//added by skwashd
				'php'	=> 'application/x-httpd-php',
				'php3'	=> 'application/x-httpd-php3',
				'pl'	=> 'application/x-perl',
				'pm'	=> 'application/x-perl',
				'png'	=> 'image/png',
				'pnm'	=> 'image/x-portable-anymap',
				'po'	=> 'text/plain',
				'ppm'	=> 'image/x-portable-pixmap',
				'ppt'	=> 'application/vnd.ms-powerpoint',
				'ps'	=> 'application/postscript',
				'qt'	=> 'video/quicktime',
				'ra'	=> 'audio/x-realaudio',
				'rar'=>'application/octet-stream',
				'ram'	=> 'audio/x-pn-realaudio',
				'ras'	=> 'image/x-cmu-raster',
				'rgb'	=> 'image/x-rgb',
				'rm'	=> 'audio/x-pn-realaudio',
				'roff'	=> 'application/x-troff',
				'rpm'	=> 'audio/x-pn-realaudio-plugin',
				'rtf'	=> 'text/rtf',
				'rtx'	=> 'text/richtext',
				'sgm'	=> 'text/sgml',
				'sgml'	=> 'text/sgml',
				'sh'	=> 'application/x-sh',
				'shar'	=> 'application/x-shar',
				'shtml'	=> 'text/html',
				'silo'	=> 'model/mesh',
				'sit'	=> 'application/x-stuffit',
				'skd'	=> 'application/x-koan',
				'skm'	=> 'application/x-koan',
				'skp'	=> 'application/x-koan',
				'skt'	=> 'application/x-koan',
				'smi'	=> 'application/smil',
				'smil'	=> 'application/smil',
				'snd'	=> 'audio/basic',
				'so'	=> 'application/octet-stream',
				'spl'	=> 'application/x-futuresplash',
				'src'	=> 'application/x-wais-source',
				'stc'	=> 'application/vnd.sun.xml.calc.template',
				'std'	=> 'application/vnd.sun.xml.draw.template',
				'sti'	=> 'application/vnd.sun.xml.impress.template',
				'stw'	=> 'application/vnd.sun.xml.writer.template',
				'sv4cpio'	=> 'application/x-sv4cpio',
				'sv4crc'	=> 'application/x-sv4crc',
				'swf'	=> 'application/x-shockwave-flash',
				'sxc'	=> 'application/vnd.sun.xml.calc',
				'sxd'	=> 'application/vnd.sun.xml.draw',
				'sxg'	=> 'application/vnd.sun.xml.writer.global',
				'sxi'	=> 'application/vnd.sun.xml.impress',
				'sxm'	=> 'application/vnd.sun.xml.math',
				'sxw'	=> 'application/vnd.sun.xml.writer',
				't'	=> 'application/x-troff',
				'tar'	=> 'application/x-tar',
				'tcl'	=> 'application/x-tcl',
				'tex'	=> 'application/x-tex',
				'texi'	=> 'application/x-texinfo',
				'texinfo'	=> 'application/x-texinfo',
				'tgz'	=> 'application/x-gtar',
				'tif'	=> 'image/tiff',
				'tiff'	=> 'image/tiff',
				'tr'	=> 'application/x-troff',
				'tsv'	=> 'text/tab-separated-values',
				'txt'	=> 'text/plain',
				'ustar'	=> 'application/x-ustar',
				'vbs'	=> 'text/plain', //added by skwashd - for obvious reasons
				'vcd'	=> 'application/x-cdlink',
				'vcf'	=> 'text/x-vcard',
				'vcs'	=> 'text/calendar',
				'vfb'	=> 'text/calendar',
				'vrml'	=> 'model/vrml',
				'vsd'	=> 'application/vnd.visio',
				'wav'	=> 'audio/x-wav',
				'wax'	=> 'audio/x-ms-wax',
				'wbmp'	=> 'image/vnd.wap.wbmp',
				'wbxml'	=> 'application/vnd.wap.wbxml',
				'wm'	=> 'video/x-ms-wm',
				'wma'	=> 'audio/x-ms-wma',
				'wmd'	=> 'application/x-ms-wmd',
				'wml'	=> 'text/vnd.wap.wml',
				'wmlc'	=> 'application/vnd.wap.wmlc',
				'wmls'	=> 'text/vnd.wap.wmlscript',
				'wmlsc'	=> 'application/vnd.wap.wmlscriptc',
				'wmv'	=> 'video/x-ms-wmv',
				'wmx'	=> 'video/x-ms-wmx',
				'wmz'	=> 'application/x-ms-wmz',
				'wrl'	=> 'model/vrml',
				'wvx'	=> 'video/x-ms-wvx',
				'xbm'	=> 'image/x-xbitmap',
				'xht'	=> 'application/xhtml+xml',
				'xhtml'	=> 'application/xhtml+xml',
				'xls'	=> 'application/vnd.ms-excel',
				'xlt'	=> 'application/vnd.ms-excel',
				'xml'	=> 'application/xml',
				'xpm'	=> 'image/x-xpixmap',
				'xsl'	=> 'text/xml',
				'xwd'	=> 'image/x-xwindowdump',
				'xyz'	=> 'chemical/x-xyz',
				'z'	=> 'application/x-compress',
				'zip'	=> 'application/zip',
       );
       $type = strtolower(substr(strrchr($filename, '.'),1));
       if(isset($contentType[$type])) {
            $mime = $contentType[$type];
       }else {
       	    $mime = 'application/octet-stream';
       }
       return $mime;
    }
}

?>