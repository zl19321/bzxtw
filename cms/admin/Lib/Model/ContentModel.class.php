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
// | 文件描述: 内容管理Model
// +----------------------------------------------------------------------

class ContentModel extends Model{

	/**
	 * 数据表名
	 * @var string
	 */
	protected $tableName = 'content';

	/**
	 * 分类ID
	 * @var int
	 */
	public $_catid = '';

	/**
	 * 模型ID
	 * @var int
	 */
	public $_modelid = '';

	/**
	 * 模型的字段信息数组
	 * @var array
	 */
	public $_field_data = array();


	/**
	 * 是否自动格式化数据
	 *
	 * @var unknown_type
	 */
	public $auto_format = false;
	
	

	/**
	 * 查询的后置方法，会在find()之后自动以引用方式调用
	 */
	protected function _after_find(&$result, $options = '') {
		parent::_after_find ( $result, $options );
		if ($this->auto_format) {
			if (method_exists($this,'format')) {
				$t = $this->format(array($result));
				$result = $t [0];
			}			
		}
		if (!empty($result['attr'])) {
			$result['attr'] = explode(',', $result['attr']);
		}
	}

	/**
	 * findAll 或者 select 之后自动调用的数据处理方法
	 * @param ref $resultSet
	 */
	protected function _after_select(&$resultSet, $options = '') {
		parent::_after_select ( $resultSet, $options );
		if ($this->auto_format) {
			if (method_exists($this,'format')) {
				 $resultSet = $this->format($resultSet);
			}
		}
		foreach ($resultSet as $k=>$v) {
			!empty($v['attr']) && $resultSet [$k]['attr'] = explode(',', $v['attr']);
		}
	}


	/**
	 * 初始化栏目模型信息，模型字段信息
	 * @param int $catid
	 */
	public function init($catid) {
		if (!$catid) return false;
		$this->_catid = $catid;
		$category_data = F ('category_'.$catid);
		$modelid = $category_data['modelid'];
		$this->_modelid = $modelid;
		if (!$modelid) {
			$this->error .= "ID为{$modelid}的模型缓存信息被破坏，请更新模型缓存！";
			return false;
		}
		// 调用各字段类处理提交的内容
		$fieldArr = D("ModelField")->field("`fieldid`")->where(" `status`='1' AND `modelid`='{$modelid}'")->findAll();
		$field = array();  // 栏目模型所有字段的信息
		foreach ($fieldArr as $k=>$v) {
			$value = F ('modelField_'.$v['fieldid']);
			if (is_array($value)) {  //组合成 array($formtype1=>'',$formtype2=>'',$formtype3=>'',)的形式
				$field[$value['field']] = $value;
			}
		}
		$this->_field_data = $field;
	}



	/**
	 * 添加内容的时候进行数据验证
	 * @param array $data  //提交的数据
	 * @param int $catid  //栏目ID
	 * @param string $field //验证的字段，为空则验证所有字段
	 */
	public function validate($data,$catid, $field = '') {
		if ($this->_catid!=$catid || !is_array($this->_field_data)) {
			$this->init($catid);
		}
		$field_data = $this->_field_data;
		$return = true;
		if (is_array($data)) {  //验证各个字段
			foreach ($data as $k=>$v) {
				if (!isset($field_data[$k])) continue;
				if ($field_data[$k]['required']) {   //必填项
					if (empty($v) && $v != '0') {
						if (!empty($field_data[$k]['errortips'])) {
							$this->error .= $field_data[$k]['errortips'] . "<br />";
						} else {
							$this->error .= $field_data[$k]['name'] . "必填<br />";
						}
						$return = false;
					} else { // 验证必填项的 长度 minlength  maxlength
						if ($field_data[$k]['minlength'] && (mb_strlen($v,'utf-8') < $field_data[$k]['minlength']) ) {
							if (!empty($field_data[$v]['errortips'])) {  //minlength
								$this->error .= $field_data[$v]['errortips'] . "<br />";
							} else {
								$this->error .= $field_data[$k]['name'] . "长度不能小于{$field_data[$k]['minlength']} <br />";
							}
							$return = false;
						}
						if ($field_data[$k]['maxlength'] && (mb_strlen($v,'utf-8') > $field_data[$k]['maxlength'])) {  //maxlength
							if (!empty($field_data[$k]['errortips'])) {
								$this->error .= $field_data[$k]['errortips'] . "<br />";
							} else {
								$this->error .= $field_data[$k]['name'] . "长度不能大于于{$field_data[$k]['maxlength']} <br />";
							}
							$return = false;
						}
					}
				}
				if (!empty($field_data[$k]['pattern'])) {  //正则
					if (!parent::regex($v,$field_data[$k]['pattern'])) {
						if (!empty($field_data[$k]['errortips'])) {
							$this->error .= $field_data[$k]['errortips'] . "<br />";
						} else {
							$this->error .= $field_data[$k]['name'] . "填写不规范  <br />";
						}
						$return = false;
					}
				}
			}
		}
		return $return;
	}


