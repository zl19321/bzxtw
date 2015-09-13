<?php
// +----------------------------------------------------------------------
// | FangFa Net [ http://www.fangfa.net ]
// +----------------------------------------------------------------------
// | File: FrbacAction.class.php
// +----------------------------------------------------------------------
// | Date: 2010-6-2
// +----------------------------------------------------------------------
// | Author: 龚承军 <kxgate@139.com>
// +----------------------------------------------------------------------
// | 文件描述:  操作模块管理及角色授权管理
// +----------------------------------------------------------------------

defined('IN_ADMIN') or die('Access Denied');
/**
 * @name 操作模块及角色授权管理
 *
 */
class FrbacAction extends FbaseAction {

	/**
	 * @name模块操作列表
	 */
	public function manage() {		
		$in = &$this->in;		
		$in['_tablename'] = 'acts';
		if(!isset($in['appname']))$in['appname'] = "admin";
		$in['where'] = "appname = '{$in['appname']}'";
		if ($this->ispost()) {	//关键字查询筛选
			$search = array();
			if ($in['q'] != '请输入关键字') {
				$in['q'] = urldecode($in['q']);
				$search['q'] = $in['q'];
				if ($in['field']) {
					$in['where'] .= " AND `{$in['field']}` LIKE '%{$in['q']}%'";
					$search['field'] = $in['field'];
				}
			}
			$this->assign('search',$search);
		}
		
		if (! $in['_tablename'])
			$this->message ( '没有指定操作表！' );
		$name = $in['_tablename']; //数据表名

		$_m = D ( parse_name($name,1) ); //实例化表模型类
		$_keyid = $_m->getPk ();

		//操作条件
		$option = array ();
		if ($in['order']) {
			$option['order'] = &$in['order'];
		} else {
			$option['order'] = "`{$_keyid}` DESC ";
		}
		if ( $in[$_keyid] ) { //主键筛选
			$option['where'] = array ($_keyid => $in[$_keyid] );
		}
		if ($in['where']) {
			$option['where'] = &$in['where'];
		}

		//获取数据
		//初始化分页类
		$data = array ();

		//统计记录数
		$data ['count'] = $_m->where ( $option['where'] )->count ();

		$_GET ['p'] = &$in['p']; //分页类中会用到$_GET
		$Page = new Page ( $data ['count'], $this->getPageSize ( 'pagesize' ) );

		//分页代码
		$data ['pages'] = $Page->show ();

		//当前页数据
		$data ['info'] = $_m->limit ( $Page->firstRow . ',' . $Page->listRows )->select ($option);
		$this->assign ( 'data', $data );
		$this->assign ( 'appname', $in['appname'] );
		if (!empty($in['tpl'])) {
			$this->display ( $in['tpl'] );
		} else {
			$this->display();
		}
	}
		
	/**
	 * @name添加模块
	 */
	public function add() {
		$in = &$this->in;
		if ($in['ajax']) $this->_add_ajax();
		$in['_tablename'] = 'acts';

		if ($this->ispost()) {
			$_acts = D ('Acts');
			if ($_acts->isActsExists($in['info'])) {
				$this->error( "“".$in['info']['controller'] ."”的操作 “".$in['info']['action']."”已经注册，无需再次注册到系统中！<br />");
			}
			
			if (! $in['_tablename'])
				$this->message ( '没有指定操作表！' );
            
            //柳文浩   2012-12-2   修改现有后台操作注册管理后需要手动添加超级管理员的操作权限的问题    
            $in['info']['allow']    =  'administrator';
                
			$name = $in['_tablename']; //数据表名
			$_m = D ( parse_name($name,1) ); //实例化表模型类
			$_keyid = $_m->getPk ();
			//用create()创建数据对象，以可以使用系统内置的数据自动验证功能以及令牌验证功能
			$in['info'][C ('TOKEN_NAME')] = $in[C ('TOKEN_NAME')];
			if ( $_m->create ( $in['info'] ) ) {
				if (! empty ( $in['info'] [$_keyid] )) { //更新
					$keyid = $_m->save ();
				} else { //添加
					$keyid = $_m->add ();
					if ($keyid) $in['info'][$_keyid] = $keyid;
				}
				if (false !== $keyid) { //添加数据
					if (method_exists ( $_m, 'cache' )) { //调用缓存处理;
						$_m->cache ( ($in['info'][$_keyid] ? $in['info'][$_keyid] : $keyid), $in['info'] );
					}
					//返回处理信息
					if ($in['ajax'])
						$this->ajaxReturn ( $in['info'], '记录保存成功！', 1, 'json' );
					else if($in['_tablename'] == 'menu')
						$this->message ( '记录保存成功！', '', 0, false);
					else
						$this->message ( '记录保存成功！' );
				} else {
					//返回处理信息
					if ($in['ajax'])
						$this->ajaxReturn ( '', $_m->getError () . '<br />数据保存失败！', 1, 'json' );
					else
						$this->message ( $_m->getError () . '<br />数据保存失败！' );
				}
			} else {
				if ($in['ajax'])
					$this->ajaxReturn ( '', $_m->getError (), 1, 'json' );
				else
					$this->message ( $_m->getError ().'记录保存失败！' );
			}
		}
		//获取数据
		if (!empty($in['_tablename'])) {
			$name = $in['_tablename']; //数据表名
			$_m = D ( parse_name($name,1) ); //实例化表模型类
			$_keyid = $_m->getPk ();
			if ( $in[$_keyid] ) { //编辑
				$keyid = $in[$_keyid] ;
				$data = $_m->find ( $keyid );
				if (isset($data['parentid']) && $data['parentid']>0) {
					$this->assign('parent_data',$_m->find($data['parentid']));
				}
				$this->assign ( 'data', $data );
			}
		}
		$this->assign ( 'appname', isset($in['appname'])?$in['appname']:"admin" );
		if (!empty($in['tpl'])) {
			$this->display ( $in['tpl'] );
		} else {
			$this->display();
		}
	}
	
