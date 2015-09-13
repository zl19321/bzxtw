<?php
// +----------------------------------------------------------------------
// | FangFa Net [ http://www.fangfa.net ]
// +----------------------------------------------------------------------
// | File: FhomeAction.class.php
// +----------------------------------------------------------------------
// | Date: 2010-5-5
// +----------------------------------------------------------------------
// | Author: fangfa <1364119331@qq.com>
// +----------------------------------------------------------------------
// | 文件描述: 系统管理
// +----------------------------------------------------------------------


defined ( 'IN_ADMIN' ) or die ( 'Access Denied' );
/**
 * @name 系统管理
 *
 */
class FsetAction extends FbaseAction {
	
	/**
	 * @name系统设置
	 */
	public function set() {
		$in = &$this->in;
		if ($in['ajax']) $this->_set_ajax();
		$key = 'sysset';
		if ($this->ispost()) {
			$_setting = D ('Setting');
			if (is_array($in['info'])) {
				foreach ($in['info'] as $k=>$v) {
					$_setting->set($k,$v,$key);
				}
				//更新缓存和配置
				$_setting->cacheAll();
				R('Fcache','app');
				$this->message('<font class="green">网站配置保存成功！</font>',U('fset/set'));
			} else {
				$this->message('<font class="red">' . "参数错误！</font>");
			}
		}
		$_widget = D ( 'Widget' );
		$where = array (
			'appname' => APP_NAME, 
			'controller' => 'fset', 
			'action' => 'set', 
			'status' => '1' 
		);
		//启用的widget
		$widget_data = $_widget->where ( $where )->order("`sort` ASC")->findAll ();
		$data['widget'] = $widget_data;
		//widget需要的数据，参数表中
		$_setting = D('Setting');
		$value_data = $_setting->where("`key`='{$key}'")->findAll();
		if (is_array($value_data)) {
			foreach ($value_data as $k=>$v) {
				$value_data[$v['var']] = $v['value'];
				unset($value_data[$k]);	
			}
			$data['value'] = $value_data;
		}
		//当期选中项
		$in['selected'] && $data['selected'] = $in['selected'];
		$this->assign ( 'set_html', W ( 'SysSet', $data, true ) );
		$this->display ();
	}
	
	/**
	 * @name处理set时候的ajax请求操作
	 */
	protected function _set_ajax() {
		$in = &$this->in;
		switch($in['ajax']) {
			case 'test_mail':	//发送测试邮件
				import('SendMail',INCLUDE_PATH);
		        $_sendmail = get_instance_of('SendMail');
		        $_sendmail->set($in['mail_server'], $in['mail_port'], $in['mail_user'], $in['mail_password'], $in['mail_type']);
		        echo $_sendmail->send($in['email_to'], "测试邮件 - FangfaCMS {$this->version}", "Fanafa CMS {$this->version} 邮件发送测试！<br />", $in['mail_user']) ? "邮件发送成功！" : $_sendmail->error[0][1];
				break;
		}
		exit ();
	}
	
	
	
