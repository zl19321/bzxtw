<?php
// +----------------------------------------------------------------------
// | FangFa Net [ http://www.fangfa.net ]
// +----------------------------------------------------------------------
// | File: TagModel.class.php
// +----------------------------------------------------------------------
// | Date: 2010-5-7
// +----------------------------------------------------------------------
// | Author: fangfa <1364119331@qq.com>
// +----------------------------------------------------------------------
// | 文件描述: 菜单数据模型类
// +----------------------------------------------------------------------

class MenuModel extends Model{
	
	/**
	 * 数据表名，子类会自动引用该属性
	 * @var string
	 */
	protected $tableName = 'menu';
	
	/**
	 * 手动指定数据表模型名称，子类会自动引用该属性
	 * @var string
	 */
	protected $name = 'Menu';
	
	/**
	 * add方法insert之前处理方法，会在insert之前自动以引用方式调用
	 * @param unknown_type $data
	 */
	protected function _before_insert(&$data) {
		if (!empty($data ['rolenames'])) {
			$data ['rolenames'] = var_export ( $data ['rolenames'], true );
		} else {
			$data['rolenames'] = '';
		}		
	}
	
	/**
	 * save方法update数据之前的处理方法，会在update数据之前自动以引用方式调用
	 * @param unknown_type $data
	 */
	protected function _before_update(&$data) {
		$this->_before_insert ( $data );
	}
	
	/**
	 * 查询的后置方法，会在find()之后自动以引用方式调用
	 */
	protected function _after_find(&$result) {
		$result ['rolenames'] && $result ['rolenames'] = eval ( "return {$result ['rolenames']};" );
	}
	
	/**
	 * findAll 或者 select 之后自动调用的数据处理方法
	 * @param unknown_type $resultSet
	 */
	protected function _after_select(&$resultSet) {		
		if (is_array ( $resultSet )) {
			foreach ( $resultSet as $k => $v ) {
				if (isset($resultSet [$k] ['rolenames'])) {
					$resultSet [$k] ['rolenames'] = eval ( "return {$resultSet [$k] ['rolenames']};" );						
				}
			}
		}
	}
	
	/**
	 * 保存菜单缓存
	 * @param int $menuid
	 * @param array $data
	 *
	 */
	public function cache($menuid,$data = array()) {
		if ($menuid) {
			if ($data == null) { //删除缓存
				F('menu_'.$menuid,null,ALL_CACHE_PATH . 'menu/');
				return true;
			} else {
				if (!is_array($data) || empty($data['menuid']))
					$data = $this->find($menuid);
				$child = $this->where("`parentid`='{$data['menuid']}'")->find();
				if (is_array($child)) $data['hasChildren'] = true;
				else if ($data['target'] == 'left') $data['hasChildren'] = true;
				else $data['hasChildren'] = false;
				return F('menu_'.$menuid,$data,ALL_CACHE_PATH . 'menu/');
			}
			
		}
		return false;
	}
	
	
	public function cacheAll() {
		//先清空
		import ( 'ORG.Io.Dir' );
		$_dir = get_instance_of ( 'Dir' );
		$_dir->clearDir(ALL_CACHE_PATH . 'menu/');
		//缓存数据
		$data = $this->findAll();
		foreach ($data as $d) {
			$this->cache($d['menuid'],$d);
		}
	}
	
	private $child_menu = array();
	