	/**
	 * @name处理添加模块操作时候的ajax请求
	 */
	protected function _add_ajax() {
		$in = &$this->in;
		switch ($in['ajax']) {
			default:
				break;
		}
	}
	
	/**
	 * @name编辑模块
	 */
	public function edit() {
		$in = &$this->in;
		$in['_tablename'] = 'acts';
		$in['tpl'] = 'edit';
		
		if ($this->ispost()) {
			if (! $in['_tablename'])
				$this->message ( '没有指定操作表！' );
			$name = $in['_tablename']; //数据表名
			//		die($this->getInTableName($name));
			$_m = D ( parse_name($name,1) ); //实例化表模型类
			$_keyid = $_m->getPk ();
			//用create()创建数据对象，以可以使用系统内置的数据自动验证功能以及令牌验证功能
			$in['info'][C ('TOKEN_NAME')] = $in[C ('TOKEN_NAME')];
			if ( $_m->create ( $in['info'] ) ) {
				if (! empty ( $in['info'] [$_keyid] )) { //更新
					$keyid = $_m->save ();
				} else { //添加
					$keyid = $_m->add ();
					if ($keyid) $in['info'][$_keyid] = $keyid;
				}
				if (false !== $keyid) { //添加数据
					if (method_exists ( $_m, 'cache' )) { //调用缓存处理;
						$_m->cache ( ($in['info'][$_keyid] ? $in['info'][$_keyid] : $keyid), $in['info'] );
					}
					//返回处理信息
					if ($in['ajax'])
						$this->ajaxReturn ( $in['info'], '记录保存成功！', 1, 'json' );
					else if($in['_tablename'] == 'menu')
						$this->message ( '记录保存成功！', '', 0, false);
					else
						$this->message ( '记录保存成功！' );
				} else {
					//返回处理信息
					if ($in['ajax'])
						$this->ajaxReturn ( '', $_m->getError () . '<br />数据保存失败！', 1, 'json' );
					else
						$this->message ( $_m->getError () . '<br />数据保存失败！' );
				}
			} else {
				if ($in['ajax'])
					$this->ajaxReturn ( '', $_m->getError (), 1, 'json' );
				else
					$this->message ( $_m->getError ().'记录保存失败！' );
			}
		}
		//获取数据
		if (!empty($in['_tablename'])) {
			$name = $in['_tablename']; //数据表名
			$_m = D ( parse_name($name,1) ); //实例化表模型类
			$_keyid = $_m->getPk ();
			if ( $in[$_keyid] ) { //编辑
				$keyid = $in[$_keyid] ;
				$data = $_m->find ( $keyid );
				if (isset($data['parentid']) && $data['parentid']>0) {
					$this->assign('parent_data',$_m->find($data['parentid']));
				}
				$this->assign ( 'data', $data );
			}
		}
		if (!empty($in['tpl'])) {
			$this->display ( $in['tpl'] );
		} else {
			$this->display();
		}
	}
	
