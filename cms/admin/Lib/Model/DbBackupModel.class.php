<?php
/**
 * 数据库备份，还原，优化，修复
 */
class DbBackupModel extends Model {
	/**
     +----------------------------------------------------------
     * 取得数据库的表信息
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     */
	public function getTables()
	{
		return parent::getDb()->getTables();
	}
	
	/**
     +----------------------------------------------------------
     * 取得数据库表总共记录数
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     */
	public function getCount($table) 
	{
		$db = parent::getDb();
		$result = $db->query('SELECT count(*) as total FROM `' . $table . '`');
		return $result[0]['total'];
	}
	
    /**
     +----------------------------------------------------------
     * 备份数据库
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param tables array 需要备份的数据表
     * @param fsize int 分卷大小
     * @param fnum int 分卷序号
     * @param nowtable string 正执行备份的表
     * @param startpos int 记录数开始行号
     * @param backup_last_string string 备份卷后缀字符
     +----------------------------------------------------------
     * @return array
     +----------------------------------------------------------
     */
    public function doBackup($tables, $fsize=2048, $fnum=1, $nowtable='', $startpos=0, $prefix)
    {
    	 $db = parent::getDb();
    	 $fsizeb = $fsize * 1000;
    	 $bakStr = '';  //备份数据
    	 
    	 if (!is_array($tables)) {
    	 	return array('code' => 'error', 'msg' => '没有需要备份的表');
    	 }
    	 //判断是否存在备份文件夹
    	 if (is_dir(DB_BACKUP_PATH)) {
    	 	mk_dir(DB_BACKUP_PATH, 0775);
    	 }
    	 if (!is_writable(DB_BACKUP_PATH)) {
    	 	return array('code' => 'error', 'msg' => '请您将文件夹 ' . DB_BACKUP_PATH . ' 设置成775或777');
    	 }
    	 
    	$bkfile = DB_BACKUP_PATH . $prefix . '_' . $fnum . ".txt"; //备份的文件
		$mysql_version = substr(mysql_get_server_info ( parent::getDb()->getNlinkID()), 0, 3);
		
		$fp = fopen($bkfile, "w");
		foreach($tables as $key=>$t)
		{
			if ($nowtable != $t && $startpos == 0) {   //如果不是表记录分卷
				$bakStr .= "DROP TABLE IF EXISTS `$t`;\r\n";
				$createTable = $this->doViewinfo($t);  //获取表结构
				$bakStr .= ''.$createTable.";\r\n\r\n";
				//fwrite($fp,''.$createTable.";\r\n\r\n");  //写入表结构到文件
			}
			
			$j = 0;
			$fs = '';
    		//获取当前表结构
    		$table_fields = $db->getFields($t);
    		foreach ($table_fields AS $tf) {
    			$fs[$j] = trim($tf['name']);
				$j++;
    		}
			$fsd = $j-1;
			
			//获取当面表所有数据
    		$data_list = $db->query("SELECT * FROM `$t`");
			$intable = "INSERT INTO `$t` VALUES(";
			
			$m = 0;
			foreach ($data_list AS $dl) {
				if ($m < $startpos) {
					$m++;
					continue;
				}
				
				//检测数据是否达到规定大小
				if(strlen($bakStr) > $fsizeb)
				{
					fwrite($fp, $bakStr);
					fclose($fp);
					return array('code'   	=> 'incomplete',
								 'tableName' 	=> implode(',', $tables), 
								 'fsize'  	=> $fsize, 
								 'fnum'   	=> $fnum+1, 
								 'nowtable'	=> $t, 
								 'startpos'	=> $m,
								 'msg' 		=> "已完成第" . $fnum ."卷备份，正在备份下一卷...");
				}
				
				//正常情况
				$line = $intable;
				for($j=0;$j<=$fsd;$j++)
				{
					if($j < $fsd)
					{
						$line .= "'" . $this->rpLine(addslashes($dl[$fs[$j]])) . "',";
					}
					else
					{
						$line .= "'" . $this->rpLine(addslashes($dl[$fs[$j]])) . "');\r\n";
					}
				}
				$m++;
				$bakStr .= $line;
			}
			
			$startpos = 0;
			$bakStr .= "\r\n";
			unset($tables[$key]);
		}
		
    	fwrite($fp, $bakStr);  //写入表结构到文件
    	fclose($fp);
    	return array('code' => 'success', 'msg' => '已完成第' . $fnum . '卷备份！<br />完成所有数据备份,共' . $fnum . '卷！');
    }
    
    /**
     +----------------------------------------------------------
     * 数据还原
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     */
    public function doRedat($backupFile)
    {
    	$tdata = '';
		$fp = fopen($backupFile, 'r');
		while(!feof($fp))
		{
			$tdata .= fgets($fp, 512*1024);
		}
		fclose($fp);
		
		$tdata = explode(";\r\n", $tdata);
		foreach ($tdata AS $sql) {
			parent::getDb()->execute($sql);
		}
		return true;
    }
    
    /**
     +----------------------------------------------------------
     * 修复表
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     */
    public function doRepair($tablename)
    {
    	return parent::getDb()->execute("REPAIR TABLE `$tablename` ");
    }
    
    /**
     +----------------------------------------------------------
     * 优化表
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     */
    public function doOpimize($tablename){
    	return parent::getDb()->execute("OPTIMIZE TABLE `$tablename` ");
    }
    
    /**
     +----------------------------------------------------------
     * 查看表结构
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     */
    public function doViewinfo($tablename)
    {
    	$create = current(parent::getDb()->query('SHOW CREATE TABLE `' . $tablename . '`'));
    	return $create['Create Table'];
    }
    
    /**
     +----------------------------------------------------------
     * 取得已备份数据
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     */
    public function getBackupFiles($backup_path) {
    	$dh = dir($backup_path);
		$i = 0;
		$filelists = array();
		while (false !== ($filename = $dh->read())) {
			if(!preg_match('/txt$/i',$filename))
			{
				continue;
			}
			if( filesize($backup_path . "/$filename") > 0 )
			{
				$filelists[$i]['name'] = $filename;
				$filelists[$i]['mtime'] = date('Y-m-d H:i:s', filemtime($backup_path . "/$filename"));
				$filelists[$i]['size'] = byte_format(filesize($backup_path . "/$filename"));
			}
			$i++;
		}
		$dh->close();
		
		return $filelists;
    }
    
    /**
     +----------------------------------------------------------
     * 分类排序备份文件
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     */
    public function orderBackupFiles($backup_path)
    {
    	$files = $this->getBackupFiles($backup_path);
    	$result = array();
    	foreach ($files AS $f) {
    		$prefix = substr($f['name'], 0, 21);
    		$result[$prefix][substr(trim($f['name'], '.txt'), 22)] = $f;
    	}
    	ksort($result);
    	return $result;
    }
    
    /**
     +----------------------------------------------------------
     * 字符替换
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     */
    public function rpLine($str)
	{
		$str = str_replace("\r","\\r",$str);
		$str = str_replace("\n","\\n",$str);
		return $str;
	}
	
}