	/**
	 * 更新用户菜单缓存，同是返回角色的菜单json数据
	 * 
	 * 构造两级
	 * 
	 * @param string $rolename
	 */
	public function getRoleMenu($parentid, $rolenames) {
		$data = $this->field("`menuid`")->where("`parentid`='{$parentid}'")->order("`sort` ASC,`menuid` ASC")->findAll();
		$menu = array();
		if (is_array($data) && !empty($data)) {
			foreach ($data as $v) {
				//从缓存中读取数据
				$v = F('menu_'.$v['menuid'],'',ALL_CACHE_PATH . 'menu/');
				$access = false; //访问权限
				if (is_array($rolenames)) {
					foreach ($rolenames as $r) {
						if (in_array($r,$v['rolenames'])) $access = true;
					}
				} else {
					if (in_array($rolenames,$v['rolenames'])) $access = true;
				}
				if ($rolenames[0] == 'developer' ) $access = true;
				if (!$access) continue;
				$hasChild = $v['hasChildren'];  //判断是否包含子节点，该信息被预先缓存在数组中
				$m = array(
					'text' => $v['name'],
					'classes' => $hasChild ? 'folder' : 'file',
					'id' => $v['menuid'],
					'hasChildren' => $hasChild
				);				
				if (!empty($v['url']) && $v['url'] != '#') {					
					$m['text'] = '<a href="'.$v['url'].'" target="'.$v['target'].'">'.$v['name'].'</a>';
				}
				if ($hasChild && $v['isopen']) { //构造第二级的树，这样是为了保证在界面可以控制出示展开一级
					unset($m['hasChildren']);  //去掉父级的 hasChild属性，不然会生成一个 placeholder，  treeview的BUG
					$m['expanded'] = true;
					$childArr = $this->field("`menuid`")->where("`parentid`='{$v['menuid']}'")->order("`sort` ASC,`menuid` ASC")->findAll();
					foreach ($childArr as $secChild) {
						$secChild = F('menu_'.$secChild['menuid'],'',ALL_CACHE_PATH . 'menu/');
						
						$catid = intval(substr($secChild['keyid'], 4));  //获取栏目ID，如果是外链，且没有子栏目，则过滤掉
						if ($catid) {  //如果是连接，且没有子菜单，则不显示
							$cat = F('category_' . $catid, '', ALL_CACHE_PATH.'cache/');
							if ($cat['type'] == 'link' && empty($cat['childrenids'])) {
								continue;
							}
						}
				
						$secAccess = false;
						if (is_array($rolenames)) {
							foreach ($rolenames as $r) {
								if (in_array($r,$secChild['rolenames'])) $secAccess = true;
							}
						} else {
							if (in_array($rolenames,$secChild['rolenames'])) $secAccess = true;
						}
						if ($rolenames[0] == 'developer' ) $secAccess = true;
						if (!$secAccess) continue;
						$secHasChild = $secChild['hasChildren'];  //判断第二级是否包含子树(也就是是否包含第三级树)，该信息被预先缓存在数组中
						$secM = array(
							'text' => $secChild['name'],
							'classes' => $secHasChild ? 'folder' : 'file',
							'id' => $secChild['menuid'],
							'hasChildren' => $secHasChild
						);
						if (!empty($secChild['url']) && $secChild['url'] != '#') {
							$secM['text'] = '<a href="'.$secChild['url'].'" target="'.$secChild['target'].'">'.$secChild['name'].'</a>';							
						}
						is_array($secM ) && !empty($secM['text']) && $m['children'][] = $secM; 
					}
				}
				$menu[] = $m;  //构造数组
			}
		}
		return $menu;
	}
	
