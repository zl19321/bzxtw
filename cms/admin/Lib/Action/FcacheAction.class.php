<?php
// +----------------------------------------------------------------------
// | FangFa Net [ http://www.fangfa.net ]
// +----------------------------------------------------------------------
// | File: FdbAction.class.php
// +----------------------------------------------------------------------
// | Date: 2010-5-5
// +----------------------------------------------------------------------
// | Author: fangfa <1364119331@qq.com>
// +----------------------------------------------------------------------
// | 文件描述: 数据缓存维护
// +----------------------------------------------------------------------

defined ( 'IN_ADMIN' ) or die ( 'Access Denied' );
/**
 * @name 数据缓存维护
 *
 */
class FcacheAction extends FbaseAction {

	/**
	 * @name更新所有缓存
	 */
	public function all() {
		//检查数据缓存目录的权限设置
		//更新缓存信息
		$this->model (false);
		$this->category (false);
		$this->module (false);
		$this->index (false);
		$this->menu(false);
		$this->sysset(false);
        $this->act(false);
        $this->role(false);
        $this->ad(false);
        $this->keylink(false);
        //更新admin/front  app缓存
        $this->app();
		$this->message('<font class="green">所有缓存更新成功！</font>', U ( 'findex/home' ) );
	}
	
	/**
	 * @name更新app缓存以及项目临时文件，项目log
	 * 
	 */
	public function app() {
		$file = array(
			ALL_CACHE_PATH . 'admin/runtime/~app.php',
			ALL_CACHE_PATH . 'admin/runtime/~runtime.php',
			ALL_CACHE_PATH . 'front/runtime/~app.php',
			ALL_CACHE_PATH . 'front/runtime/~runtime.php',		
		);
		$dir = array(
			ALL_CACHE_PATH . 'admin/runtime/cache/',
			ALL_CACHE_PATH . 'admin/runtime/logs/',
			ALL_CACHE_PATH . 'admin/runtime/tmp/',
			ALL_CACHE_PATH . 'front/runtime/cache/',
			ALL_CACHE_PATH . 'front/runtime/logs/',
			ALL_CACHE_PATH . 'front/runtime/tmp/',
		);
		if (!empty($file)) {
			foreach ($file as $f) {
				@unlink($f);
			}
		}
		if (!empty($dir)) {
			import('ORG.Io.Dir');
			$_dir = new Dir();
			foreach ($dir as $d) {
				@$_dir->clearDir($d);
			}
		}
	}

	/**
	 * @name更新首页
	 * @param boolean $msg
	 */
	public function index($msg = true) {
		@unlink(FANGFA_ROOT . 'index.html');
		if($msg) {
			$this->message('<font class="green">网站首页更新成功！</font>',U('findex/home'));
		}
	}

	/**
	 * @name更新参数配置文件
	 * 
	 * @param boolean $msg
	 */
	public function sysset($msg = true) {
		$_setting = D ('Setting');
		$_setting->cacheAll();
		if($msg) {
			$this->message('<font class="green">网站配置更新成功！</font>',U('fset/set'));
		}
	}

	/**
	 * @name更新act缓存
	 * 
	 * @param boolean $msg
	 */
    public function act($msg = true) {
		$_setting = D ('Acts');
		$_setting->cacheAct();
    	if($msg) {
    		$in = &$this->in;
    		$url = 'frole/manage';
    		if(isset($in['isadmin']) && $in['isadmin']==0)$url = 'fmember/manage_group';
			$this->message('<font class="green">授权信息更新成功！</font>',U($url));
		}
	}

	/**
	 * @name更新模型缓存，包括field缓存
	 * @param boolean $msg
	 */
	public function model($msg = true) {
		$_model = D ( 'Model' );
		$_model->cacheAll ();
		if($msg) {
			$this->message('<font class="green">模型信息更新成功！</font>',U('fmodel/manage'));
		}
	}

	/**
	 * @name更新模块缓存
	 * @param boolean $msg
	 */
	public function module($msg = true) {
		$_module = D ( 'Module' );
		$_module->cacheAll ();
        if($msg) {
			$this->message('<font class="green">模块缓存更新成功！</font>',U('fmodule/manage'));
		}
	}

	/**
	 * @name更新栏目缓存，包括路由缓存
	 * @param boolean $msg
	 */
	public function category($msg = true) {
		$_category = D ( 'Category' );
		$_category->cacheAll ();
		
		if($msg) {
			$this->message('<font class="green">栏目信息更新成功！</font>',U('fcategory/manage'));
		}
	}


	/**
	 * @name更新菜单缓存
	 * @param boolean $msg
	 */
	public function menu($msg = true) {
		$_menu = D ('Menu');
		$_menu->cacheAll();
		if($msg) {
			$this->message('<font class="green">菜单缓存更新成功！</font>',U('fset/menu?do=manage'));
		}
	}

	
	/**
	 * @name更新角色信息缓存
	 * @param boolean $msg
	 */
	public function role($msg = true) {
		$_role = D ('Role');
		$_role->cacheAll();
		if($msg) {
			$this->message('<font class="green">角色缓存更新成功！</font>',U('frole/manage'));
		}
	}
	
	/**
	 * @name缓存广告信息
	 *
	 * @param boolean $msg
	 */
	public function ad($msg = true) {
		$_ad = D ('Ad');
		$_ad->cacheAll();
		if($msg) {
			$this->message('<font class="green">广告信息缓存更新成功！</font>',U('fad/manage'));
		}
	}
	
	/**
	 * @name更新关键词链接
	 * @param boolean $msg
	 */
	public function keylink($msg = true) {
		$_keylink = D('Keylink');
		$_keylink->cache();
		if($msg) {
			$this->message('<font class="green">关键词链接信息缓存更新成功！</font>',U('fset/keylink?do=manage'));
		}
	}
    
    /**
     * @name清空前后台所有缓存文件
     * @articler fangfa
     * @date 2013-01-28
     * 
     */
    
    public function deldir($dir) {
      //先删除目录下的文件：
      //前后台
        if(!isset($dir)){
            $dir['admin'] = './data/admin/runtime/templates_c';
            $dir['front'] = './data/front/runtime/templates_c';
        }
        foreach($dir as $k=>$v){
            $dh=opendir($v);
            while ($file=readdir($dh)) {
                if($file!="." && $file!="..") {
                    $fullpath=$v."/".$file;
                    if(!is_dir($fullpath)) {
                        unlink($fullpath);
                    } else {
                        deldir($fullpath);
                    }
                }
            }

        closedir($dh);
        }
        $this->message('<font class="green">前后台所有缓存文件已清空！</font>', U ( 'findex/home' ));
    } 
     
}

?>