	/**
	 * 检测标题是否已经存在
	 * @param string $title
	 */
	public function titleExist($title) {
		return null !== $this->where("`title`='{$title}'")->find();
	}


	/**
	 * 处理内容模型栏目添加信息表单
	 * @param unknown_type $data
	 */
	public function add($data) {
		if (!$data['catid']) {
			$this->error .= '没有选择栏目！';
			return false;
		}
		if ($this->_catid !== $data['catid']) {
			$this->init($data['catid']);
		}
		if (!$this->validate($data,$data['catid'])) {  //数据验证
			return false;
		}
		$field_data = $this->_field_data;
		import('Field',INCLUDE_PATH);  //进行处理

		foreach ($data as $k=>$v) { //对数据进行处理
            if(empty($field_data[$k]['dbname'])){
                $data[$k] = Field::add($field_data[$k]['formtype'],$k,$v,$field_data[$k]['setting']);
            }			
		}

		if (!empty($data['url'])) { //url链接地址处理
			$data['url'] = date('Ym/') . $data['url'] . C ('URL_HTML_SUFFIX');
		}
		//摘要处理
		if (empty($data['description'])) {
			$text = html2txt(strip_tags(trim($data['content'])));
			if (!empty($text)) {
				$data['description'] = msubstr($text,0,160);
			}
		}
		//默认seo信息处理
		if (empty($data['seotitle'])) {
			$data['seotitle'] = trim($data['title']) . ' - {stitle}';
		}
		if (empty($data['seokeywords'])) {
			$data['seokeywords'] = $data['title'];
		}
		if (empty($data['seodescription'])) {
			$data['seodescription'] = $data['description'];
		}
		//提交到基础表content
		$cid = parent::add($data);
		if (false !== $cid) {
			//更新url
			if (empty($data['url'])) {
				parent::execute("UPDATE __TABLE__ set `url`='".date('Ym')."/{$cid}". C ('URL_HTML_SUFFIX') ."' WHERE `cid`='{$cid}' LIMIT 1");
			}
            //处理二维码
            $this->_deal_brcode($data, $cid);
			//添加到扩展表信息
			$data['cid'] = $cid;
			$modelData = F ('model_'.$this->_modelid);
			$_extContentModel = D (parse_name( $modelData['tablename'], 1 ));
			$_extContentModel->add($data);
			//处理tag关联
			if (!empty($data['tag'])) {
				$_contentTag = D ('ContentTag');
				$_tag = D ('Tag');
				foreach ($data['tag'] as $tag) {  //保存关联信息
					$tagid = '';
					$contentTag = array();
					$tagData = $_tag->where(array("name"=>$tag))->find();
					if (null === $tagData) {  //新增tag
						$tagid = $_tag->add(array('name'=>$tag));
					} else {
						$tagid = $tagData['tagid'];
					}
					if ($tagid) {
						$contentTag = array(
							'tagid' => $tagid,
							'keyid' => 'c-' . $cid
						);
						$_contentTag->add($contentTag);
					}	
				}
			}			
			//初始统计表关联纪录
			M('ContentCount')->add(array('cid'=>$cid));
			return $cid;
		}
		return false;
	}