	/**
	 * 获取用户权限下的菜单列表，针对后台Frame theme2
	 * 
	 * @param string $rolename
	 */
	public function getRoleMenu2($parentid, $rolenames) {
		$data = $this->field("`menuid`")->where("`parentid`='{$parentid}'")->order("`sort` ASC,`menuid` ASC")->findAll();
		$html = '';
		if (is_array($data) && !empty($data)) {
			foreach ($data as $v) {
				//从缓存中读取数据
				$v = F('menu_'.$v['menuid'],'',ALL_CACHE_PATH . 'menu/');
				$access = false; //访问权限
				if (is_array($rolenames)) {
					foreach ($rolenames as $r) {
						if (in_array($r,$v['rolenames'])) $access = true;
					}
				} else {
					if (in_array($rolenames,$v['rolenames'])) $access = true;
				}
				if ($rolenames[0] == 'developer' ) $access = true;
				if (!$access) continue;
				$hasChild = $v['hasChildren'];  //判断是否包含子节点，该信息被预先缓存在数组中
				$m = array(
					'text' => $v['name'],
					'classes' => $hasChild ? 'fold' : '',
					'id' => $v['menuid'],
					'hasChildren' => $hasChild
				);				
				if (!empty($v['url']) && $v['url'] != '#') {					
					$m['text'] = '<a href="'.$v['url'].'" target="'.$v['target'].'">'.$v['name'].'</a>';
				} else $m['text'] = $v['name'];
				
				$html .= '<h3 class="menu_name ' . ($v['isopen']==1?'expand' : 'fold') .'">'.$m['text'].'</h3>';
				$html .= ($hasChild ? $this->getNode($m['id'], $rolenames, '1') : '');
			}
		}
		
		return $html;
	}
	
	/**
	 * 递归获取子菜单
	 * @param int $menuid
	 * @param array $rolenames
	 * @param int $level
	 * @return string
	 */
	protected function getNode($menuid, $rolenames, $level='')
	{
		$str = '<ul' . ($level ? ' class="menu_item">': '>');
		$childArr = $this->field("`menuid`")->where("`parentid`='{$menuid}'")->order("`sort` ASC,`menuid` ASC")->findAll();
		foreach ($childArr as $secChild) {
			$secChild = F('menu_'.$secChild['menuid'],'',ALL_CACHE_PATH . 'menu/');
			
			$catid = intval(substr($secChild['keyid'], 4));  //获取栏目ID，如果是外链，且没有子栏目，则过滤掉
			if ($catid) {  //如果是连接，且没有子菜单，则不显示
				$cat = F('category_' . $catid, '', ALL_CACHE_PATH.'cache/');
				if ($cat['type'] == 'link' && empty($cat['childrenids'])) {
					continue;
				}
			}
						
			$secAccess = false;  //判断权限
			if (is_array($rolenames)) {
				foreach ($rolenames as $r) {
					if (in_array($r,$secChild['rolenames'])) $secAccess = true;
				}
			} else {
				if (in_array($rolenames,$secChild['rolenames'])) $secAccess = true;
			}
			if ($rolenames[0] == 'developer' ) $secAccess = true;
			if (!$secAccess) continue;
			
			$secHasChild = $secChild['hasChildren']; //是否有子栏目
			if (empty($secChild['url'])) {
				$secChild['url'] = '#';
			}
			if ($secHasChild) {   //获取菜单是否展开还是收缩
				if ($secChild['isopen'] == 1) {
					$secChild['classes'] = 'expand';
				} else $secChild['classes'] = 'fold';
			} else $secChild['classes'] = '';
			
			$str .= '<li><a href="'.$secChild['url'].'" class="'. $secChild['classes'] . '"' . ((empty($secChild['url']) || $secChild['url'] == '#') ? '' : 'target="'.$secChild['target'].'"') . '>' . $secChild['name'] . '</a>';
			if($secHasChild) {
				$str .= $this->getNode($secChild['menuid'], $rolenames);
			}
			$str .= '</li>';
		}
		$str .= '</ul>';
		
		return $str;
	}
	
