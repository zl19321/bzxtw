<?php
// +----------------------------------------------------------------------
// | FangFa Net [ http://www.fangfa.net ]
// +----------------------------------------------------------------------
// | File: ExcelParser.class.php
// +----------------------------------------------------------------------
// | Date: 2010-6-28
// +----------------------------------------------------------------------
// | Author: fangfa <1364119331@qq.com>
// +----------------------------------------------------------------------
// | 文件描述: 读取Excel文件数据
// +----------------------------------------------------------------------
/**
 * 导入PHPExcelParser
 */
vendor('PHPExcelParser.excelparser');
class ExcelParser {
	/**
	 * 
	 * @var 读取到的数据
	 */
	private $_data = array (0, '');
	
	/**
	 * 
	 * @var excelparser 类对象实例
	 */
	private $_excel_handle;
	
	/**
	 * 
	 * @var excel 文件信息
	 */
	private $_excel = array ();
	
	/**
	 * 构造函数
	 * @param <string> $filename 上传文件临时文件名称
	 */
	public function __construct($filename) {
		//excelparser类对象	 
		$this->_excel_handle = new ExcelFileParser ();
		//错误获取
		$this->checkErrors ( $filename );
	}
	
	/**
	 * excel文件的错误校验
	 */
	private function checkErrors($filename) {
		/**
		 * 方法一
		 */
		$error_code = $this->_excel_handle->ParseFromFile ( $filename );
		/**
		 * 方法二
		 * $file_handle = fopen($this->_filename,'rb');
		 * $content = fread($file_handle,filesize($this->_filename));
		 * fclose($file_handle);
		 * $error_code = $this->_excel->ParseFromString($content);
		 * unset($content,$file_handle);
		 */
		switch ($error_code) {
			case 0 :
				//无错误不处理
				break;
			case 1 :
				$this->_data = array (
					1, '文件读取错误(Linux注意读写权限)' 
				);
				break;
			case 2 :
				$this->_data = array (
					1, '文件太小' 
				);
				break;
			case 3 :
				$this->_data = array (
					1, '读取Excel表头失败' 
				);
				break;
			case 4 :
				$this->_data = array (
					1, '文件读取错误' 
				);
				break;
			case 5 :
				$this->_data = array (
					1, '文件可能为空' 
				);
				break;
			case 6 :
				$this->_data = array (
					1, '文件不完整' 
				);
				break;
			case 7 :
				$this->_data = array (
					1, '读取数据错误' 
				);
				break;
			case 8 :
				$this->_data = array (
					1, '版本错误' 
				);
				break;
		}
		unset ( $error_code );
	}
	/**
	 * Excel信息获取
	 */
	private function getExcelInfo() {
		if (1 == $this->_data [0])
			return;
		/**
		 * 获得sheet数量
		 * 获得sheet单元对应的行和列
		 */
		$this->_excel ['sheet_number'] = count ( $this->_excel_handle->worksheet ['name'] );
		for($i = 0; $i < $this->_excel ['sheet_number']; $i ++) {
			/**
			 * 行于列
			 * 注意:从0开始计数
			 */
			$row = $this->_excel_handle->worksheet ['data'] [$i] ['max_row'];
			$col = $this->_excel_handle->worksheet ['data'] [$i] ['max_col'];
			$this->_excel ['row_number'] [$i] = ($row == NULL) ? 0 : ++ $row;
			$this->_excel ['col_number'] [$i] = ($col == NULL) ? 0 : ++ $col;
			unset ( $row, $col );
		}
	}
	/**
	 * 中文处理函数
	 * @return <string>
	 */
	private function uc2html($str) {
		$ret = '';
		for($i = 0; $i < strlen ( $str ) / 2; $i ++) {
			$charcode = ord ( $str [$i * 2] ) + 256 * ord ( $str [$i * 2 + 1] );
			$ret .= '&#' . $charcode;
		}
		return mb_convert_encoding ( $ret, 'UTF-8', 'HTML-ENTITIES' );
	}
	/**
	 * Excel数据获取
	 */
	private function getExcelData() {
		if (1 == $this->_data [0])
			return;
			
		//修改标记
		$this->_data [0] = 1;
		//获取数据
		for($i = 0; $i < $this->_excel ['sheet_number']; $i ++) {
			/**
			 * 对行循环
			 */
			for($j = 0; $j < $this->_excel ['row_number'] [$i]; $j ++) {
				/**
				 * 对列循环
				 */
				for($k = 0; $k < $this->_excel ['col_number'] [$i]; $k ++) {
					/**
					 * array(4) {
					 * ["type"]   => 类型 [0字符类型1整数2浮点数3日期]
					 * ["font"]   => 字体
					 * ["data"]   => 数据
					 * ...
					 * }
					 */
					$data = $this->_excel_handle->worksheet ['data'] [$i] ['cell'] [$j] [$k];
					switch ($data ['type']) {
						case 0 :
							//字符类型
							if ($this->_excel_handle->sst ['unicode'] [$data ['data']]) {
								//中文处理
								$data ['data'] = $this->uc2html ( $this->_excel_handle->sst ['data'] [$data ['data']] );
							} else {
								$data ['data'] = $this->_excel_handle->sst ['data'] [$data ['data']];
							}
							break;
						case 1 :
							//整数
							//TODO
							break;
						case 2 :
							//浮点数
							//TODO
							break;
						case 3 :
							//日期
							//TODO
							break;
					}
					$this->_data [1] [$i] [$j] [$k] = $data ['data'];
					unset ( $data );
				}
			}
		}
	}
	/**
	 * 获取excel数据
	 * @return <array> array(标识符,内容s)
	 */
	public function getData() {
		//Excel信息获取
		$this->getExcelInfo ();
		//Excel数据获取
		$this->getExcelData ();
		//返回结果
		return $this->_data;
	}
	
	
}

?>
