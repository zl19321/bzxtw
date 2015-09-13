<?php
// +----------------------------------------------------------------------
// | FangFa Net [ http://www.fangfa.net ]
// +----------------------------------------------------------------------
// | File: CategoryModel.class.php
// +----------------------------------------------------------------------
// | Date: 2010-5-13
// +----------------------------------------------------------------------
// | Author: fangfa <1364119331@qq.com>
// +----------------------------------------------------------------------
// | 文件描述: 栏目Model
// +----------------------------------------------------------------------

class CategoryModel extends RelationModel {

	/**
	 * 关联定义
	 * @var unknown_type
	 */
	protected $_link = array (
		'model' => array (
			'mapping_type' => BELONGS_TO,
			'class_name' => 'Model',
			'foreign_key' => 'modelid',
			'mapping_fields' => 'modelid,name'
		)
	);
	/**
	 * 栏目的子孙栏目
	 * @var array
	 */
	protected $child_id_arr = array();

	/**
	 * 栏目的父辈栏目
	 * @var array
	 */
	protected $parent_id_arr = array();

	/**
	 * 操作的栏目ID
	 * @var unknown_type
	 */
	protected $_catid = '';

	/**
	 * 菜单数组
	 * @var unknown_type
	 */
	protected $_data = array();

	/**
	 * 是否自动格式化数据（栏目URL）
	 *
	 * @var boolean
	 */
	public $auto_format = false;

	protected $_topcatid = '';
	/**
	 * 验证数据有效性
	 * 当$field 为空，$data为数组的时候验证所有字段的有效性
	 * 当$field 不为空，则$field是要验证的字段名，$data 则是要验证的字段值
	 * @param mixed $data
	 */
	public function validate($data, $field = '') {
		if (empty($field) && is_array($data)) { 	//检查所有字段的有效性
			$t = true;
			foreach ($data as $k=>$v) {//进行递归验证
				if (false === $this->validate($data,$k)) $t = false;
			}
			return $t;
		} else if ($field == 'catdir') { //检查文件夹名称
			if ($data['type'] == 'normal' || empty($data['type'])) {
			    //检查是否与系统目录同名冲突
    			$systemDir = C ('SYSTEM_DIR');
    			if (in_array($data['catdir'],$systemDir)) {
    				$this->error .= "栏目目录与系统目录冲突<br />";
    				return false;
    			}
    			//不能以f开头，避免与控制器冲突
    			if (strtolower ( substr ( $data['catdir'], 0, 1 ) ) == 'f' || !parent::regex($data['catdir'],'catdir')) {
    				$this->error .= "栏目目录不能以f开头<br />";
    				return false;
    			}

    			//正则验证
    			if (!preg_match('/^[\w\-]+$/', $data['catdir'])) {
    				$this->error .= "只能包含英文半角字母、数字、下划线以及减号<br />";
    				return false;
    			}

    			//不能与前台的routes冲突
    			$front_routes_conf = FANGFACMS_ROOT . '/front/Conf/routes.php';
    			if (file_exists($front_routes_conf)) {
    				$routes = include($front_routes_conf);
    				if (isset($routes['catdir']) || isset($routes['catdir@'])) {
    					$this->error .= "栏目目录与路由发生冲突<br />";
    					return false;
    				}
    			}
				//if (!empty($data['parentid'])) {
				//	$parent_data = $this->field("`catdir`")->find($data['parentid']);
				//	$catdir = $parent_data['catdir'] . $data['catdir'] .'/';
				//}
				$catdir = &$data['catdir'];
				$category_data = $this->where ( "`catdir`='{$catdir}' " . ($data['catid'] ? ' AND catid!='.$data['catid'] : '') )->find ();
				if (is_array ( $category_data )) {
					$this->error .= "文件夹名称不符合要求或者已经就存在！<br />";
					return false;
				} else {
					return true;
				}
			} else if($data['type'] == 'page') { //检查页面链接
				$url = $data['url'] . C('URL_HTML_SUFFIX');
				$category_data = $this->where ( "`url`='{$url}' " . ($data['catid'] ? ' AND catid!='.$data['catid'] : ''))->find ();
				if (is_array ( $category_data )) {
					$this->error .= "该名称的页面已经存在！<br />";
					return false;
				} else if (!preg_match('/^[A-Za-z0-9]+$/', $data['url'])) {
					$this->error .= "链接格式不正确！<br />";
					return false;
				} else {
					return true;
				}
			}
			return true;
		} else if ($field == 'type') {	//栏目类型
			if (!in_array($data['type'],array('normal','page','link'))) {
				$this->error .= '栏目类型错误！<br />';
				return false;
			}
			return true;
		} else if ($field == 'ishtml') {
			;
		} else if ($field == 'modelid') {	//检查模型是否存在切可用！
			if ($data['modelid'] > 0) {
				$_model = D ('Model');
				$model_data = $_model->field("`modelid`,`name`")->find($data['modelid']);
				if (!is_array($model_data)) {
					$this->error .= '栏目模型不存在<br />';
					return false;
				}
			}
			return true;
		} else if ($field == 'url') { //检查绑定的二级域名
			return true;
		}
	}

