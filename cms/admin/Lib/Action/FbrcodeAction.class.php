<?php
// +----------------------------------------------------------------------
// | FangFa Net [ http://www.fangfa.net ]
// +----------------------------------------------------------------------
// | File: FbrcodeAction.class.php
// +----------------------------------------------------------------------
// | Date: 2013-4-9
// +----------------------------------------------------------------------
// | Author: 
// +----------------------------------------------------------------------
// | 文件描述: 二位码生成器
// +----------------------------------------------------------------------


defined ( 'IN_ADMIN' ) or die ( 'Access Denied' );
/**
 * @name 系统管理
 *
 */
class FbrcodeAction extends FbaseAction {
	/**
	 * @name初始化二维码数据
	 */
	protected function _initialize()
	{
		parent::_initialize();
		$in = &$this->in;
		$in['_tablename'] = 'brcode';
		$this->assign('status', array('0' => '未审核', '1' => '已审核'));
		$is_brcode = C("is_brcode");
        
		if(empty($is_brcode)){
             if($in['ajax'] == 1) die( '没有启用二维码功能！');
			$this->message ( '没有启用二维码功能！' );
		}
		//$this->assign('in', $this->in);
	}
	
	/**
	 * @name管理入口
	 */
	public function index()
	{
		$this->manage();
	}
	
	/**
	 * @name二维码管理
	 *
	 */
	public function manage()
	{
		$in = &$this->in;
		$where = array();
		if (isset($in['status']) && $in['status'] != 'all') {
			$where[] = ' `status`=' . $in['status'];
		}
		
		if (count($where) > 0) {
			$in['where'] = implode(' AND ', $where);
		}
		$in['order'] = '`id` DESC';
		
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
		$this->assign('upload_dir', C('UPLOAD_DIR'));

		//当前页数据
		$data ['info'] = $_m->limit ( $Page->firstRow . ',' . $Page->listRows )->select ($option);
		$this->assign ( 'data', $data );
		if (!empty($in['tpl'])) {
			$this->display ( $in ['tpl'] );
		} else {
			$this->display();
		}
	}
	
	/**
	 * @name添加、编辑 二维码
	 */
	public function add()
	{
		$in = &$this->in;
        $arr_size = array();
		for($size=1; $size<=10; $size++) 
			$arr_size[$size] = '尺寸：'.$size;
		$this->assign('size', $arr_size);

		$this->assign('upload_dir', C('UPLOAD_DIR'));
		if ($in['do']) {
			$_model = M('brcode');
			$data[$in['do']] = $in[$in['do']];
			$_model->where('id=' . $in['id'])->save($data);
			redirect($this->forward);
		} else {
			if ($this->ispost()) {
                if(empty($in ['info']['title']))  $this->message ( '标题不能为空！' );
 				if(empty($in ['info']['content']))  $this->message ( '内容不能为空！' );

				if (! $in ['_tablename'])
					$this->message ( '没有指定操作表！' );
				$name = $in ['_tablename']; //数据表名
				//		die($this->getInTableName($name));
				$_m = D ( parse_name($name,1) ); //实例化表模型类

				$_keyid = $_m->getPk ();
				//用create()创建数据对象，以可以使用系统内置的数据自动验证功能以及令牌验证功能
				$in['info'][C ('TOKEN_NAME')] = $in[C ('TOKEN_NAME')];

				if( $in ['info'] [$_keyid] ){
					$val = $_m->find (array('id'=>$in ['info'] [$_keyid]));
					$filename = FANGFACMS_ROOT.C('UPLOAD_DIR').$val['url'];
					if(!empty($val['url']) && file_exists( $filename ) ) @unlink($filename);
				}
				//二维码存在时 删除原来的二维码
				$in['info']['url'] = generate_brcode(trim($in['info']['content']), $in['info']['size']);
                
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
	}
	
	/**
	 * @name编辑
	 */
	public function edit()
	{
		$in = &$this->in;
		if ($in ['ajax']) {
			$this->_edit_ajax ();
		}
	}
	
	/**
	 * @name编辑
	 */
	public function _edit_ajax()
	{
		$in = &$this->in;
		$_model = M('fbcode');
		switch ($in['ajax']) {
			case 'sort':  //排序
				$in['id'] && $in['id'] = (int)substr($in['id'],5);
				if ($in['id'] && !empty($in['sort'])) {
					$data = $_model->find($in['id']);
					if (is_array($data)) {
						$data['sort'] = $in['sort'];
						if (false !== $_model->save($data)) {
							//更新缓存
							die($data['sort']);
						}
					}
				}
				break;
			default:
				break;
		}
		exit ();
	}
	
	/**
	 * @name删除 二维码
	 */
	public function delete()
	{
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
		if ($in['info'][$_keyid] ) { //主键筛选
			if (is_array($in['info'][$_keyid])) {
				if (!empty($in['info'][$_keyid])) {
					$option ['where'] = " `{$_keyid}` IN (".implode(',', $in['info'][$_keyid]) .")";
				}
			} else {
				$option ['where'] = array ($_keyid => $in [$_keyid] );
			}
		}
	   if ($in[$_keyid] ) { //主键筛选
			if (is_array($in[$_keyid])) {
				if (!empty($in[$_keyid])) {
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
	 * @批量生成二维码
	 */
	public function batch()
	{
 		$in        = &$this->in;
		if ($in['ajax'] == 1) {
			$modelid     =  "1,2,3,4,5,14,15";
            $brcode_size = C("brcode_size"); 
			$_model      = M();
			
			$db_pre      = C('DB_PREFIX');
			$table       = "{$db_pre}model_field mf, {$db_pre}category c";
			$where       = "mf.modelid = c.modelid AND mf.modelid IN($modelid) AND mf.field = 'brcode' AND mf.status = '1'";
			$data        = $_model->table($table)->field('c.catid')->where($where )->select();
            $count       = 0;
			foreach($data as $item){
				$catid = $item['catid'];
				$list = M('Content')->field('url, cid, brcode')->where("catid = '$catid'")->select();
				foreach($list as $val){
					$siteurl = 'http://'.$_SERVER['HTTP_HOST'];
					$categoryData = F ('category_' . $catid);
					$brcode_url   = $siteurl.str_replace('//','/',$categoryData['url'].$val['url']);
					$pic_url = generate_brcode($brcode_url, $brcode_size);
					if(!empty($pic_url)){
                        $count++;
						$sql = "UPDATE {$db_pre}content SET `brcode`='$pic_url' WHERE `cid`='".$val['cid']."' LIMIT 1";
						$_model->query($sql);
                    }
					if(!empty($val['brcode'])){
						@unlink(FANGFACMS_ROOT.C('UPLOAD_DIR').$val['brcode']);
                    }
				}
			}
			if($count == 0) die("没有生成二维码的数据！");
			die("共生成了".$count."条二维码数据！");
        }
		$this->display();
	}
}
?>