	/**
	 * 获取用户权限下的菜单列表，针对后台Frame theme3
	 * 
	 * @param string $rolename
	 */
	public function getRoleMenu3($parentid, $rolenames) {
		$data = $this->field("`menuid`")->where("`parentid`='{$parentid}'")->order("`sort` ASC,`menuid` ASC")->findAll();
		$return_html = array('top_menu' => '', 'sidebar_menu' => '');
		if (is_array($data) && !empty($data)) {
			foreach ($data as $k=>$v) {
				//从缓存中读取数据
				$v = F('menu_'.$v['menuid'],'',ALL_CACHE_PATH . 'menu/');
				$access = false; //访问权限
				if (is_array($rolenames)) {
					foreach ($rolenames as $r) {
						if (in_array($r,(array)$v['rolenames'])) $access = true;
					}
				} else {
					if (in_array($rolenames,$v['rolenames'])) $access = true;
				}
				if ($rolenames[0] == 'developer' ) $access = true;
				if (!$access) continue;
				$hasChild = $v['hasChildren'];  //判断是否包含子节点，该信息被预先缓存在数组中
				$m = array(
					'text' => $v['name'],
					'classes' => $hasChild ? 'fold' : '',
					'id' => $v['menuid'],
					'hasChildren' => $hasChild
				);
				if (empty($v['url'])) {
					$v['url'] = '#';
				}			
				$m['text'] = '<a href="'.$v['url'].'" ' . (($v['url'] || $v['url']=='#') ? "" : 'target="'.$v['target'].'"') .'>'.$v['name'].'</a>';
				
				$return_html['top_menu'] .= '<li '.($k==0 ? 'class="active"':'').'>'.$m['text'].'</li>';
				$return_html['sidebar_menu'] .= ($hasChild ? $this->getNode3($m['id'], $rolenames, $k) : '');
			}
		}
		
		return $return_html;
	}
	
	/**
	 * 递归获取子菜单,针对后台frame theme3
	 * @param int $menuid
	 * @param array $rolenames
	 * @param int $level
	 * @return string
	 */
	protected function getNode3($menuid, $rolenames, $k)
	{
		$str = '<ul class="menu_item '.($k==0 ? ' active' : '').'">';
		$childArr = $this->field("`menuid`")->where("`parentid`='{$menuid}'")->order("`sort` ASC,`menuid` ASC")->findAll();
		foreach ($childArr as $secChild) {
			$secChild = F('menu_'.$secChild['menuid'],'',ALL_CACHE_PATH . 'menu/');
			
			$catid = intval(substr($secChild['keyid'], 4));  //获取栏目ID，如果是外链，且没有子栏目，则过滤掉
			if ($catid) {  //如果是连接，且没有子菜单，则不显示
				$cat = F('category_' . $catid, '', ALL_CACHE_PATH.'cache/');
				if ($cat['type'] == 'link' && empty($cat['childrenids'])) {
					continue;
				}
			}
			
			$secAccess = false;  //判断权限
			if (is_array($rolenames)) {
				foreach ($rolenames as $r) {
					if (in_array($r,(array)$secChild['rolenames'])) $secAccess = true;
				}
			} else {
				if (in_array($rolenames,$secChild['rolenames'])) $secAccess = true;
			}
			if ($rolenames[0] == 'developer' ) $secAccess = true;
			if (!$secAccess) continue;
			
			$secHasChild = $secChild['hasChildren']; //是否有子栏目
			if (empty($secChild['url'])) {
				$secChild['url'] = '#';
			}
			if ($secHasChild) {   //获取菜单是否展开还是收缩
				if ($secChild['isopen'] == 1) {
					$secChild['classes'] = 'expand';
				} else $secChild['classes'] = 'fold';
			} else $secChild['classes'] = '';
			
			$str .= '<li><a href="'.$secChild['url'].'" class="'. $secChild['classes'] . '" '. (($secChild['url']=='#' || empty($secChild['url'])) ? "" : 'target="'.$secChild['target'].'"') .'>' . $secChild['name'] . '</a>';
			if($secHasChild) {
				$str .= $this->getNode($secChild['menuid'], $rolenames);
			}
			$str .= '</li>';
		}
		$str .= '</ul>';
		
		return $str;
	}
	
	/**
	 * 根据父菜单ID以及角色信息获取菜单树数据
	 * 
	 * @param int $parentid
	 * @param string $role
	 */
	public function getMenuDataTree ($parentid, $rolenames = '') {
		$data = $this->field("`menuid`")->where("`parentid`='{$parentid}'")->order("`sort` ASC,`menuid` ASC")->findAll();
		
		return $data;
	}
}