	/**
	 * @name菜单管理
	 */
	public function menu() {
		$in = &$this->in;		
		if ($in ['ajax']) {	//处理ajax请求
			switch ($in['ajax']) {
				case 'getchild':  //获取子菜单
					$_menu = D('Menu');
					$array[] = array(
							'title' => $in['parentid'] ? '请选择' : '无',
							'value' => 0,
						);
					$infos = $_menu->where("`parentid`='{$in['parentid']}'")->findAll();
			        if (is_array($infos)) {
				        foreach($infos as $k=>$v) {
							$array[] = array(
								'title' => $v['name'],
								'value' => $v['menuid'],
							);
					    }
			        }		        
					if(!$in['parentid'] || $array) {
						ksort($array);
						import('Html',INCLUDE_PATH);
						echo Html::select('setparentid',$array, $in['parentid'], 'onchange="if(this.value>0){getchild(this.value);$(\'#parentid\').val(this.value);this.disabled=true;}"');
					}
					break;
				case 'sort':  //ajax保存排序
						$in['menuid'] && $in['menuid'] = intval(substr($in['menuid'],5));
//						dump($in);
						$_menu = D ('Menu');
						if ($in['menuid'] && isset($in['sort'])) {
							$in['sort'] = intval($in['sort']);
							$data = array(
								'menuid' => $in['menuid'],
								'sort' => $in['sort']
							);
							if (false !== $_menu->save($data)) {
								echo $in['sort'];
								exit ();
							}
						}
						echo '';
						break;
				default:
					break;
			}
			
		} else {	//处理普通请求
			$TARGET = array(
				'_self'=>'当前窗口', 
				'_blank'=>'新窗口', 
				'top'=>'上窗口', 
				'left'=>'左窗口', 
				'mainFrame'=>'右窗口', 
			);
			$this->assign('TARGET',$TARGET);
			switch ($in['do']) {
				case 'add':	//添加
					$in ['tpl'] = 'add_menu';
					if ($in['parentid']) {
						$parent_data = D ('Menu')->find(intval($in['parentid']));
						$this->assign('parent_data',$parent_data);
					}
					//所有角色
					$_role = D ('Role');
					$this->assign('roles',$_role->where("`status`='1'")->select());
					if ($in['parentid']) {
						$this->assign('forward',U('fset/menu?do=manage&parentid='.$in['parentid']));
					} else {
						$this->assign('forward',U('fset/menu?do=manage'));
					}
					$this->addData();
					$this->ispost() && D('Menu')->cacheAll();
					break;
				case 'edit':		//编辑
					$in ['tpl'] = 'edit_menu';
					$in ['_tablename'] = 'menu';
					//所有角色
					$_role = D ('Role');
					$this->assign('roles',$_role->where("`status`='1'")->select());
					if ($in['parentid']) {
						$this->assign('forward',U('fset/menu?do=manage&parentid='.$in['parentid']));
					} else {
						$this->assign('forward',U('fset/menu?do=manage'));
					}
					$this->addData();
					$this->ispost() && D('Menu')->cacheAll();
					break;
				case 'manage':	//管理
					$in ['tpl'] = 'manage_menu';
					$in ['_tablename'] = 'menu';
					$in ['where'] = array(
						'parentid' => (int)$in ['parentid'],
					);
					$in['order'] = '`sort` ASC,`menuid` ASC';
					$this->assign('parentid',$in['parentid']);
					$this->assign('menu',F ('menu_'.$in['parentid'],'',ALL_CACHE_PATH .'menu/'));						
					$this->manageData();
					break;
				case 'delete':	//删除	
					$in ['_tablename'] = 'menu';
					$this->deleteData();
					break;
				default:
					break;
			}			
		}		
	}
	
	/**
	 * @name系统参数设置
	 */
	public function arg() {
		$in = &$this->in;
		if ($in ['ajax']) {	//处理ajax请求
			switch ($in['ajax']) {
				case 'check_var':
					
					break;
				case 'savevalue':
					$in['var'] && $in['var'] = substr($in['var'],6);
					$_setting = D ('Setting');
					if ($in['var'] && !empty($in['var'])) {
						$data = array(
							'var' => $in['var'],
							'value' => $in['value']
						);							
						if (false !== $_setting->save($data)) {
							//更新配置文件
							$_setting->cacheAll();
							die($data['value']);
						}
						
					}
					echo '';
					break;
				default:
					break;
			}
			
		} else {	//处理普通请求			
			!$in['setting'] && $in ['_tablename'] = 'setting';
			switch ($in['do']) {
				case 'add':	//添加
					$in ['tpl'] = 'add_arg';
					$this->assign('forward',U('fset/arg?do=manage'));									
					if ($this->ispost()) {
						$_setting = D ('Setting');	
						if (false !== $_setting->add($in['info'])) {
							//同时更新配置文件
							if(!$_setting->cacheAll()){
								$this->message('<font class="red">配置文件更新失败！</font>',U('fset/arg?do=manage'));
							}
							$this->message('<font class="green">保存成功！</font>',U('fset/arg?do=manage'));							
						} else {
							$this->message('<font class="red">保存失败！</font>');
						}
					}
					$this->display('add_arg');
					break;
				case 'edit':		//编辑
					$in ['tpl'] = 'edit_arg';
					$this->assign('forward',U('fset/arg?do=manage'));
					$this->addData();
					//同时更新配置文件
					$_setting = D ('Setting');
					if(!$_setting->cacheAll()){
						$this->message('<font class="red">配置文件更新失败！</font>',U('fset/arg?do=manage'));
					}
					break;
				case 'manage':	//管理
					$in ['tpl'] = 'manage_arg';	
					if ($in['q']) {	//关键字查询筛选
						$search = array();
						$search['q'] = $in['q'];
						if ($in['field']) {
							$in ['where'] = " `{$in['field']}` LIKE '%{$in['q']}%'";
							$search['field'] = $in['field'];
						}
						$this->assign('search',$search);
					}
					//获取参数标识
					$_setting = D ('Setting');
					$grouparray = $_setting->field("`key`")->group("`key`")->findAll();
					$group = array();
					if(is_array($grouparray)){
						foreach($grouparray as $k=>$v){
							$group[]=$v["key"];
						}
						$this->assign('group',$group);
					}
					if($in['orderkey']){
						$in['where'] = "`key` = '".$in['orderkey']."'";
						$this->assign('orderkey',$in['orderkey']);
					}
					$this->manageData();
					break;
				case 'delete':	//删除
					$this->deleteData();
					break;
				default:
					break;
			}			
		}
	}