	/**
	 * 查询的后置方法，会在find()之后自动以引用方式调用
	 */
	protected function _after_find(&$result, $options = '') {
		parent::_after_find ( $result, $options );
        
        //mouselwh add 2012-12-25 获取所有model内容
        $_model = D('Model');
        $result['model'] = $_model->selectonesModel('modelid = '.$result['modelid']);
        
		if (isset ( $result ['setting'] )) {
			$result ['setting'] = eval ( "return {$result ['setting']};" );
		}
		if (isset ( $result ['permissions'] )) {
			$result ['permissions'] = eval ( "return {$result ['permissions']};" );
		}
		if ($this->auto_format && isset($result ['url'])) {
		    if (substr(ltrim($result ['url'], ' \\/'), 0, 11) == 'HTTP_SERVER') {
				$result ['url'] = str_replace('HTTP_SERVER', __ROOT__, $result ['url']);
			} elseif (substr($result['url'],0,7) != 'http://') {
				$result ['url'] = __ROOT__ . '/' . $result ['url'];
			}
		}
	}

	/**
	 * findAll 或者 select 之后自动调用的数据处理方法
	 *
	 * @param array $resultSet
	 */
	protected function _after_select(&$resultSet, $options = '') {
		parent::_after_select ( $resultSet, $options );
		if (is_array ( $resultSet )) {
			foreach ( $resultSet as $k => $v ) {
				//添加栏目管理权限。根据菜单判断权限
				if(isset($v['id'])){
					$_menu = D("Menu");
					$rolenames = $_menu->where("keyid='cat_".$v['id']."'")->getField("rolenames");
					if(!empty($rolenames) && $_SESSION['userdata']['roles'][0] != 'developer' && !empty($_SESSION['userdata'])){
						$rolenames = eval ( "return {$rolenames};" );
						if (!in_array($_SESSION['userdata']['roles'][0],$rolenames)){
							unset($resultSet[$k]);
						}
					}elseif(empty($rolenames)){
						unset($resultSet[$k]);
					}
				}
				if (isset ( $resultSet [$k] ['setting'] )) {
					$resultSet [$k] ['setting'] = eval ( "return {$resultSet [$k] ['setting']};" );
				}
				if (isset ( $resultSet [$k] ['permissions'] )) {
					$resultSet [$k] ['permissions'] = eval ( "return {$resultSet [$k] ['permissions']};" );
				}
				if ($this->auto_format && isset($resultSet [$k] ['url'])) {
					if (substr(ltrim($resultSet[$k]['url'], ' \\/'), 0, 11) == 'HTTP_SERVER') {
						$resultSet [$k] ['url'] = str_replace('HTTP_SERVER', __ROOT__, $resultSet[$k]['url']);
					} elseif (substr($resultSet [$k]['url'],0,7) != 'http://') {
				        $resultSet [$k] ['url'] = __ROOT__ . '/' . $resultSet  [$k] ['url'];
				    }
				}
			}
		}
	}

	/**
	 * add方法insert之前处理方法，会在insert之前自动以引用方式调用
	 *
	 * @param array $data
	 */
	protected function _before_insert(&$data, $options) {
		parent::_before_insert ( $data, $options );
		//过滤与默认值设置
		!empty($data ['name']) && $data ['name'] = htmlspecialchars ( trim ( $data ['name'] ) );
		!empty($data ['thumb']) && $data ['thumb'] = htmlspecialchars ( trim ( $data ['thumb'] ) );
		!empty($data ['catdir']) && $data ['catdir'] = htmlspecialchars ( trim ( $data ['catdir'] ) );
		!empty($data ['url']) && $data ['url'] = htmlspecialchars ( trim ( $data ['url'] ) );
		!empty($data ['template']) && $data ['template'] = htmlspecialchars ( trim ( $data ['template'] ) );
		$data ['sort'] = (int)$data['sort']>0 ? intval($data['sort']) : 1;
		!empty($data ['setting']) && $data ['setting'] = var_export ( $data ['setting'], true );
		!empty($data ['permissions']) && $data ['permissions'] = var_export ( $data ['permissions'], true );
		return true;
	}
	
