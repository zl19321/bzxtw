<?php
// +----------------------------------------------------------------------
// | FangFa Net [ http://www.fangfa.net ]
// +----------------------------------------------------------------------
// | File: IpArea.class.php
// +----------------------------------------------------------------------
// | Date: 2010-6-22
// +----------------------------------------------------------------------
// | Author: fangfa <1364119331@qq.com>
// +----------------------------------------------------------------------
// | 文件描述: IP 地理位置查询类 修改自 PHPCMS
// +----------------------------------------------------------------------

class IpArea {
	/**
	 * 纯真库文件指针
	 */
	public $fp = NULL;
	
	/**
	 * 分析函数
	 * 
	 */
	public $func;
	
	/**
	 *
	 * 偏移量
	 */
	public $offset;
	
	/**
	 * 索引
	 */ 
	public $index;
	
	function __construct($filename) {
		if (@file_exists ( $filename )) {
			$this->func = 'data_full';
			$this->fp = @fopen ( $filename, 'rb' );
		}
	}
	
	function get($ip) {
		$return = '';
		if (preg_match ( "/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}$/", $ip )) {
			$iparray = explode ( '.', $ip );
			if ($iparray [0] == 10 || $iparray [0] == 127 || ($iparray [0] == 192 && $iparray [1] == 168) || ($iparray [0] == 172 && ($iparray [1] >= 16 && $iparray [1] <= 31))) {
				$return = 'LAN';
			} elseif ($iparray [0] > 255 || $iparray [1] > 255 || $iparray [2] > 255 || $iparray [3] > 255) {
				$return = 'Invalid IP Address';
			} else {
				$return = $this->func ? $this->{$this->func} ( $ip ) : '';
				if (strpos ( $return, ' ' ) !== false)
					$return = substr ( $return, 0, strpos ( $return, ' ' ) );
			}
			$return = iconv ( 'gbk', 'utf-8', $return );
		}
		return $return;
	}
	
	function data_full($ip) {
		rewind ( $this->fp );
		$ip = explode ( '.', $ip );
		$ipNum = $ip [0] * 16777216 + $ip [1] * 65536 + $ip [2] * 256 + $ip [3];
		if (! ($DataBegin = fread ( $this->fp, 4 )) || ! ($DataEnd = fread ( $this->fp, 4 )))
			return;
		@$ipbegin = implode ( '', unpack ( 'L', $DataBegin ) );
		if ($ipbegin < 0)
			$ipbegin += pow ( 2, 32 );
		@$ipend = implode ( '', unpack ( 'L', $DataEnd ) );
		if ($ipend < 0)
			$ipend += pow ( 2, 32 );
		$ipAllNum = ($ipend - $ipbegin) / 7 + 1;
		$BeginNum = $ip2num = $ip1num = 0;
		$ipAddr1 = $ipAddr2 = '';
		$EndNum = $ipAllNum;
		while ( $ip1num > $ipNum || $ip2num < $ipNum ) {
			$Middle = intval ( ($EndNum + $BeginNum) / 2 );
			fseek ( $this->fp, $ipbegin + 7 * $Middle );
			$ipData1 = fread ( $this->fp, 4 );
			if (strlen ( $ipData1 ) < 4) {
				fclose ( $this->fp );
				return 'System Error';
			}
			$ip1num = implode ( '', unpack ( 'L', $ipData1 ) );
			if ($ip1num < 0)
				$ip1num += pow ( 2, 32 );
			if ($ip1num > $ipNum) {
				$EndNum = $Middle;
				continue;
			}
			$DataSeek = fread ( $this->fp, 3 );
			if (strlen ( $DataSeek ) < 3) {
				fclose ( $this->fp );
				return 'System Error';
			}
			$DataSeek = implode ( '', unpack ( 'L', $DataSeek . chr ( 0 ) ) );
			fseek ( $this->fp, $DataSeek );
			$ipData2 = fread ( $this->fp, 4 );
			if (strlen ( $ipData2 ) < 4) {
				fclose ( $this->fp );
				return 'System Error';
			}
			$ip2num = implode ( '', unpack ( 'L', $ipData2 ) );
			if ($ip2num < 0)
				$ip2num += pow ( 2, 32 );
			if ($ip2num < $ipNum) {
				if ($Middle == $BeginNum) {
					fclose ( $this->fp );
					return 'Unknown';
				}
				$BeginNum = $Middle;
			}
		}
		$ipFlag = fread ( $this->fp, 1 );
		if ($ipFlag == chr ( 1 )) {
			$ipSeek = fread ( $this->fp, 3 );
			if (strlen ( $ipSeek ) < 3) {
				fclose ( $this->fp );
				return 'System Error';
			}
			$ipSeek = implode ( '', unpack ( 'L', $ipSeek . chr ( 0 ) ) );
			fseek ( $this->fp, $ipSeek );
			$ipFlag = fread ( $this->fp, 1 );
		}
		if ($ipFlag == chr ( 2 )) {
			$AddrSeek = fread ( $this->fp, 3 );
			if (strlen ( $AddrSeek ) < 3) {
				fclose ( $this->fp );
				return 'System Error';
			}
			$ipFlag = fread ( $this->fp, 1 );
			if ($ipFlag == chr ( 2 )) {
				$AddrSeek2 = fread ( $this->fp, 3 );
				if (strlen ( $AddrSeek2 ) < 3) {
					fclose ( $this->fp );
					return 'System Error';
				}
				$AddrSeek2 = implode ( '', unpack ( 'L', $AddrSeek2 . chr ( 0 ) ) );
				fseek ( $this->fp, $AddrSeek2 );
			} else {
				fseek ( $this->fp, - 1, SEEK_CUR );
			}
			while ( ($char = fread ( $this->fp, 1 )) != chr ( 0 ) )
				$ipAddr2 .= $char;
			$AddrSeek = implode ( '', unpack ( 'L', $AddrSeek . chr ( 0 ) ) );
			fseek ( $this->fp, $AddrSeek );
			while ( ($char = fread ( $this->fp, 1 )) != chr ( 0 ) )
				$ipAddr1 .= $char;
		} else {
			fseek ( $this->fp, - 1, SEEK_CUR );
			while ( ($char = fread ( $this->fp, 1 )) != chr ( 0 ) )
				$ipAddr1 .= $char;
			$ipFlag = fread ( $this->fp, 1 );
			if ($ipFlag == chr ( 2 )) {
				$AddrSeek2 = fread ( $this->fp, 3 );
				if (strlen ( $AddrSeek2 ) < 3) {
					fclose ( $this->fp );
					return 'System Error';
				}
				$AddrSeek2 = implode ( '', unpack ( 'L', $AddrSeek2 . chr ( 0 ) ) );
				fseek ( $this->fp, $AddrSeek2 );
			} else {
				fseek ( $this->fp, - 1, SEEK_CUR );
			}
			while ( ($char = fread ( $this->fp, 1 )) != chr ( 0 ) )
				$ipAddr2 .= $char;
		}
		if (preg_match ( '/http/i', $ipAddr2 ))
			$ipAddr2 = '';
		$ipaddr = "$ipAddr1 $ipAddr2";
		$ipaddr = preg_replace ( '/CZ88\.NET/is', '', $ipaddr );
		$ipaddr = preg_replace ( '/^\s*/is', '', $ipaddr );
		$ipaddr = preg_replace ( '/\s*$/is', '', $ipaddr );
		if (preg_match ( '/http/i', $ipaddr ) || $ipaddr == '')
			$ipaddr = 'Unknown';
		return '' . $ipaddr;
	}
	
	function close() {
		@fclose ( $this->fp );
	}
}
?>