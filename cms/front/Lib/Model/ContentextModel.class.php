<?php
// +----------------------------------------------------------------------
// | FangFa Net [ http://www.fangfa.net ]
// +----------------------------------------------------------------------
// | File: ContentextModel.class.php
// +----------------------------------------------------------------------
// | Date: 下午02:55:35
// +----------------------------------------------------------------------
// | Author: fangfa <1364119331@qq.com>
// +----------------------------------------------------------------------
// | 文件描述: 前台针对内容的一些操作的封装
// +----------------------------------------------------------------------
import('admin.Model.ContentModel');
class ContentextModel extends ContentModel {	
	
	/**
	 * 批量格式化 content及其扩展表查询出来的数据
	 *
	 * @param array $data
	 */
	public function format($data) {
		import('Field',INCLUDE_PATH);
		if (!$data[0]['catid']) {
			return $data;
		}		
		//初始化数据
		$this->init($data[0]['catid']);
		//格式化数据		
		foreach ($data as $k=>$v) {			
			$data[$k] = $this->formatOne($v);
		}
		return $data;
	}
	
	/**
	 * 格式化一条数据
	 *
	 * @param array $data
	 */
	protected function formatOne($data) {
		//查询结果，取出数据，根据field查询是否有扩展表中的数据
		//基础表字段
		import('admin.Model.ModelFieldModel');
		$base_field = ModelFieldModel::$baseFields;		
		//URL
		$data['url'] = parent::getUrl($data);
		//缩略图
		if (!empty($data['thumb'])) {			
			$data['thumb'] = parent::getThumb($data['thumb']);						
		}
		//扩展表中的数据会使用对应的Field中的output进行处理
		if (is_array($this->_field_data)) {
			foreach ($this->_field_data as $k=>$v)  {
				if (!in_array($v['field'],$base_field)) {  //扩展表中的数据
					Field::output($v['formtype'],$k, $v['setting'],$data);
				}
			}
		}
		return $data;
	}
}

?>