	/**
	 * 更新内容
	 * @param array $data
	 */
	public function update($data, $cid) {
		if (!$data['cid']) {
			$data['cid'] = $cid;
		}
		if (!$data['cid']) {
			$this->error .= '数据接收错误，没有指定要修改的信息！';
			return false;
		}
		if (!$data['catid']) {
			$this->error .= '没有选择栏目！';
			return false;
		}
		if ($this->_catid !== $data['catid']) {
			$this->init($data['catid']);
		}
		if (!$this->validate($data,$data['catid'])) {  //数据验证
			return false;
		}
		$field_data = $this->_field_data;
		//文档属性为必须字段，当表单没有传递数据的时候，我们需要手动设定为空值
		!isset($data['attr']) && $data['attr'] = '';
		import('Field',INCLUDE_PATH);  // 进行处理
		foreach ($data as $k=>$v) { // 对数据进行处理
            if(empty($field_data[$k]['dbname'])){
                $data[$k] = Field::add($field_data[$k]['formtype'],$k,$v,$field_data[$k]['setting']);
            }
		}		
		//提交到基础表content
		if(empty($data['update_time'])){
			$data['update_time'] = time();  //更新时间
		}
		if (false !== parent::save($data)) {
			//添加到扩展表信息
			$modelData = F ('model_'.$this->_modelid);
			$_extContentModel = D (parse_name($modelData['tablename'], 1));
			$exist_record = $_extContentModel->field("`cid`")->where("`cid`='{$cid}'")->find();
			if (!empty($exist_record)) {
				$_extContentModel->where("`cid`='{$cid}'")->save($data);
			} else {
				$_extContentModel->where("`cid`='{$cid}'")->add($data);
			}
			//处理tag关联、先删除旧关联信息，然后重建tag_content关联信息
			$_contentTag = D ('ContentTag');
			$_contentTag->where("`keyid`='c-{$data['cid']}'")->delete();
			if (!empty($data['tag'])) {
				$_tag = D ('Tag');
				foreach ($data['tag'] as $tag) {  //更新关联信息
					$tagid = '';
					$contentTag = array();
					$tagData = $_tag->where(array("name"=>$tag))->find();
					if (null === $tagData) {  //新增tag
						$tagid = $_tag->add(array('name'=>$tag));
					} else {
						$tagid = $tagData['tagid'];
					}
					if ($tagid) { //建立关联
						$contentTag = array(
							'tagid' => $tagid,
							'keyid' => 'c-' . $data['cid']
						);
						$_contentTag->add($contentTag);
					}
				}
			}
			return $cid;
		} // done save
		return false;
	}


	/**
	 * 内容的数据表信息，取得url地址
	 * @param array $data
	 */
	public function getUrl($data) {
		$categoryData = F ('category_' . $data['catid']);
		$link = $categoryData['url'];
		$link .= $data['url'];
		$link = str_replace('//','/',$link);
		return $link;
	}

	/**
	 * 根据数据存储的图片路径得到其缩略图路径，相对根目录的地址
	 * @param string $image
	 */
	public function getThumb($image) {
		//缩略图
		$realImage = FANGFACMS_ROOT . 'public/uploads/' . $image;
		$imageInfo = pathinfo($realImage);
		$realThumb = $imageInfo['dirname'] . '/thumb/' . $imageInfo['basename'];
		if (file_exists($realThumb)) { //存在缩略图，返回缩略图
			$image = __ROOT__ . '/public/uploads/' . str_replace(FANGFACMS_ROOT . 'public/uploads/','',$realThumb);
		} else $image = __ROOT__ . '/public/uploads/' . $image;  //不存在缩略图，返回原图
		return $image;
	}
 
