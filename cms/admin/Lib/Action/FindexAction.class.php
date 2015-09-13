<?php
// +----------------------------------------------------------------------
// | FangFa Net [ http://www.fangfa.net ]
// +----------------------------------------------------------------------
// | File: FindexAction.class.php
// +----------------------------------------------------------------------
// | Date: 2010-4-28
// +----------------------------------------------------------------------
// | Author: fangfa <1364119331@qq.com>
// +----------------------------------------------------------------------
// | 文件描述: 后台管理主页
// +----------------------------------------------------------------------

defined ( 'IN_ADMIN' ) or die ( 'Access Denied' );
/**
 * @name 后台管理主页
 *
 */
class FindexAction extends FbaseAction {

	/**
	 * @name后台框架
	 */
	public function index() {
		$in = &$this->in;
		if ($in['ajax']) {
			$this->_ajax_index();
		}
		//角色的菜单
		$_menu = D('Menu');
		$where = array(
			'parentid' => '1',	//后台顶部菜单
		);

		/*switch ($this->_a_theme) {
			case 'theme2':
				$_menu = D ("Menu");
				$this->assign('menu_html', $_menu->getRoleMenu2(2,$_SESSION['userdata']['roles']));
				break;
			case 'theme3':
			case 'theme4':
				$_menu = D ("Menu");
				$this->assign('menu_html', $_menu->getRoleMenu3(2,$_SESSION['userdata']['roles']));
				break;
			default :
				$topMenus = $_menu->where($where)->findAll();
				$this->assign('topMenus',$topMenus);
		}*/
		$_menu = D ("Menu");
		$this->assign('menu_html', $_menu->getRoleMenu3(1,$_SESSION['userdata']['roles']));
		$this->assign('companyname',C('COMPANYNAME'));
		//用户信息
		$this->display ($this->_a_theme);
	}


	/**
	 * @name后台主界面上的ajax请求
	 */
	private function _ajax_index() {
		$in = &$this->in;
		switch ($in['ajax']) {
			case 'getmenu':  //获取菜单
				if ($in['root'] == 'source' && $in['menuid']) {
					$menuid = intval($in['menuid']);
				} else {
					$menuid = intval($in['root']);
				}
				$_menu = D ("Menu");
				$data = $_menu->getRoleMenu($menuid,$_SESSION['userdata']['roles']);
//				dump($data);exit;
				die(json_encode($data));
				break;
		}
		exit ();
	}

	/**
	 * @name管理主页、显示统计信息
	 */
	public function home() {
		$in = &$this->in;
		$_user = D ('User');
		$userdata = $_user->getUserData($_SESSION['userdata']['user_id']);

		if ($_SESSION['userdata']['user_id'] == '999999') {
				$userdata['user_id'] = '999999';
				$userdata['username'] = 'Developer';
				$userdata['rolenickname'] = 'Developer';
		}
		if ($userdata['last_login_ip']) {
			import('ORG.Net.IpArea');
			$_ipArea = new IpArea(INCLUDE_PATH . '/data/QQWry.Dat');
			$userdata['last_login_place'] = $_ipArea->get($userdata['last_login_ip']);
		}
		$this->assign('userdata',$userdata);
		$this->display ();
	}

	/**
	 * @name后台导航
	 */
	public function map() {
		$this->display();
	}