 	/**
	 * @name关键词链接
	 */
	public function keylink() {
		$in = &$this->in;
		if ($in ['ajax']) {	//处理ajax请求
			switch ($in['ajax']) {				
				case 'saveword':
					$in['id'] && $in['id'] = substr($in['id'],5);
//					dump($in);exit;					
					$_kl = D ('Keylink');
					if ($in['id']) {
						$data = array(
							'word' => $in['word']							
						);
						$where['id'] = $in['id'];
						if (false !== $_kl->where($where)->save($data)) {
							$_kl->cache();
							die($data['word']);
						}
					}
					echo '';
					break;
				case 'saveurl':
					$in['id'] && $in['id'] = substr($in['id'],4);
//					dump($in);exit;
					$_kl = D ('Keylink');
					if ($in['id']) {
						$data = array(
							'url' => $in['url']							
						);
						$where['id'] = $in['id'];
						if (false !== $_kl->where($where)->save($data)) {
							$_kl->cache();
							die($data['url']);
						}
					}
					echo '';
					break;
				case 'savesort':
					$in['id'] && $in['id'] = substr($in['id'],5);
//					dump($in);exit;
					$_kl = D ('Keylink');
					if ($in['id']) {
						$data = array(
							'sort' => $in['sort']							
						);
						$where['id'] = $in['id'];
						if (false !== $_kl->where($where)->save($data)) {
							$_kl->cache();
							die($data['sort']);
						}
					}
					echo '';
					break;
				default:
					break;
			}

		} else {	//处理普通请求
			$in ['_tablename'] = 'keylink';
			switch ($in['do']) {
				case 'add':	//添加
					$in ['tpl'] = 'add_keylink';
					$this->assign('forward',U('fset/keylink?do=manage'));
					if ($this->ispost()) {
						$_kl = D ('Keylink');
						if (false !== $_kl->add($in['info'])) {
							$_kl->cache();
							$this->message('<font class="green">保存成功！</font>',U('fset/keylink?do=manage'));
						} else {
							$this->message('<font class="red">保存失败！</font>');
						}
					}
					$this->display('add_keylink');
					break;
				case 'edit':		//编辑
					$in ['tpl'] = 'edit_keylink';
					//如果是全局参数，则同时更新config.inc.php文件
					$this->assign('forward',U('fset/keylink?do=manage'));
					$this->addData();
					break;
				case 'manage':	//管理
					$in ['tpl'] = 'manage_keylink';
					if ($in['q'] && $in['q'] != '请输入关键字') {	//关键字查询筛选
						$search = array();
						$search['q'] = $in['q'];
						if ($in['field']) {
							$in ['where'] = " `{$in['field']}` LIKE '%{$in['q']}%'";
							$search['field'] = $in['field'];
						}
						$this->assign('search',$search);
					}
					$in['order'] = '`sort` ASC';
					$this->manageData();
					break;
				case 'delete':	//删除
					$this->deleteData();
					break;
				case 'cache':  //更新缓存
					$_kl = D ('Keylink');
					$_kl->cache();
					$this->message('<font class="green">缓存更新完毕！</font>',U('fset/keylink?do=manage'));
					break;
				default:
					break;
			}
		}
	}
	
