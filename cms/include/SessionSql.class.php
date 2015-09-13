<?php
// +----------------------------------------------------------------------
// | Mysql存储SESSION
// +----------------------------------------------------------------------
// | Session.calss.php
// +----------------------------------------------------------------------
// | Author: 成俊 <cgjp123@163.com>
// +----------------------------------------------------------------------
// | 利用数据库对SESSION进行存储
// +----------------------------------------------------------------------

class SessionSql {
	
	private static $_expiry = '1440';//SESSION默认有效期
	
	private static $table_name = 'session'; //数据库SESSION表名

	
	public static function init(){		
		ini_set('session.gc_maxlifetime',SessionSql::$_expiry);//设置SESSION最大有效期
	}
	
	public static function sess_open($save_path, $session_name){
		return true;
	}

	public static function sess_close(){
		SessionSql::sess_gc(ini_get('session.gc_maxlifetime'));
		return true;
	}
	
	public static function sess_read($sid){
		$db = Db::getInstance();
		$table_prefix = C ('DB_PREFIX');
		$sql = "SELECT * FROM ".$table_prefix.self::$table_name." WHERE `sid`='".$sid."'";
		$query_data = $db->query($sql);//读取SESSION
		if($query_data){
			return $query_data[0]['value'];//session读取的返回值应该是VALUE值
		}else{
			return NULL;
		}
	}
	
	public static function sess_write($sid,$value){
		$expiry = time()+self::$_expiry;//有效时间		
		$_mSession = M(ucfirst(self::$table_name));
		$sql = "REPLACE INTO __TABLE__ (`sid`,`expiry`,`value`) VALUES ('{$sid}','{$expiry}','{$value}')";	
		return $_mSession->execute($sql);		
	}
	
	public static function sess_destory($sid){
		$_mSession = M(ucfirst(self::$table_name));
		$option['where'] = " `sid` = '{$sid}' ";  
		$sid = $_mSession->delete($option);   
		return true;  
	}
	
	public static function sess_gc($maxLifeTime){
		$_mSession = M(ucfirst(self::$table_name));
		$option['where'] = " `expiry` < " . time();
		$_mSession->delete($option);
		return true;
	}

}//类定义结束
?>