	/**
	 * @name系统信息、环境检测
	 */
	public function system() {
		//允许上传的最大文件大小
		if (@ini_get ( 'file_uploads' )) {
			$sys_info ['fileupload'] = ini_get ( 'upload_max_filesize' );
		} else {
			$sys_info ['fileupload'] = '<font color="red">未知大小</font>';
		}
		//gd信息
		$sys_info ['gdv'] = $gd = $this->gd_version ();
		if ($gd == 0) {
			$sys_info ['gd'] = 'N/A';
		} else {
			if ($gd == 1) {
				$sys_info ['gd'] = 'GD1';
			} else {
				$sys_info ['gd'] = 'GD2';
			}
			$sys_info ['gd'] .= ' (';
			/* 检查系统支持的图片类型 */
			if ($gd && (imagetypes () & IMG_JPG) > 0) {
				$sys_info ['gd'] .= ' JPEG';
			}
			if ($gd && (imagetypes () & IMG_GIF) > 0) {
				$sys_info ['gd'] .= ' GIF';
			}
			if ($gd && (imagetypes () & IMG_PNG) > 0) {
				$sys_info ['gd'] .= ' PNG';
			}
			$sys_info ['gd'] .= ')';
		}
		//操作系统
		$sys_info ['os'] = PHP_OS;
		$sys_info ['zlib'] = function_exists ( 'gzclose' ); //zlib
		$sys_info ['safe_mode'] = ( boolean ) ini_get ( 'safe_mode' ); //safe_mode = Off
		$sys_info ['safe_mode_gid'] = ( boolean ) ini_get ( 'safe_mode_gid' ); //safe_mode_gid = Off
		$sys_info ['timezone'] = function_exists ( "date_default_timezone_get" ) ? date_default_timezone_get () : '没有设置';
		$sys_info ['socket'] = function_exists ( 'fsockopen' );
		$sys_info ['web_server'] = $_SERVER ['SERVER_SOFTWARE']; //web服务器

		$sys_info ['short_open_tag'] = ( boolean ) @ini_get ( 'short_open_tag' ); //短标签功能
		$sys_info ['php_fopenurl'] = ( boolean ) @ini_get ( 'allow_url_fopen' ); //远程打开,不符合要求将导致采集、远程资料本地化等功能无法应用
		//GD 不支持将导致与图片相关的大多数功能无法使用
		$sys_info ['php_dns'] = preg_match ( "/^[0-9.]{7,15}$/", @gethostbyname ( 'www.phpcms.cn' ) ); //域名解析
		$sys_info ['gzip'] = GZIP && function_exists ( 'ob_gzhandler' ); //开启gzip设置

		$sys_info ['version'] = $this->version;  //cms版本
		$sys_info ['phpv'] = phpversion ();  //php版本
		$_db = D ();
		$ft_min = $_db->query ( "SHOW VARIABLES LIKE 'ft_min_word_len'" );
		$sys_info ['ft_min_word_len'] = $ft_min [0];
		$sys_info ['mysqlv'] = mysql_get_server_info ( $_db->getDb ()->getNlinkID () ); //数据库版本
		$sys_info ['ft_min_word_len'] = $sys_info ['ft_min_word_len']['Value'];  //全文索引最小字长度设定
		$sys_info ['web_root'] = realpath(FANGFACMS_ROOT); //网站根目录
		$sys_info ['web_root_relative'] = '/' . __ROOT__; //网站根目录
		//检测mb扩展是否开启
		$sys_info ['mb_string'] = (boolean)function_exists('mb_substr');
		$this->assign ( 'sys_info', $sys_info );
		$this->display ();
	}

	/**
	 * @name获取GD库版本
	 */
	private function gd_version() {
		static $version = - 1;
		if ($version >= 0) {
			return $version;
		}
		if (! extension_loaded ( 'gd' )) {
			$version = 0;
		} else {
			// 尝试使用gd_info函数
			if (PHP_VERSION >= '4.3') {
				if (function_exists ( 'gd_info' )) {
					$ver_info = gd_info ();
					preg_match ( '/\d/', $ver_info ['GD Version'], $match );
					$version = $match [0];
				} else {
					if (function_exists ( 'imagecreatetruecolor' )) {
						$version = 2;
					} elseif (function_exists ( 'imagecreate' )) {
						$version = 1;
					}
				}
			} else {
				if (preg_match ( '/phpinfo/', ini_get ( 'disable_functions' ) )) {
					/* 如果phpinfo被禁用，无法确定gd版本 */
					$version = 1;
				} else {
					// 使用phpinfo函数
					ob_start ();
					phpinfo ( 8 );
					$info = ob_get_contents ();
					ob_end_clean ();
					$info = stristr ( $info, 'gd version' );
					preg_match ( '/\d/', $info, $match );
					$version = $match [0];
				}
			}
		}
		return $version;
	}

	/**
	 * @name 得到栏目树
	 * @param unknown_type $parentid
	 */
	private function getTreeByParentId($parentid) {
        //要返回的数据
        $data = array();
        if ($parentid > 0) {
            $data[0] = F ('category_'.$parentid);
            if (!empty($data[0]['childrenidarr'])) {
                foreach ($data[0]['childrenidarr'] as $v) {
                    $data[] = F ('category_'.$v);
                }
            }
            $data = array_to_tree($data,'catid','parentid' );
            $return = $data[0];
        } else {
            $categorys = D('Category','admin')->field("`name`,`parentid`,`catid`,`catdir`,`url`,`sort`")->order("`sort` ASC")->findAll();
            $return = array_to_tree($categorys,'catid','parentid');
        }
        return $return;
    }

    /**
     * @name 测试
     */
    public function test() {
        print_r($this->getTreeByParentId(0));
    }

}
?>