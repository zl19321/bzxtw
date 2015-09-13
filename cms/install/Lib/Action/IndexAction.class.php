<?php
// 本类由系统自动生成，仅供测试用途
class IndexAction extends Action{
	protected $in;
	protected $fromurl;

	public function _initialize()
	{
		header('Content-Type: text/html; charset=utf-8');
		import ( 'ORG.Util.Input' );
		$this->in = &Input::getVar ( $_REQUEST );
		$this->in = (Object)$this->in;
		if (file_exists_case(FANGFACMS_ROOT . 'data/config.inc.php') && $this->in->step != 5) {
			die("程序已运行安装，如果你确定要重新安装，请先从FTP中删除 data/config.inc.php！");
    	}
	}

    public function index(){
        switch ($this->in->step) {
        	case 2:  //环境检测
        		$this->assign('step', '2');
        		$this->step_2();
        		exit;
        	case 3:   //参数填写
        		$this->assign('step', '3');
        		$this->step_3();
        		exit;
        	case 4:  //正在安装
        		$this->assign('step', '4');
        		$this->step_4();
        		exit;
        	case 5:  //安装完成
        		$this->assign('step', '5');
        		$this->step_5();
        		exit;
        	default:
        		$this->assign('step', '1');
        		$this->step_1();
        		exit;
        }
    }

    public function step_1()
    {
    	$this->display('step_1');
    }

    public function step_2()
    {
    	$data = array();
		$data['phpversion'] = phpversion();
		$data['os'] = @getenv('OS');
		$data['gdversion'] = gd_info();
		$data['software'] = $_SERVER['SERVER_SOFTWARE'];
		$data['servername'] = $_SERVER['SERVER_NAME'];
		$data['install_path'] = str_replace('\\', '/', FANGFACMS_ROOT);
		$data['max_execution_time'] = ini_get('max_execution_time');
		$data['allow_reference'] = (ini_get('allow_call_time_pass_reference') ? 1 : 0);
		$data['allow_url_fopen'] = (ini_get('allow_url_fopen') ? 1 : 0);
		$data['safe_mode'] = (ini_get('safe_mode') ? 1 : 0);
		$data['gd'] = ($data['gdversion']['GD Version'] ? 1 : 0);
		$data['mysql'] = (function_exists('mysql_connect') ? 1 : 0);

		$this->assign($data);
    	$this->display('step_2');
    }

    public function step_3()
    {
    	if ($this->in->short_open_tag != 1) {
    		die("<script>alert('您的PHP配置不支持短标签，请修改php.ini的short_open_tag = On');history.go(-1);</script>");
    	}
    	if ($this->in->mysql != 1) {
    		die("<script>alert('您的系统不支持Mysql数据库');history.go(-1);</script>");
    	}
    	if ($this->in->gd != 1) {
    		die("<script>alert('您的系统不支持GD库');history.go(-1);</script>");
    	}
    	if ($this->in->safe_mode == 1) {
    		die("<script>alert('本系统不支持在安全模式下运行！');history.go(-1);</script>");
    	}
    	if ($this->in->allow_url_fopen != 1) {
    		die("<script>alert('请开启php.ini里得 allow_url_fopen参数！');history.go(-1);</script>");
    	}
    	define('SCHEME', $_SERVER['SERVER_PORT'] == '443' ? 'https://' : 'http://');
    	$this->assign('siteurl',SCHEME.$_SERVER['HTTP_HOST']);
    	$this->display('step_3');
    }