	/**
	 * @name取消模块注册
	 */
	public function delete() {
		$in = &$this->in;
		$in['_tablename'] = 'acts';
		
		if (! $in['_tablename'])
			$this->message ( '没有指定操作表！' );
		$name = $in['_tablename']; //数据表名
		$_m = D ( parse_name($name,1) ); //实例化表模型类
		$_keyid = $_m->getPk ();
		$_model = D ( $name );
		//安全起见，必须包含删除的记录的主键，或者删除条件
		$option = array ();
		if ($in['order']) {
			$option['order'] = &$in['order'];
		}
		if ($in[$_keyid] ) { //主键筛选
			if (is_array($in[$_keyid])) {
				if (!empty($in[$_keyid])) {
					$option['where'] = " `{$_keyid}` IN (".implode(',', $in[$_keyid]) .")";
				}
			} else {
				$option['where'] = array ($_keyid => $in[$_keyid] );
			}
		}
		if ($in['where']) {
			if (!empty($option['where'])) {
				@$option['where'] = array_merge($in['where'],$option['where']);
			} else {
				$option['where'] = &$in['where'];
			}
		}
		if (! empty ( $option )) {
			if (false !== $_m->delete($option)) {
				if (method_exists ( $_m, 'cache' )) { //删除缓存
					if (is_array($in[$_keyid])) {
						if (!empty($in[$_keyid])) {
							foreach ($_keyid as $k) {
								$_m->cache ( $k , null );
							}
						}
					} else if (is_numeric($in[$_keyid])) {
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
	 * @name角色授权
	 * 后台勾选以后自动生效
	 */
	public function power() {
		$in = &$this->in;
		if (!$in['role_id']) {
			$this->error('没有选择要授权的角色！');
		}
		$_acts = D ('Acts');

		// $where = array(
		// 		"appname" =>'front',
		// 	);
		// $act_data = $_acts->where($where)->select();
		// print_r($act_data);exit;

		if ($this->ispost()) {
			if ($in['dosubmit'] == 1) {
				$result = $_acts->ajaxSave($in);
			}elseif($in['dosubmit'] == 2) {
				$result = $_acts->ajaxCategory($in);
			}elseif($in['dosubmit'] == 3) {	//新增批量操作
				$result = $_acts->ajaxSaveAll($in);
			}
			if(null === $result) {
				die('e');
			} else if (false === $result) {
				die('false');	
			} else {
				die('true');
			}
		}
		//所有操作文件
		//SELECT GROUP_CONCAT(DISTINCT(controller)) FROM `fangfa_acts` GROUP BY appname;
		$group = $_acts->field(array("GROUP_CONCAT(DISTINCT(controller)) AS 'group'"))->group("appname")->order('controller')->findAll();
		if(strpos($group[1]['group'],'frbac')){//通过FRBAC来判断是前台还是后台的控制器组合
			$group['admin'] = $group[1];
			$group['front'] = $group[0];
		}elseif(strpos($group[0]['group'],'frbac')){
			$group['admin'] = $group[0];
			$group['front'] = $group[1];
		}
		unset($group[0],$group[1]);
		if (!empty($group) && is_array($group)) {
			foreach ($group as $k=>$v) {
				!empty($v) && $data[$k] = explode(',', $v['group']);				
			}
		}
		unset($group,$k,$v);
		$data = array_map('array_flip', $data);//交换键与值
		$datas = array();
		if (is_array($data)) {
			foreach ($data as $k=>$v) {	//操作文件下的所有动作
				if (is_array($v) && !empty($v)) {
					foreach ($v as $controller=>$t) {
						$where = array(
							'appname' => $k,
							'controller' => $controller,
						);
						$datas[$k][$controller]['info'] = $_acts->where($where)->findAll();
						//控制器名	
						$datas[$k][$controller]['contorllername'] = $datas[$k][$controller]['info'][0]['controllername'];				
					}
				} 
			}
		}
		$category = D ( 'Category' )->where("type='normal'")->relation( 'model' )->field ( "`catid` AS `id`,`name`,controller,`modelid`,`parentid`,`type`,`sort`,`url`,`islock`,`topcatid`,permissions" )->order ( "`sort` ASC,`catid` ASC " )->select ();

		foreach ($category as $k => $t){
			$tablename = ucfirst ( substr($t ['controller'],1) );
			$_m = D ( $tablename );
			$setting = $_m->setting($t, $t['catid'], $t['parentid']);
			$setting = explode("<!--start permissions-->",$setting['body']);
			$setting = explode("<!--end permissions-->",$setting[1]);
			$category[$k]['setting'] = $setting[0];
		}
		// echo '<pre>';
		// print_r($datas);exit;
		$this->assign('data',$datas);
		$this->assign('isadmin',isset($in['isadmin'])?$in['isadmin']:1);
		$this->assign('role_data',D('Role')->find($in['role_id']));
		$this->assign("category",$category);		
		$this->display();
	}
	
	/**
	 * @name 自动注册模块
	 */
	public function Autoadd(){
		$in = &$this->in;
		if(isset($in['appname'])){
			if(($re = $this->_auto_add(FANGFACMS_ROOT.$in['appname'].'/lib/Action',$in['appname']))!==true){
				$this->message ( $re );
			}
			$this->message ( '模块更新成功！' ,U("frbac/manage?appname=".$in['appname']));
		}
		else{
			if(($re = $this->_auto_add(FANGFACMS_ROOT.'admin/lib/Action','admin'))!==true){
				$this->message ( $re );
			}
			//跳转执行前台模块的更新
			echo '<script>self.location.href="'. U('frbac/Autoadd?doupdate=1&appname=front') .'"</script>';
		}
	}
	
	/**
	 * @name 自动注册
	 */
	private function _auto_add($path,$app){
		$in = &$this->in;
		$file_path = realpath($path);//模块路径
		$path = rtrim($path,'/');
		if(file_exists($path)){
			$file_array = array();
			$file_array = glob($path.'/*.php');//遍历后台文件
			foreach($file_array as $k){
				$file[] = substr(basename($k),0,strpos(basename($k),'.'));
			}
			$method_name = array();
			foreach($file as $v){
				if($v!='EmptyAction' && $v!='FbaseAction'){//过滤掉EmptyAction和基础类
					if($app=='front'){
						define ( 'IN', true );//前台模块入口
						include($path.'/'.$v.'.class.php');//加载前台模块文件
					}
					$reflector = new ReflectionClass($v);//反射类
					foreach($reflector->getMethods() as $method){//遍历方法
						$reflector_method = new ReflectionMethod($v,$method->getName());//获取该方法的属性
						if($method->getName()!='_empty'){//过滤掉空方法
							$Classname = $reflector_method->getDeclaringClass();//得到该方法对应的已定义类名
							$Class_docname = $reflector->getDocComment();//得到该类的别名
							if($reflector_method->isPublic() && $Classname->getName()==$v){//判断是否是PUBLIC和本类中的方法，去掉继承
								$note = $method->getDocComment();//取得该方法的注释
								$reg  = '/@name[\s]?([\w\x{4e00}-\x{9fa5}]+)/u';
								preg_match($reg,$note,$mat);//取得该方法名称
								if(is_array($mat) && !empty($mat)){
									$notes = $mat[1];
								}else{
									$notes = '';
								}
								preg_match($reg,$Class_docname,$matc);//取得类名
								if(is_array($matc) && !empty($matc)){
									$c_name = $matc[1];
								}else{
									$c_name = '';
								}
								$method_name[strtolower(substr($v,0,-6))][] = array('name' => $method->getName(),
																		'note' => $notes, 
																		'cname' => $c_name,
																		);
							}
						}
					}
				}
			}
			//写入数据库
			if($in['doupdate']){
				$_acts = D ('Acts');
				foreach($method_name as $k=>$v){
					if(is_array($v)){
						foreach($v as $key=>$val){
							$name = $val['note'];
							$appname = $app;
							$controller = $k;
							$c_name = $val['cname'];
							$action = $val['name'];
							$array = array('name'=>$name,
											'appname' => $appname,
											'controller' => $controller,
											'action' => $action,
											'controllername' => $c_name);
							if($appname=='admin'){
								$array['allow'] = 'administrator';
							}
							//读数据库
							$data = $_acts->where("`controller`='{$controller}' AND `action`='{$action}' AND `appname`='{$appname}'")->find();
							if(is_array($data) && !empty($data)){//有数据
								$array['aid'] = $data['aid'];
								$keyid = $_acts->save($array);//更新
							}else{
								$keyid = $_acts->add($array);//添加
							}
							if(false === $keyid){
								//$this->message ( $_acts->getError () . ' '.$controller.' 模块中 '.$action.' 方法更新失败！' );
								$re_string = $appname . '-' . $controller.' 模块中 '.$action.' 方法更新失败！';
								return $re_string;
							}
						}
					}
				}
				//$this->message ( '所有模块更新成功！' );
				return true;
			}
		}
	}
	
	/**
	 * @name  编辑角色菜单
	 **/
	public function menu(){
		$in = &$this->in;
		$_role = D("Role");
		$_menu = D("Menu");
		$role = $_role->where("role_id=".$in['role_id'])->find();
		$this->childmenu(1,$data);
		$role_name = $_role->where("role_id=" . $in['role_id'])->getField("name");
		$role_menu = array();
		foreach ($data as $t){
			if (in_array($role_name,$t['rolenames'])) {
				$role_menu[] = $t['menuid'];
			}
		}
		$str = "";
		$top_menu = $_menu -> where("parentid=1")->order("menuid asc")->findAll();
		$menu_tpl = "<table border='0' cellpadding='0' cellspacing='0' id='menus' ><tr>";
		
		foreach ($top_menu as $k =>$v){
			
			if($_SESSION['userdata']['roles'][0] != 'developer'){
				if (!in_array($_SESSION['userdata']['roles'][0],$v['rolenames'])){
					continue;
				}
			}
			
			$check = "";
			if(in_array($v['menuid'], $role_menu)){
				$check = "checked";
			}
			$menu_tpl .= "<td id='top".$k."' valign='top'><ul><li><input type='checkbox' name='menuid[]' value='". $v['menuid'] ."' $check/>".$v['name'];
			$menu_tpl .= $this -> menulist($v['menuid'], $role_menu);
			
			$menu_tpl .= "</li></ul></td>";
		}
		$menu_tpl .= "</tr></table>";
		
		/*foreach ($menu as $k => $t) {
			menu_tpl
		}*/
		$this->assign("role", $role);
		$this->assign("menu_tpl", $menu_tpl);
		$this->display();
	}
	
	/**
	 * @name       得到所有的子菜单
	 * $parentid   为父级菜单的编号
	 * $menuid     为当前菜单的编号
	 * $role_menu  当前权限拥有的菜单
	 **/
	private function menulist( $parentid, $role_menu, $menuid = 0 ){
		$in = &$this->in;
		$_menu = D("Menu");
		$where = "parentid=".$parentid ;
		if ($menuid != 0) 
			$where .= " and menuid=".$menuid;
		$menu = $_menu  -> where($where)->order("menuid asc")->findAll();
		$str = "";
		foreach ($menu as $k => $v){
			if($_SESSION['userdata']['roles'][0] != 'developer'){
				if (!in_array($_SESSION['userdata']['roles'][0],$v['rolenames'])){
					continue;
				}
			}
			$check = "";
			if(in_array($v['menuid'], $role_menu)){
				$check = "checked";
			}
			$str .= "<li><input type='checkbox' name='menuid[]' value='". $v['menuid'] ."' $check/>".$v['name'];
			
			$str .= $this-> menulist($v['menuid'], $role_menu);
			$str .= "</li>";
		}
		
		if ($str != '') {
			$return = "<ul>". $str ."</ul>";
		}
		return $return;
	}
	/**
	 * @name 保存角色用油的菜单权限
	 * */
	public function savemenu(){
		$in = &$this->in;
		
		//$data['menu'] = implode(",", $in['menuid']);
		$_role = D("Role");
		$_menu = D("Menu");
		$role_name = $_role->where("role_id=".$in['role_id'])->getField("name");
		$db = C("DB_PREFIX");
		$data = array();
		$this->childmenu(1,$data);
		foreach ($data as $k => $t) {
			$rolenames = array();
			if (!empty($t['rolenames'])) {
				$rolenames = $t['rolenames'];
			}
			if(in_array($t['menuid'],$in['menuid']) && !in_array($role_name,$rolenames)){
				$rolenames[] = $role_name;
			}elseif (!in_array($t['menuid'],$in['menuid']) && in_array($role_name,$rolenames)) {
				$new = array();
				foreach ($rolenames as $c){
					if ($c != $role_name) {
						$new [] = $c;
					}
				}
				unset($rolenames);
				$rolenames = $new;
 			}
			if (!empty($rolenames)) {
				$rolenames = var_export ($rolenames, true );
			}else {
				$rolenames = "";
			}
			$_menu->query('update '.$db.'menu set rolenames="'.$rolenames.'" where menuid='.$t['menuid']);
		}
		$_menu->cacheAll();
		$this->message("操作成功！",__ROOT__."/admin.php?m=frole&a=manage&modelid=6");
		
	}
	/**
	 *@name 得到子级栏目
	 * */
	private function childmenu($parentid, & $data){
		$_menu =  D("Menu");
		$menu = $_menu->where("parentid=".$parentid)->findAll();
		foreach ($menu as $k => $t){
			$count = $_menu->where("parentid=".$t['menuid'])->count();
			$data [] = $t;
			if ($count > 0) {
				$this->childmenu($t['menuid'], $data);
			}
			
		}
	}
}
?>