	/**
	 * 根据数据存储的图片路径得到其二维码路径，相对根目录的地址
	 * @param string $image
	 */
	public function getBrcode($image) {
		//缩略图
		$image = __ROOT__ . '/public/uploads/' . $image;  //返回二维码图片
		return $image;
	}

	/**
	 * 栏目设置、非直接操作，供栏目创建和修改的时候调用
	 * 如果模块不需要进行特殊设置，则直接返回空字符串
	 * @param array $data 栏目数据库记录信息
	 * @param int $catid 栏目ID
	 */
	public function setting($data = array(),$catid = '',$parentid = '') {
		$html = '';
		if (empty ( $data ) && ( int ) $catid) {
			$data = D ( 'Category' )->find ( $catid );
		} elseif ($parentid) {
			$data['parentid'] = $parentid;
		}
		$data ['controller'] = 'fcontent';
		//   修改2011-10-10 ，用于子栏目集成父级栏目的模板设置
		if ($parentid != '' && empty($data['setting'])) {
			$parentid_data = D ( 'Category' )->find ( $parentid );
			$data['setting'] = $parentid_data['setting'];
		}
		
		//调用相应的widget
		$html = W ( 'CategoryNormalAdd', $data, true );
		return $html;
	}

	/**
	 * 获取指定ID的详细内容
	 * @param int or array $options  主键，或者  查询条件数组
	 * @param int $catid  栏目ID
	 * @param array $more  array('base','ext','tag');
	 * 数组参数至少包含一个base 选项，其余可选，也可为all，全选
	 */
	public function get($options, $more = array('base')) {
		if (is_string($more) && $more == 'all') {
			$more = array('all');
		}
		//基本信息
		$data = parent::find($options);
		$catid = $data['catid'];
		if (!$catid) return ;
		if ($data['cid']) {
			if (in_array('ext',$more) || in_array('all',$more)) {  //扩展表信息
				$where = array('cid'=>$data['cid']);
				$category_data = F ('category_'.$catid);
				if ($category_data['modelid']) {
					 $model_data = F('model_'.$category_data['modelid']);
					 $ext_data = D($model_data['tablename'])->where($where)->find();
					 unset($ext_data['cid']);
					 @$data = array_merge($data,(array)$ext_data);
				}
			}
			$this->init($data['catid']);			
			if ((in_array('tag',$more) || in_array('all',$more)) ) {  //取得tag
				//表前缀
				$db_pre = C('DB_PREFIX');
				//查询条件
				$where = array ();
				$where["{$db_pre}content_tag.keyid"] = 'c-'.$data['cid'];  //content的tag都有 "c-" 作为keyid标识
				//排序条件
				$order = "{$db_pre}tag.tagid DESC";
				$join_tag = "{$db_pre}tag ON ({$db_pre}tag.tagid={$db_pre}content_tag.tagid)";
				//查找字段
				$field = "{$db_pre}tag.name";
				//数据
				$result_tag = D('ContentTag')->field($field)->join($join_tag)->where($where)->order($order)->select();
				$arr_tag = array();
				foreach ($result_tag as $v){
					$arr_tag[] = $v['name'];
				}
				$data['tag'] = implode(" ", $arr_tag);
			}
		}
		//格式化输出数据
		//TODO
		if (is_array($this->_field_data)) {
			import('Field',INCLUDE_PATH);
			foreach ($this->_field_data as $k=>$v)  {
				Field::output($v['formtype'],$k, $v['setting'],$data);
			}
		}
		return $data;
	}


	/**
	 * 改变指定ID信息的status
	 * @param array $cidArr
	 * @param int $status
	 */
	public function status($cidArr,$status = '1') {
		if (!empty($cidArr) && is_array($cidArr)) {
			$ids = implode(',',$cidArr);
			$_c = D ('Content');
			$data = array('status'=>$status);
			$return = $_c->where("`cid` IN ({$ids})")->save($data);
		} else {
			$return = false;
		}
		return $return;
	}