	/**
	*@name TODO更新参数缓存
	*/
	public function cacheset(){
		$in = &$this->in;
		$_setting = D ('Setting');
		$_setting->cacheAll();
	}
	
	/**
	 * @name常规的添加、更新操作
	 *
	 */
	public function addData() {
		$in = &$this->in;
		if ($this->ispost()) {
			if (! $in ['_tablename'])
				$this->message ( '没有指定操作表！' );
			$name = $in ['_tablename']; //数据表名
			//		die($this->getInTableName($name));
			$_m = D ( parse_name($name,1) ); //实例化表模型类
			$_keyid = $_m->getPk ();
			//用create()创建数据对象，以可以使用系统内置的数据自动验证功能以及令牌验证功能
			$in['info'][C ('TOKEN_NAME')] = $in[C ('TOKEN_NAME')];
			if ( $_m->create ( $in ['info'] ) ) {
				if (! empty ( $in ['info'] [$_keyid] )) { //更新
					$keyid = $_m->save ();
				} else { //添加
					$keyid = $_m->add ();
					if ($keyid) $in['info'][$_keyid] = $keyid;
				}
				if (false !== $keyid) { //添加数据
					if (method_exists ( $_m, 'cache' )) { //调用缓存处理;
						$_m->cache ( ($in['info'][$_keyid] ? $in['info'][$_keyid] : $keyid), $in ['info'] );
					}
					//返回处理信息
					if ($in ['ajax'])
						$this->ajaxReturn ( $in ['info'], '记录保存成功！', 1, 'json' );
					else if($in ['_tablename'] == 'menu')
						$this->message ( '记录保存成功！', '', 0, false);
					else
						$this->message ( '记录保存成功！' );
				} else {
					//返回处理信息
					if ($in ['ajax'])
						$this->ajaxReturn ( '', $_m->getError () . '<br />数据保存失败！', 1, 'json' );
					else
						$this->message ( $_m->getError () . '<br />数据保存失败！' );
				}
			} else {
				if ($in ['ajax'])
					$this->ajaxReturn ( '', $_m->getError (), 1, 'json' );
				else
					$this->message ( $_m->getError ().'记录保存失败！' );
			}
		}
		//获取数据
		if (!empty($in['_tablename'])) {
			$name = $in ['_tablename']; //数据表名
			$_m = D ( parse_name($name,1) ); //实例化表模型类
			$_keyid = $_m->getPk ();
			if ( $in [$_keyid] ) { //编辑
				$keyid = $in [$_keyid] ;
				$data = $_m->find ( $keyid );
				if (isset($data['parentid']) && $data['parentid']>0) {
					$this->assign('parent_data',$_m->find($data['parentid']));
				}
				$this->assign ( 'data', $data );
			}
		}
		if (!empty($in['tpl'])) {
			$this->display ( $in ['tpl'] );
		} else {
			$this->display();
		}
	}


	/**
	 * @name常规的删除操作、如果此方法不适用，则可以在对应的action中override
	 */
	public function deleteData() {
		$in = &$this->in;
		if (! $in ['_tablename'])
			$this->message ( '没有指定操作表！' );
		$name = $in ['_tablename']; //数据表名
		//		die($this->getInTableName($name));
		$_m = D ( parse_name($name,1) ); //实例化表模型类
		$_keyid = $_m->getPk ();
		$_model = D ( $name );
		//安全起见，必须包含删除的记录的主键，或者删除条件
		$option = array ();
		if ($in ['order']) {
			$option['order'] = &$in['order'];
		}
		if ($in [$_keyid] ) { //主键筛选
			if (is_array($in[$_keyid])) {
				if (!empty($in [$_keyid])) {
					$option ['where'] = " `{$_keyid}` IN (".implode(',', $in[$_keyid]) .")";
				}
			} else {
				$option ['where'] = array ($_keyid => $in [$_keyid] );
			}
		}
		if ($in ['where']) {
			if (!empty($option ['where'])) {
				@$option['where'] = array_merge($in['where'],$option ['where']);
			} else {
				$option['where'] = &$in['where'];
			}
		}
		if (! empty ( $option )) {
			if (false !== $_m->delete($option)) {
				if (method_exists ( $_m, 'cache' )) { //删除缓存
					if (is_array($in[$_keyid])) {
						if (!empty($in [$_keyid])) {
							foreach ($_keyid as $k) {
								$_m->cache ( $k , null );
							}
						}
					} else if (is_numeric($in [$_keyid])) {
						$_m->cache ( $_keyid , null );
					}
				}
				$this->message('删除成功！');
			} else {
				$this->message($_m->getError() . '删除失败！');
			}
		} else {
			$this->message ( '参数错误，没有指定删除条件！' );
		}
	}

