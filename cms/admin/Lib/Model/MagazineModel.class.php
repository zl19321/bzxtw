<?php
// +----------------------------------------------------------------------
// | FangFa Net [ http://www.fangfa.net ]
// +----------------------------------------------------------------------
// | File: TagModel.class.php
// +----------------------------------------------------------------------
// | Date: 2011-8-16
// +----------------------------------------------------------------------
// | Author: Mark
// +----------------------------------------------------------------------
// | 文件描述: 报刊杂志Model
// +----------------------------------------------------------------------

class MagazineModel extends Model{

	/**
	 * 数据表名
	 * @var string
	 */
	protected $tableName = 'magazine';

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
	//错误信息
 	public $error = '';
	
	/**
	 * findAll 或者 select 之后自动调用的数据处理方法
	 * @param ref $resultSet
	 */
	protected function _after_select(&$resultSet) {
		parent::_after_select ( $resultSet );
		foreach ($resultSet as $k=>$v) {
			
			if(!empty($v['images'])){
				$resultSet[$k]['is_images'] = '<font color="Blue">[图]</font>';
			}else {
				$resultSet[$k]['is_images'] = '';
			}
			$resultSet[$k]['create_time'] = date("Y-m-d",$v['create_time']);
			$resultSet[$k]['update_time'] = date("Y-m-d",$v['update_time']);
			$resultSet[$k]['access'] = '<a href="" title="访问" target="_blank">查看</a>';
			$resultSet[$k]['edit'] = '<a href="__ROOT__/admin.php?m=fmagazine&a=edit&id='.$v['id'].'&catid='.$v['catid'].'"  title="更新内容">编辑</a>';
			$resultSet[$k]['editiamges'] = '<a href="__ROOT__/admin.php?m=fmagazine&a=editimages&id='.$v['id'].'&catid='.$v['catid'].'"  title="编辑导图">编辑导图</a>';
			$resultSet[$k]['delete'] = '<a href="__ROOT__/admin.php?m=fmagazine&a=edit&do=delete&id='.$v['id'].'&catid='.$v['catid'].'" onClick="return window.confirm(\'此操作不可撤销，你确定要继续？\');">删除</a>';
		}
	}
	
	protected function _before_update(&$data){
		$this-> _before_insert ( $data );
	}
	
	protected  function _before_insert(&$data){
		if ( empty($data['catid']) ) {
			$this->error .= '没有选择栏目！';
			return false;
		} 
		if (empty($data['create_time'])) {
			$data['create_time'] = time();
		}else {
			 $data['create_time'] = strtotime($data['create_time']);
		}
		if (empty($data['update_time'])) {
			$data['update_time'] = time();
		}else {
			 $data['update_time'] = strtotime($data['update_time']);
		}
		if ((int)$data['sort'] == 0) {
			$data['sort'] = 1;
		}
	}
	
	public function del($parentid = "", $catid, $id){
		$_magazine = D ( 'Magazine' );
		if (empty($catid)) {
			$this->error .= '没有选择栏目！';
			return false;
		}
		$where['parentid'] = $parentid;
		$where['catid'] = $catid;
		if(!empty($id))  $where['id'] = $id; 
		$all = $_magazine->field("parentid, catid, id")->where($where)->findAll();
		$_magazine->where("id=" . $id)->delete();
		foreach ($all as $t){
			$_magazine->where("id=" . $t['id'])->delete();
			$_magazine->del($t['id'],$t['catid'],0);
		}
		return true;
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
		//调用相应的widget
		$html = W ( 'CategoryMagazineAdd', $data, true );
		return $html;
	}
}
?>