    /***
	 * 插入后得到插入的catid，更新栏目的顶级栏目编号 topcatid 
	 */
	protected function _after_insert($data, $options) {
		$this->gettopcatid($data['catid'], $data['parentid']);
		$in['topcatid'] = $this->_topcatid;
		$in['catid'] = $data['catid'];
        //print_r($in);exit;
		parent::query ("UPDATE `".C("DB_PREFIX")."category` SET `topcatid`='".$in['topcatid']."' WHERE catid='".$in['catid']."' ");
      
	}
	/**
	 * save方法update数据之前的处理方法，会在update数据之前自动以引用方式调用
	 * @param array $data
	 */
	protected function _before_update(&$data, $options) {
		parent::_before_update ( $data, $options );
		$this->gettopcatid($data['catid'], $data['parentid']);
		$data['topcatid'] = $this->_topcatid;
		return $this->_before_insert ( $data );
	}


	/**
	 * 添加栏目
	 * @param unknown_type $data
	 */
	public function add($data = array()) {
		if (! is_array ( $data ) || ($data ['type'] == 'normal' && ! $data ['modelid'])) {
			$this->error .= '无效数据！<br />';
			return false;
		}
		$data['url'] = $this->getUrl($data);
		if($data ['cattype'] == 'cla'){
			return $this->normalAdd($data);//添加分类
		}
        

        
		if ($this->{$data ['type'] . 'Add'} ( $data )) {  //添加菜单，更新缓存
			$data = $this->_data;  //获取现在的栏目数据
//			dump($data);
			$_menu = D ( 'Menu' );
			//查找父级菜单，确定要添加的菜单信息
            
            //mouselwh 2012-12-25 副表挂靠上级ID
            if($data['controller'] == 'fsidetable'){
                $menu_parentid = 10; //副表发布管理，固定ID
            }else{
                $menu_parentid = 4; //内容发布管理，固定ID
            }
			
			if ($data ['parentid'] > 0) {
				$menu_parent_data = $_menu->field ( "`menuid`" )->where ( "`keyid`='cat_" . $data ['parentid'] . "'" )->find ();
				if ($menu_parent_data ['menuid']) {
					$menu_parentid = $menu_parent_data ['menuid'];
				}
			}
            

            
			//添加菜单
			if ($data ['type'] == 'page') {
				$data['controller'] = 'fpage';
			}

            $url = ($data['type'] == 'link' ? '#' : "?m={$data ['controller']}&a=manage&catid={$this->_catid}");

			
			$menu_data = array (
				'parentid' => $menu_parentid,
				'name' => $data ['name'],
                'ename' => $data ['ename'],
				'url' => $url,
				'isshow' => '1',
				'sort' => '1',
				'isopen' => '0',
				'keyid' => 'cat_' . $this->_catid ,
				'rolenames' => array ( 0 => 'administrator',),
			);
			if (false === $_menu->add ( $menu_data )) {
				$this->error .= '新增栏目内容管理菜单出错，栏目添加事务失败！<br />';
				$this->rollback ();
				return false;
			}
//			if ($data ['ishtml']) { //创建物理文件夹
//				$dirpath = HTML_PATH . $data['catdir'];
//				mk_dir($dirpath);
//			}
			//更新栏目缓存
//			dump($catid);dump($data);

			//校正URL
			if ($data['type'] == 'normal' && strpos('http://', $data['url']) === false) {
				$data['url'] = __ROOT__ . '/' . $data['url'];
			}
			$this->cache($this->_catid,$data);
			if ($data['parentid']) { //更新所有父栏目缓存
				$parent_id_arr = $this->getParentIdsArr($this->_catid,$data);
				if (is_array($parent_id_arr)) {
					foreach ($parent_id_arr as $v) {
						$this->cache($v['catid']);
					}
				}
			}
			//更新菜单缓存
			D('Menu')->cacheAll();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * 添加常规栏目，内部调用
	 * @param unknown_type $data
	 */
	protected function normalAdd($data = array()) {
		if (! $this->validate ( $data )) {
			return false;
		}
		//根据父类信息，修改栏目自身的信息，包括	arrparentid,catdir,url
//		if ($data ['parentid']) { //如果有父类，则先查找出父类的信息
//			$parent_data = $this->find ( $data ['parentid'] );
//		}
		//controller
		$_model = D ( 'Model' );
		$model_data = $_model->relation ( true )->find ( $data ['modelid'] );
		if (! is_array ( $model_data )) {
			$this->error .= '模型不存在！<br />';
			$result = false;
		} else {
			$data ['controller'] = $model_data ['module'] ['controller'];
		}
		//动态栏目只有一级虚拟的文件夹，没有子文件夹
//		$controller = A ( ucfirst ( $data ['controller'] ), 'front' );
		//seo信息
		empty($data['seotitle']) && $data['seotitle'] = $data['name'] . ' - {stitle}';
		empty($data['seokeywords']) && $data['seokeywords'] = $data['name'];
		empty($data['seodescription']) && $data['seodescription'] = $data['description'] ? $data['description'] : $data['name'];
		//导图处理
		if (!empty($_FILES['thumb_img']['name'])) {
			import ( 'ORG.Net.UploadFile' );
			//允许上传类型
			$fileext = explode ( '|', C ( 'UPLOAD_IMAGES_ALLOWEXT' ) );
			//允许上传大小
			$allowSize = C ( 'UPLOAD_MAXSIZE' );
			$allowSize = intval ( $allowSize ) * 1024; //KB
			$upload = get_instance_of ( 'UploadFile' ); // 实例化上传类
			$upload->maxSize = $allowSize;
			$upload->allowExts = $fileext;
			$upload->savePath = C ( 'UPLOAD_DIR' ) .'images/';
			if(!$upload->upload()) { // 上传错误提示错误信息
				$this->error .= $upload->getErrorMsg() . '<br />';
				return false;
			}else{ // 上传成功获取上传文件信息
				$thumbinfo =  $upload->getUploadFileInfo();
				$data['thumb'] = 'images/' . $thumbinfo[0]['savename'];
			 }
		}
		unset($data['catid']);
		//添加记录
		$catid = parent::add ( $data );
		if (false !== $catid) {
			$this->_catid = $catid;
			$this->_data = $data;
			return $catid;
		}
		$this->error .= '新增栏目记录失败！<br />';
		return false;
	}

	/**
	 * 添加单页栏目，内部调用
	 * @param unknown_type $data
	 */
	protected function pageAdd($data = array()) {
		if (! $this->validate ( $data )) {
			return false;
		}
		//根据父类信息，修改栏目自身的信息，包括	arrparentid,catdir,url
//		if ($data ['parentid']) { //如果有父类，则先查找出父类的信息
//			$parent_data = $this->find ( $data ['parentid'] );
//			$data['catdir'] = $parent_data['catdir'];
//		}
		$data ['controller'] = 'fpage';
		$data ['type'] = 'page';
		$data ['ishtml'] = '1';
		//seo信息
		//seo信息
		empty($data['seotitle']) && $data['seotitle'] = $data['name'] . ' - {stitle}';
		empty($data['seokeywords']) && $data['seokeywords'] = $data['name'];
		empty($data['seodescription']) && $data['seodescription'] = $data['description'] ? $data['description'] : $data['name'];
		//导图处理
		if (!empty($_FILES['thumb_img']['name'])) {
			import ( 'ORG.Net.UploadFile' );
			//允许上传类型
			$fileext = explode ( '|', C ( 'UPLOAD_IMAGES_ALLOWEXT' ) );
			//允许上传大小
			$allowSize = C ( 'UPLOAD_MAXSIZE' );
			$allowSize = intval ( $allowSize ) * 1024; //KB
			$upload = get_instance_of ( 'UploadFile' ); // 实例化上传类
			$upload->maxSize = $allowSize;
			$upload->allowExts = $fileext;
			$upload->savePath = C ( 'UPLOAD_DIR' ) .'images/';
			if(!$upload->upload()) { // 上传错误提示错误信息
				$this->error .= $upload->getErrorMsg() . '<br />';
				return false;
			}else{ // 上传成功获取上传文件信息
				$thumbinfo =  $upload->getUploadFileInfo();
				$data['thumb'] = 'images/' . $thumbinfo[0]['savename'];
			 }
		}

		//添加记录
		$catid = parent::add ( $data );
		if (false !== $catid) {
			$this->_catid = $catid;
			$this->_data = $data;
			return $catid;
		}
		$this->error .= '新增栏目记录失败！<br />';
		return false;
	}

	/**
	 * 添加外链栏目，内部调用
	 * @param unknown_type $data
	 */
	protected function linkAdd($data = array()) {
		$data ['type'] = 'link';
		$data ['ishtml'] = '0';
		//导图处理
		if (!empty($_FILES['thumb_img']['name'])) {
			import ( 'ORG.Net.UploadFile' );
			//允许上传类型
			$fileext = explode ( '|', C ( 'UPLOAD_IMAGES_ALLOWEXT' ) );
			//允许上传大小
			$allowSize = C ( 'UPLOAD_MAXSIZE' );
			$allowSize = intval ( $allowSize ) * 1024; //KB
			$upload = get_instance_of ( 'UploadFile' ); // 实例化上传类
			$upload->maxSize = $allowSize;
			$upload->allowExts = $fileext;
			$upload->savePath = C ( 'UPLOAD_DIR' ) .'images/';
			if(!$upload->upload()) { // 上传错误提示错误信息
				$this->error .= $upload->getErrorMsg() . '<br />';
				return false;
			}else{ // 上传成功获取上传文件信息
				$thumbinfo =  $upload->getUploadFileInfo();
				$data['thumb'] = 'images/' . $thumbinfo[0]['savename'];
			 }
		}
		//添加记录
		$catid = parent::add ( $data );
		if (false !== $catid) {
			$this->_catid = $catid;
			$this->_data = $data;
			return $catid;
		}
		$this->error .= '新增栏目记录失败！<br />';
		return false;
	}


	/**
	 * 更新栏目数据
	 * @param unknown_type $data
	 */
	public function save($data = array()) {
		if (! $data ['catid']) {
			$this->error .= '未指定要更新的栏目！';
			return false;
		}
		//检查数据有效性
//        dump($data);
		$category_data = $this->find ( $data ['catid'] );
		if (!empty($_FILES['thumb_img']['name'])) { //处理导图上传
			import ( 'ORG.Net.UploadFile' );
			//允许上传类型
			$fileext = explode ( '|', C ( 'UPLOAD_IMAGES_ALLOWEXT' ) );
			//允许上传大小
			$allowSize = C ( 'UPLOAD_MAXSIZE' );
			$allowSize = intval ( $allowSize ) * 1024; //KB
			$upload = get_instance_of ( 'UploadFile' ); // 实例化上传类
			$upload->maxSize = $allowSize;
			$upload->allowExts = $fileext;
			$upload->savePath = C ( 'UPLOAD_DIR' ) .'images/';
			if(!$upload->upload()) { // 上传错误提示错误信息
				$this->error .= $upload->getErrorMsg() . '<br />';
				return false;
			}else{ // 上传成功获取上传文件信息
				$thumbinfo =  $upload->getUploadFileInfo();
				$data['thumb'] = 'images/' . $thumbinfo[0]['savename'];
			 }
		}
		//修改部分字段信息
		isset($data ['name']) && $category_data ['name'] = $data ['name'];
        isset($data ['ename']) && $category_data ['ename'] = $data ['ename'];
		isset($data ['thumb']) && $category_data ['thumb'] = $data ['thumb'];
		isset($data ['parentid']) && $category_data ['parentid'] = $data ['parentid'];
		isset($data ['description']) && $category_data ['description'] = $data ['description'];
		isset($data ['seotitle']) && $category_data ['seotitle'] = trim($data ['seotitle']);
		isset($data ['seokeywords']) && $category_data ['seokeywords'] = $data ['seokeywords'];
		isset($data ['seodescription']) && $category_data ['seodescription'] = $data ['seodescription'];
		isset($data ['template']) && $category_data ['template'] = $data ['template'];
		isset($data ['permissions']) && $category_data ['permissions'] = $data ['permissions'];
		isset($data ['setting']) && $category_data ['setting'] = $data ['setting'];
		isset($data ['sort']) && $category_data ['sort'] = $data ['sort'];
		$category_data ['islock'] = isset($data ['islock']) ? '1' : '0';
		isset($data ['catdir']) && $category_data ['catdir'] = $data ['catdir'];
		if ($category_data['type'] == 'normal' && $data['url']) {
            $category_data ['url'] = $this->getUrl($data);
        } else {
            $data ['url'] && $category_data ['url'] = $data ['url'];
        }
		//更新栏目
		if (false === parent::save($category_data)) {
			return false;
		}
		//更新菜单
		$_menu = D ('Menu');
		$menu_data = $_menu->where(array("keyid"=>'cat_'.$category_data['catid']))->find();
		$menu_data['name'] = $category_data['name'];
		$_menu->save($menu_data);
		$_menu->cache($menu_data['menuid'],$menu_data);
		//更新栏目缓存

		//校正URL
		if ($category_data['type'] == 'normal' && strpos('http://', $category_data['url']) === false) {
			$category_data['url'] = __ROOT__ . '/' . $category_data['url'];
		}
		$this->cache($category_data['catid'],$category_data);
		return true;
	}

	/**
	 * 删除目录，目录下面所有的记录信息业将删除
	 * @param $catid
	 */
	public function delete($catid) {
		$catid = intval ( $catid );
		$category_data = F ( 'category_'.$catid );
		if (is_array ( $category_data ) ) {
			//子栏目
			$child_id_arr = $category_data['childrenidarr_self'];
			import ( 'ORG.Io.Dir' );
			$_dir = get_instance_of ( 'Dir' );
			//系统目录
			$systemDir = C ('SYSTEM_DIR');
			foreach ($child_id_arr as $v) {
				$v_category_data = array();
				$v_category_data = F ('category_'.$v);
				//删除数据库记录
				parent::delete ( $v_category_data['catid'] );
				//删除栏目下的资料
				if ($v_category_data['type']=="page") {
					D("page") ->where("catid=".$v_category_data['catid'] )->delete();
				}elseif ($v_category_data['type']=="normal" && in_array($v_category_data['modelid'],array(1,2,3,4,5))){
					D("Content") ->execute ("delete from ".C("DB_PREFIX")."content where catid=".$v_category_data['catid']);
				}
				elseif ($v_category_data['modelid'] == "8" && $v_category_data['type']=="normal"){//留言删除
					D("Guestbook") ->where("catid=".$v_category_data['catid'] )->delete();
				}
				elseif ($v_category_data['modelid'] == "13" && $v_category_data['type']=="normal"){//活动报名
					D("Activity") ->where("catid=".$v_category_data['catid'] )->delete();
				}
				//
				//删除物理目录及下面的实体静态文件
				if ($v_category_data['type'] == 'normal') {  //内部栏目才需要删除实体目录
				    if ($v_category_data['ishtml'] && !in_array($v_category_data ['catdir'],$systemDir) && !empty($v_category_data['catdir'])) {
    					$catdir = HTML_PATH . $v_category_data ['catdir'];
    					if (is_dir ( $catdir )) {
    						$_dir->delDir ( $catdir );
    					}
    				}
				} else {
				    if (file_exists(FANGFACMS_ROOT . $v_category_data['url'])) {
        				@unlink(FANGFACMS_ROOT . $v_category_data['url']);
        			}
				}
				//删除菜单，更新菜单缓存
				$_menu = D ( 'Menu' );
				$_menu->where ( "`keyid`='cat_{$v_category_data['catid']}'" )->delete ();
				$_menu->cacheAll();
				//删除缓存
				F('category_'.$v_category_data['catid'],NULL);
				if ($v_category_data['parentid']>0) { //更新所有父栏目缓存
					$parent_id_arr = $this->getParentIdsArr($v_category_data['catid'], $v_category_data);
					if (is_array($parent_id_arr)) {
						foreach ($parent_id_arr as $v) {
							$this->cache($v['catid']);
						}
					}
				}
			}
			return true;
		}
		return false;
	}

	/**
	 * 删除栏目导图
	 * @param int $catid
	 */
	public function delete_thumb($catid) {
		$catid = intval ( $catid );
		$category_data = $this->find ( $catid );
		if ( is_array ( $category_data )) {
			$thumb = $category_data ['thumb'];
			$thumb = C ( 'UPLOAD_DIR' ) .  $thumb;
			//更新记录
			$category_data['thumb'] = '';
			if (false !== parent::save($category_data)) {
				//删除实体
				if (file_exists ( $thumb )) {
					@unlink ( $thumb );
				}
				//更新栏目缓存
				$this->cache($catid);
				return true;
			}
		}
		return false;
	}
	
	/*
	*得到顶级栏目编号
	*@param int $catid 分类ID
	*@param int $parentid 父级栏目编号
	*/
	protected function gettopcatid($catid,$parentid){
		$parentid = empty($parentid) ? 0 : $parentid;
		if ($parentid != 0) {
			$category = $this->where("catid=".$parentid)->find ();
			if ($category['parentid'] == 0) {
				$this->_topcatid = $category['catid'];
			}else {
				$this->gettopcatid($category['catid'],$category['parentid']);
			}
		}else {
			$this->_topcatid = $catid;
		}
	}
	
	/*
	*设置topcatid字段值
	*@param int $catid 分类ID
	*@param int $topcatid 需要绑定的栏目ID
	*/
	public function settopcatid($catid,$topcatid){
		if(is_array($catid)){
			foreach($catid as $k){
				$category_data = $this->find ( $k );
				if ( is_array ( $category_data )){
					$o_tid = $category_data['topcatid'];
					if($o_tid){
						$o_tid_array = split(',',$o_tid);
						if(!in_array($topcatid,$o_tid_array)){
							$category_data['topcatid'] = ','.$topcatid;
						}
					}else{
						$category_data['topcatid']=$topcatid;
					}
					if (false !== parent::save($category_data)){
					//更新栏目缓存
					$this->cache($k);
					}
				}
			}
			return true;
		}else{
			$catid = intval ( $catid );
			if(!is_array($topcatid)) return false;
			//$topcatid = intval ( $topcatid );
			$category_data = $this->find ( $catid );
			if ( is_array ( $category_data )) {
				$tid = implode(',',$topcatid);
				if(empty($topcatid)) $tid = '';
				$category_data['topcatid'] =  $tid;
				if (false !== parent::save($category_data)){
					//更新栏目缓存
					$this->cache($catid);
					return true;
				}
			}
			return false;
		}
	}
	
	

	/**
	 * 根据数组里面的信息组合出栏目的url地址
	 *
	 * $option = array(
	 * 	'type' => 'normal'  // or 'page'   or  'link'
	 * 	'parentid' => '',
	 * 	'catdir' => '',
	 * 	'catid' => ''
	 * )
	 */
	public function getUrl($option) {
	    $url = '';
	    switch ($option['type']) {
	        case 'normal':
    			if ($option ['parentid'] > 0) {
    				$parent_data = $this->find ( $option ['parentid'] );
    				if (strpos('http://',$parent_data ['url'])) { // 二级域名
    					$url = $parent_data['url']  . '/' . $option['catdir'] . '/';
    				} else {
    				    $url = $option ['catdir'] . '/';
    				}
    			} else {
    			    $url = $option ['catdir'] . '/';
    			}
	            break;
	        case 'page':
    			$url = $option ['url'] . C ( 'URL_HTML_SUFFIX' );
	            break;
	        case 'link':
	            $url = $option ['url'];
	            break;
	    }
	    return $url;
	}





	/**
	 * 更新栏目缓存
	 *
	 * @param int $catid
	 */
	public function cache($catid, $data) {
		if (empty ( $data ) || !is_array ( $data )) {
		    $this->auto_format = true;
			$data = $this->find ( $catid );
		}
		$data ['catid'] = $catid;
		//本栏目的父类、子类的array和ids表现形式
		$data ['parentidarr'] = $this->getParentIdsArr ( $catid, $data );
		$data ['childrenidarr'] = $this->getChildIdsArr ( $catid );
		$data ['parentids'] = ! empty ( $data ['parentidarr'] ) ? implode ( ',', $data ['parentidarr'] ) : '';
		$data ['childrenids'] = ! empty ( $data ['childrenidarr'] ) ? implode ( ',', $data ['childrenidarr'] ) : '';
		//本栏目的父类、子类包括本身的array和ids表现形式
		//本栏目的父类、子类包括本身的array和ids表现形式
		$data ['parentidarr_self'] = $data ['parentidarr'];
		$data ['childrenidarr_self'] = $data ['childrenidarr'];
		array_push($data ['parentidarr_self'], $catid);
		array_unshift($data ['childrenidarr_self'],$catid);
		$data ['parentids_self'] = implode ( ',', $data ['parentidarr_self'] );
		$data ['childrenids_self'] = implode ( ',', $data ['childrenidarr_self'] );

		//格式化链接栏目url
		if ($data['type']=='link' && substr(ltrim($data['url'], ' \\/'), 0, 11) == 'HTTP_SERVER') {
			$data['url'] = str_replace('HTTP_SERVER', __ROOT__, $data['url']);
		}

		//缓存文件名  category_{$catid}.php
		return F ( 'category_' . $catid, $data );
	}

	/**
	 * 更新所有栏目缓存信息
	 */
	public function cacheAll() {
	    $this->auto_format = true;
		$data = $this->findAll ();
		if (is_array ( $data )) {
			foreach ( $data as $v ) {
				$this->cache ( $v ['catid'], $v );
			}
		}
		return true;
	}


	/**
     * 获取指定栏目的所有子孙栏目IDS
	 * @param int $catid
	 */
	public function getParentIdsArr($catid, $data) {
		if (!empty($this->parent_id_arr)) {
			$this->parent_id_arr = array();
		}
		return $this->getParentIdArr($catid, $data);
	}

	/**
	 * 递归获取指定栏目的父辈栏目ID数组
	 * @param unknown_type $catid
	 * @param unknown_type $data
	 */
	private function getParentIdArr($catid, $data) {
		$ids = array ();
		$catid = intval ( $catid );
		if (empty ( $data )) {
			$data = $this->field ( "`catid`,`parentid`" )->find ( $catid );
		}
		$data['parentid'] >0 && $parend_data = $this->field ( "`catid`,`parentid`" )->where ( "`catid`='{$data['parentid']}'" )->findAll ();
		if (is_array($parend_data)) {
			foreach ($parend_data as $v) {
				array_unshift ($this->parent_id_arr, $v['catid']);
				$this->getParentIdArr($v['catid'],$v);
			}
		}
		return $this->parent_id_arr;
	}

	/**
	 * 获取指定栏目的所有子孙栏目IDS
	 * @param int $catid
	 */
	public function getChildIdsArr($catid) {
		if (!empty($this->child_id_arr)) {
			$this->child_id_arr = array();
		}
		return $this->getChildrenIdArr($catid);
	}
	/**
	 * 递归获取指定栏目的所有子孙栏目IDS
	 * @param int $catid
	 * @return array
	 */
	private function getChildrenIdArr($catid) {
		$ids = array ();
		$catid = intval ( $catid );
		$catid >0 && $data = $this->field ( "`catid`,`parentid`" )->where ( "`parentid`='{$catid}'" )->findAll ();
		if (is_array($data)) {
			foreach ($data as $v) {
				$this->child_id_arr[] = $v['catid'];
				$this->getChildrenIdArr($v['catid']);
			}
		}
		return $this->child_id_arr;
	}
	
	/**
	 * 递归获取指定栏目的所有子分类IDS
	 * @param int $catid
	 * @return array
	 */
	private function getClaChildrenIdArr($catid,$cattype='cla') {
		$ids = array ();
		$catid = intval ( $catid );
		$catid >0 && $data = $this->field ( "`catid`,`parentid`" )->where ( "`parentid`='{$catid}' AND `cattype`='{$cattype}'" )->findAll ();
		if (is_array($data)) {
			foreach ($data as $v) {
				$this->child_id_arr[] = $v['catid'];
				$this->getClaChildrenIdArr($v['catid'],$cattype);
			}
		}
		return $this->child_id_arr;
	}


	/**
	 * 从缓存的数据获取$catid的所有同类模型的栏目
	 * @param unknown_type $catid
	 */
	public function getSameCategorys($catid) {
		$data = F ('category_'.$catid);
		$sames = $this->field("`catid`,`modelid`")->where("`modelid`='{$data['modelid']}' AND `cattype`='cat'")->findAll();
		$result = array();
		foreach ($sames as $v) {
			$result[] = F ('category_'.$v['catid']);
		}
		return $result;
	}
	
	/**
	 * $topcatid 分类的绑定目录ID，根据此获取顶级分类的ID
	 * 从缓存的数据获取顶级分类数据
	 * @param unknown_type $topcatid
	 */
	public function getTopclassify($topcatid) {
		$categorys_all = D('Category')->field("`catid`,`topcatid`")->where("`parentid`=0 AND `cattype`='cla'")->findAll();
		if(!empty($categorys_all)){
			$array = array();
			$cid_array = array();
			foreach($categorys_all as $k=>$v){
				if($v['topcatid']){
					$array = split(',',$v['topcatid']);
					if(in_array($topcatid,$array)){
						$cid_array[] = $v['catid']; 
					}
				}
			}
		}else{
			return false;
		}
		$categorys = array();
		if(!empty($cid_array)){
			foreach($cid_array as $k){
				$categorys[] = D('Category')->field("`catid`")->where("`catid`={$k} AND `parentid`=0 AND `cattype`='cla'")->find();
			}
		}
		
		$data = array();
		if(is_array($categorys) && !empty($categorys)){
			foreach($categorys as $v){
				$data[] = F ('category_'.$v['catid']);
			}
			
			//$sames = $this->field("`catid`,`modelid`")->where("`modelid`='{$data['modelid']}' AND `cattype`='cla'")->findAll();
			//$sames = $this->getClaChildrenIdArr($catid);
			//$result = F ('category_'.$v);
		}
		return $data;
		
	}

	/**
	 * 获取指定栏目的子栏目树，不包括栏目本身
	 * return array(
	 * 	array(),  //child  1
	 * 	array(),  //child  2
	 * 	array(
	 * 	  'catid'=>'',
	 * 	  'modelid'=>'',
	 * 	  'name'=>'',
	 * 		...
	 * 	  'child' = array(),
	 *
	 * 	),   //child 3
	 * );
	 *
	 * @param int $parentid  栏目ID
	 * @return array $data
	 */
	public function getTreeByParentId($parentid) {
		//要返回的数据
        $data = array();
        if ($parentid > 0) {
            $data[0] = F ('category_'.$parentid);
            if (!empty($data[0]['childrenidarr'])) {
                foreach ($data[0]['childrenidarr'] as $v) {
                    $data[] = F ('category_'.$v);
                }
            }
            $parent_id = $data[0]['parentid'];
            $data = list_sort_by($data,'sort','asc');
            $data = array_to_tree($data,'catid','parentid', 'child', false, $parent_id);
            $return = $data[0];
        } else {
            $categorys = $this->field("`catid`")->order("`sort` ASC")->findAll();
            if (is_array($categorys)) {
                $data = array();
                foreach ($categorys as $cat) {
                    $data[] = F('category_'.$cat['catid']);
                }
            }
            $return = array_to_tree($data,'catid','parentid');
        }
        return $return;
	}
    
    /**
     * 
     * @name获取对应模型的栏目ID
     * @return catidArray   
     * 
     */
    
    public function getCatidUseModelid($modelid){
        
        $data = array();
        if(isset($modelid)){
            if(is_array($modelid)){
                $modelidstr = implode(',',$modelid);
                $data = $this->where('modelid in ('.$modelidstr.')')->select();
            }else{
                $data = $this->where('modelid = '.$modelid)->select();
            }
            return $data;
        }else{
            return false;
        }
        

    } 
      

}
?>