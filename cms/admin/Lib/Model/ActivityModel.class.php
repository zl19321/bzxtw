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
// | 文件描述: 活动报名Model
// +----------------------------------------------------------------------

class ActivityModel extends Model{
	
	protected function _before_insert(&$data, $options) {
		empty($data['create_time']) && $data['create_time'] = time();
		empty($data['update_time']) && $data['update_time'] = time();
		!empty($data['start_time']) && $data['start_time'] = strtotime($data['start_time']);
		!empty($data['end_time']) && $data['end_time'] = strtotime($data['end_time']);
		!empty($data['in_time']) && $data['in_time'] = strtotime($data['in_time']);//2013-1-15 陈敏，后台中的报名开始时间
		!empty($data['out_time']) && $data['out_time'] = strtotime($data['out_time']);//2013-1-15 陈敏，后台中的报名结束时间
		//文档属性
		$data['attr'] = empty($data['attr']) ? "" : implode(",", $data["attr"]);//2013-1-15 陈敏,后台文档属性的实现
		//start url链接地址处理
		if (empty($data['url'])) { 
			$data['url'] = date('Ym/') . $data['aid'] . C ('URL_HTML_SUFFIX');
		}
		$data['url'] = str_replace('-', '.', $data['url']);
		//end url
		if (empty($data['seotitle'])) {
			$data['seotitle'] = trim($data['title']) . ' - {stitle}';
		}
		if (empty($data['seokeywords'])) {
			$data['seokeywords'] = $data['title'];
		}
		if (empty($data['seodescription'])) {
			$data['seodescription'] = $data['description'];
		}
	}
	protected function _after_insert(&$data, $options){
		parent::execute("UPDATE ".C("DB_PREFIX")."activity set `url`='".date('Ym')."/" . $data['aid'] . C ('URL_HTML_SUFFIX') ."' WHERE `aid`='" . $data['aid'] . "' LIMIT 1");
	}
	
	protected function _before_update(&$data, $options) {	
	
		empty($data['update_time']) && $data['update_time'] = time();
		!empty($data['start_time']) && $data['start_time'] = strtotime($data['start_time']);
		!empty($data['end_time']) && $data['end_time'] = strtotime($data['end_time']);
		!empty($data['in_time']) && $data['in_time'] = strtotime($data['in_time']);//2013-1-15 陈敏 后台报名开始时间的转换
		!empty($data['out_time']) && $data['out_time'] = strtotime($data['out_time']);//2013-1-15 陈敏 后台报名结束时间的转换
		$data['attr'] = empty($data['attr']) ? "" : implode(",", $data["attr"]);//2013-1-15 陈敏 后台文档属性的定义

		//start url链接地址处理
		if (empty($data['url'])) { 
			$data['url'] = date('Ym/') . $data['aid'] . C ('URL_HTML_SUFFIX');
		}else {
			$category_data = F ('category_'.$data['catid']);
			$data['url'] = str_replace($category_data['url'], "", $data['url']);
		}
		//end url
		if (empty($data['seotitle'])) {
			$data['seotitle'] = trim($data['title']) . ' - {stitle}';
		}
		if (empty($data['seokeywords'])) {
			$data['seokeywords'] = $data['title'];
		}
		if (empty($data['seodescription'])) {
			$data['seodescription'] = $data['description'];
		}
	}
	
	public function _after_find(&$result, $options) {
		if (!empty($result)) {
			$category_data = F ('category_'.$result['catid']);
			$result['url'] = $category_data['url'] . $result['url'];
		}
	}
	
	public function _after_select(&$resultSet, $options) {
		if (!empty($resultSet)) {
			foreach ($resultSet as $k=>$v) {
				$this->_after_find($resultSet[$k], $options);
			}
		}
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
		$data ['controller'] = 'factivity';
		//调用相应的widget
		$html = W ( 'CategoryActivity', $data, true );
		return $html;
	}
	
	/**
	 * 添加内容的时候进行数据验证
	 * 
	 * @param array $data  //提交的数据
	 * @param int $catid  //栏目ID
	 * @param string $field //验证的字段，为空则验证所有字段
	 */
	public function validate($data,$catid, $field = '') {
		if (is_array($data)) {
			foreach ($data as $k=>$v) {
				
			}
		}
		return true;
	}

     /**
	 *author:陈敏
	 *update_time:2013-1-15
	 *后台中文档属性的更改
	 * 改变指定ID信息的status
	 * @param array $cidArr
	 * @param int $status
	 */
	public function status($cidArr,$status = '1') {
		if (!empty($cidArr) && is_array($cidArr)) {
			$ids = implode(',',$cidArr);
			$_c = D ('ActivityApply');
			$data = array('status'=>$status);
			$return = $_c->where("`mid` IN ({$ids})")->save($data);
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
	public function delete($cidArr) {
		if (is_array($cidArr) && !empty($cidArr)) {
			$ids = implode(',',$cidArr);
			$where = " `mid` IN ({$ids}) AND `status`='-1'";
			$field = "`mid`";
			$data = $this->field($field)->where($where)->findAll();
		} elseif (strtolower($midArr) == 'all') {  //清空回收站
			$where = "`status`='-1' AND `catid`=" . $catid;
			$field = "`mid`,`catid`,`url`";
			$data = $this->field($field)->where($where)->findAll();
		}
		if (is_array($data) && !empty($data)) {
			foreach ($data as $v) {
				//主表
				parent::delete($v['mid']);
				//扩展表
				$category_data = array();
				$category_data = F ('category_'.$v['catid']);
				$model_data = array();
				$model_data = F ('model_'.$category_data['modelid']);
				$_extContent = '';
				$_extContent = D (parse_name($model_data['tablename'], 1));
				$_extContent->where("`mid`='{$v['mid']}'")->delete();
				//统计表
				$_contentCount = D ('ContentCount');
				$_contentCount->where("`mid`='{$v['mid']}'")->delete();
				//tag关联
				$_contentTag = D ('ContentTag');
				$_contentTag->where("`keyid`='c-{$v['mid']}'")->delete();
			}
		}
		return true;
	}

		/**
	 *陈敏
	 *update_time:2013-1-15
	 *后台中实现报名信息移动到回收站中
	 * 移动指定ID信息到其他栏目
	 * @param array $cidArr
	 * @param int $catid
	 */
	public function moveto($cidArr,$catid = '') {
		if (!empty($cidArr) && $catid && is_array($cidArr)) {
			$ids = implode(',',$cidArr);
			$_c = D ('ActivityApply');
			$data = array('catid'=>$catid);
			$return = $_c->where("`mid` IN ({$ids})")->save($data);
		} else {
			$return = false;
		}
		return $return;
	}

}