	/**
	 * 移动指定ID信息到其他栏目
	 * @param array $cidArr
	 * @param int $catid
	 */
	public function moveto($cidArr,$catid = '') {
		if (!empty($cidArr) && $catid && is_array($cidArr)) {
			$ids = implode(',',$cidArr);
			$_c = D ('Content');
			$data = array('catid'=>$catid);
			$return = $_c->where("`cid` IN ({$ids})")->save($data);
		} else {
			$return = false;
		}
		return $return;
	}

	/**
	 * 删除信息记录：主表，扩展表，统计表，tag关联，实体文件
	 *
	 * @param array $cidArr
	 */
	public function delete($cidArr,$catid = '') {
		if (is_array($cidArr) && !empty($cidArr)) {
			$ids = implode(',',$cidArr);
			$where = " `cid` IN ({$ids}) AND `status`='-1'";
			$field = "`cid`,`catid`,`url`";
			$data = $this->field($field)->where($where)->findAll();
		} elseif (strtolower($cidArr) == 'all') {  //清空回收站
			$where = "`status`='-1' AND `catid`=" . $catid;
			$field = "`cid`,`catid`,`url`";
			$data = $this->field($field)->where($where)->findAll();
		}
		if (is_array($data) && !empty($data)) {
			foreach ($data as $v) {
				//主表
				parent::delete($v['cid']);
				//扩展表
				$category_data = array();
				$category_data = F ('category_'.$v['catid']);
				$model_data = array();
				$model_data = F ('model_'.$category_data['modelid']);
				$_extContent = '';
				$_extContent = D (parse_name($model_data['tablename'], 1));
				$_extContent->where("`cid`='{$v['cid']}'")->delete();
				//统计表
				$_contentCount = D ('ContentCount');
				$_contentCount->where("`cid`='{$v['cid']}'")->delete();
				//tag关联
				$_contentTag = D ('ContentTag');
				$_contentTag->where("`keyid`='c-{$v['cid']}'")->delete();
			}
		}
		return true;
	}

	/**
	 * 修改二维码 
	 * @param array $data     //提交的数据
	 * @param array $options  //选项
	 * @return 返回二维码或者null
	 */
	protected function _before_update(&$data, $options) {
		//开通二维码，并且启用了二维码字段时，添加数据时生成二维码.			
		if (C("is_brcode") == 1 && isset($data["brcode"]) ) { 
			$siteurl      = 'http://'.$_SERVER['HTTP_HOST'];
			$categoryData = F ('category_' . $data['catid']);
			$brcode_url   = $siteurl.str_replace('//','/',$categoryData['url'].$data['url']);
            
            //返回生成的二维码地址
			$data["brcode"] = generate_brcode($brcode_url, C("brcode_size"));
            $val = parent::field("brcode")->find($data['cid']);
            
             //删除以前的二维码
			$filename = FANGFACMS_ROOT.C('UPLOAD_DIR').$val['brcode'];
			if(!empty($val['brcode']) && file_exists( $filename ) ) unlink($filename);
		}
	}
	
     /**
	 * 生成二维码 
	 * @param array $data  //提交的数据
	 * @param cid $options  //数据ID
	 * @return boolean
	 */
	protected function _deal_brcode($data,$cid) {
        //开通二维码，并且启用了二维码字段时，添加数据时自动生成二维码.	
		if (C("is_brcode") == 1 && isset($data["brcode"])) { 
            $list  = M('Content')->WHERE("cid='$cid'")->find();
			if( empty($list) ) return;
			$siteurl      = 'http://'.$_SERVER['HTTP_HOST'];
			$categoryData = F ('category_' . $list['catid']);
			$brcode_url   = $siteurl.str_replace('//','/',$categoryData['url'].$list['url']);
			//返回生成的二维码地址
			$pic_url      = generate_brcode($brcode_url, C("brcode_size"));
            
            if(isset($pic_url)) parent::execute("UPDATE __TABLE__ SET `brcode`='$pic_url' WHERE `cid`='{$cid}' LIMIT 1");
 			return true;
		}
        return false;
	}
}
?>