    public function step_4()
    {
    	$this->in->username = 'admin'; //默认超级管理员是admin

    	if (empty($this->in->db_host) || empty($this->in->db_name) || empty($this->in->db_user) || empty($this->in->db_pwd) || empty($this->in->username) || empty($this->in->password) || empty($this->in->email)) {
    		die('<script>alert("表单填写不完整！请重新填写"); window.history.go(-1);</script>');
    	}
    	if (!file_exists_case(ALL_CACHE_PATH . '/CopyOfconfig.inc.php')) {
    		die('缺少配置文件' . ALL_CACHE_PATH . '/CopyOfconfig.inc.php' . '. 无法进行安装!');
    	}

    	//链接数据库
    	$conn = mysql_connect($this->in->db_host . ':' . $this->in->db_port, $this->in->db_user, $this->in->db_pwd) or die("<script>alert('数据库服务器或登录密码无效，\\n\\n无法连接数据库，请重新设定！');history.go(-1);</script>");
		mysql_query("CREATE DATABASE IF NOT EXISTS `" . $this->in->db_name . "`  DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;", $conn);
		mysql_select_db($this->in->db_name) or die("<script>alert('选择数据库失败，可能是你没权限，请预先创建一个数据库！');history.go(-1);</script>");

		//获得数据库版本信息
		$rs = mysql_query("SELECT VERSION();", $conn);
		$row = mysql_fetch_array($rs);
		$mysqlVersions = explode('.',trim($row[0]));
		$mysqlVersion = $mysqlVersions[0].".".$mysqlVersions[1];

		mysql_query("set names utf8,character_set_client=binary,sql_mode='';", $conn);

    	$config = require_once(ALL_CACHE_PATH . '/CopyOfconfig.inc.php');
    	$config_file_name = ALL_CACHE_PATH . '/config.inc.php';
    	$data = array();
    	if (is_readable(ALL_CACHE_PATH)) {
    		if (is_array($config)) {
    			$fp = fopen($config_file_name, 'wb');  //创建配置文件
    			fclose($fp);
				$config = array_change_key_case($config, CASE_UPPER);
				$upper_in = array_change_key_case((array)$this->in, CASE_UPPER);
				foreach ($config as $k=>$v) {
					if (array_key_exists($k, $upper_in)) {
						$data[$k] =  $upper_in[$k];
					} else $data[$k] = $v ;
				}
			} else die('<script>alert("文件 ' . ALL_CACHE_PATH . '/CopyOfconfig.inc.php' . ' 已被破坏，不能进行安装！"); window.history.go(-1);</script>');
    	} else {
    		die('<script>alert("文件夹' . ALL_CACHE_PATH . ' 没有写权限！"); window.history.go(-1);</script>');
    	}
    	//将配置信息写入到配置文件里
    	file_put_contents($config_file_name, "<?php\nreturn ".var_export($data,true).";");

    	//执行数据库表插入
    	$sql_data = file_get_contents(APP_PATH . '/Conf/fangfacms4.20.sql');
    	$sql_data = str_replace('#@__', $this->in->db_prefix, $sql_data);
		$sql_data = str_replace("\r\n", "\n", $sql_data);
    	$sql_lines = explode(";\n", $sql_data);
		foreach($sql_lines AS $sql) {
			mysql_query(trim($sql), $conn);
		}

    	//更新配置
    	foreach ($data AS $k=>$d) {
    		if(strtolower($k) == 'companyname') {
    			mysql_query('UPDATE `' . $this->in->db_prefix . 'setting` SET `value`="' . mysql_escape_string($d) . '" WHERE `var`="seotitle"', $conn);
    		}
    		mysql_query('UPDATE `' . $this->in->db_prefix . 'setting` SET `value`="' . mysql_escape_string($d) . '" WHERE `var`="' . strtolower($k) . '"', $conn);
    	}

    	//增加管理员帐号
    	mysql_query("INSERT INTO `" . $this->in->db_prefix . "user` (username, password, email, nickname, create_time, update_time, status, isadmin) VALUES('".$this->in->username."', '".md5($this->in->password)."', '".$this->in->email."', '超级管理员', '".time()."', '".time()."', 1, 1)", $conn);
		$admin_user_id = mysql_insert_id();
		mysql_query("INSERT INTO `" . $this->in->db_prefix . "user_person` SET user_id=". $admin_user_id);
		mysql_query("INSERT INTO `" . $this->in->db_prefix . "role_user` SET role_id=1, user_id=" . $admin_user_id);

		//插入IM管理员
		$im_admin_query = sprintf("insert into `{$this->in->db_prefix}chatoperator` (vclogin,vcpassword,vclocalename,vccommonname,vcavatar,vcemail,vcjabbername) values ('%s','%s','%s','%s','%s','%s','%s')", $this->in->username,md5($this->in->password),'Administrator','Administrator','',$this->in->email, '');
		mysql_query($im_admin_query);

    	mysql_close($conn);
    	$this->display('step_4');
    }

    public function step_5()
    {

    	//开始更新缓存
    	$_setting = D ('Setting', 'admin');
		$_setting->cacheAll();

    	$_setting = D ('Acts', 'admin');
		$_setting->cacheAct();

		$_models = D ('Model', 'admin');
		$_models->cacheAll ();

		$_module = D ('Module', 'admin');
		$_module->cacheAll ();

		$_category = D ('Category', 'admin');
		$_category->cacheAll ();

		$_menu = D ('Menu', 'admin');
		$_menu->cacheAll();

		$_role = D ('Role', 'admin');
		$_role->cacheAll();

    	$this->display('step_5');
    }
}
?>