	/**
	 * @name数据列表
	 */
	public function manageData() {
		$in = &$this->in;
		if (! $in ['_tablename'])
			$this->message ( '没有指定操作表！' );
		$name = $in ['_tablename']; //数据表名

		$_m = D ( parse_name($name,1) ); //实例化表模型类
		$_keyid = $_m->getPk ();

		//操作条件
		$option = array ();
		if ($in ['order']) {
			$option['order'] = &$in['order'];
		} else {
			$option['order'] = "`{$_keyid}` DESC ";
		}
		if ( $in [$_keyid] ) { //主键筛选
			$option ['where'] = array ($_keyid => $in [$_keyid] );
		}
		if ($in ['where']) {
			$option['where'] = &$in['where'];
		}

		//获取数据
		//初始化分页类
		$data = array ();

		//统计记录数
		$data ['count'] = $_m->where ( $option['where'] )->count ();

		$_GET ['p'] = &$in ['p']; //分页类中会用到$_GET
		$Page = new Page ( $data ['count'], $this->getPageSize ( 'pagesize' ) );

		//分页代码
		$data ['pages'] = $Page->show ();

		//当前页数据
		$data ['info'] = $_m->limit ( $Page->firstRow . ',' . $Page->listRows )->select ($option);
		$this->assign ( 'data', $data );
		if (!empty($in['tpl'])) {
			$this->display ( $in ['tpl'] );
		} else {
			$this->display();
		}
	}
	public function space(){
		$in = &$this->in;
		$space_size = (int)C("SPACE_SIZE");//初始设置空间大小
		$space_use  = $this->getDirSize(dirname($_SERVER['SCRIPT_FILENAME']).'/');//已使用空间

		$surplus = $space_size- $space_use;//剩余空间大小
		if($surplus < 0){$surplus = 0;}
		$surplus_float = number_format(( $surplus / $space_size ) * 100,2,".","");
		
		$data['space_size'] = $this->getRealSize($space_size);
		$data['space_use'] = $this->getRealSize($space_use);
		$data['surplus'] = $this->getRealSize($surplus);
		$data['surplus_float'] = $surplus_float . "%";
		
		$this->assign("data",$data);
		$this->display();
	}

	public function getDirSize($dir)
    {
        $handle = opendir($dir);
        while (false!==($FolderOrFile = readdir($handle))){
            if($FolderOrFile != "." && $FolderOrFile != ".."){
                if(is_dir("$dir/$FolderOrFile")){
                    $sizeResult += $this->getDirSize("$dir/$FolderOrFile");
                }
                else{
                    $sizeResult += filesize("$dir/$FolderOrFile");
                }
            }
        }
        closedir($handle);
        return $sizeResult;
    }
    // 单位自动转换函数
    public function getRealSize($size)
    {
        $kb = 1024;         // Kilobyte
        $mb = 1024 * $kb;   // Megabyte
        $gb = 1024 * $mb;   // Gigabyte
        $tb = 1024 * $gb;   // Terabyte
        if($size < $kb){
            return $size." B";
        }
        else if($size < $mb){
            return round($size/$kb,2)." KB";
        }
        else if($size < $gb){
            return round($size/$mb,2)." MB";
        }
        else if($size < $tb){
            return round($size/$gb,2)." GB";
        }
        else{
            return round($size/$tb,2)." TB";
        }
    }
    
}
?>