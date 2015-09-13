<?php
// +----------------------------------------------------------------------
// | FangFa Net [ http://www.fangfa.net ]
// +----------------------------------------------------------------------
// | File: ShowSystemSetWidget.class.php
// +----------------------------------------------------------------------
// | Date: 2010-5-28
// +----------------------------------------------------------------------
// | Author: fangfa <1364119331@qq.com>
// +----------------------------------------------------------------------
// | 文件描述: 添加普通时候的扩展设置输出widget
// +----------------------------------------------------------------------


class CategoryCommentAddWidget extends Widget {
	
	/**
	 * 参数为栏目数据，数组形式，widget数组信息
	 * @param array $data
	 */
	public function render($data) {
		$this->_category = D ( 'Category' );
		if (! is_array ( $data )) {
			$data = array ();
		}
		$html = $this->tpl ( $data ['setting'],$data ['permissions'] ); //配置
		return $html;
	}
	
	/**
	 * 模板配置
	 */
	public function tpl($setting,$permissions) {
		if (!is_array ( $setting )) {
			$setting = array ();
		}
		import ( 'Html', INCLUDE_PATH );
		$html['head'] .= "<li><a href='javascript:;' rel='tabsContent3'>扩展设置</a></li>";
		//权限
		if (! is_array ( $permissions )) {
			$permissions = array ();
		}
//		dump($permissions);
		$_role = D ( 'Role' );
		//所有可用的非开发人员角色
		$role_data = $_role->where ( "`status`='1' " )->findAll ();
		//模块所具有的节点
		$_node = D ( 'Node' );
		$controller = ucfirst ( $controller );
		$html ['body'] = '
		<table cellpadding="0" cellspacing="0" class="tabcontent" id="tabsContent3" style="display:none">
				<tbody>

		      	<tr>
		          <th>查看 评论<span>查看评论的页面模板，默认空</span></th>
		          <td>' . Html::template ( 'info[setting][template][lists]', $setting ['template']['lists'] ? 'comment/list.html' : $setting ['template']['lists'], 'class="input" title="请选择评论列表 页模板"' ) . '</td>
		      	</tr>
				<tr>
					 <th>是否开启敏感词替换<span>非法的敏感词将用***替代</span></th>
					 <td><input type="radio" name="info[setting][isfilter]" value=1 '.($setting['isfilter']==1?'checked':'').' />是 <input type="radio" name="info[setting][isfilter]" value=0 '.($setting['isfilter']!=1?'checked':'').' />否</td>
				</tr>

		      	<tr>
		      	  <th>权限设置<span>各管理员对该栏目的操作权限</span></th>
			      <td>
			      <!--start permissions-->
				    <table cellpadding="0" cellspacing="0" width="420">	
				      <thead>			      
				      <tr>
				        <td>角色</td>
				        <td width="30">录入</td>
				        <td width="30">编辑</td>
				        <td width="30">审核</td>				        
				        <td width="30">删除</td>
				        <td width="100">信息管理</td>
				      </tr>
				      </thead>
				      ';
		if (is_array ( $role_data )) {
			foreach ( $role_data as $v ) {
				if ($v ['role_id'] == '1') { //超级管理员，[admin]表示后台权限
					$html ['body'] .= '
					    <tr title="超级管理员拥有所有权限！">
			              <td >' . $v ['nickname'] . '</td>
			              <td><input type="checkbox" name="info[permissions][admin][add][]" value="' . $v ['name'] . '" onclick= "return false;" checked /></td>
			              <td><input type="checkbox" name="info[permissions][admin][edit][]" value="' . $v ['name'] . '" onclick= "return false;" checked /></td>
			              <td><input type="checkbox" name="info[permissions][admin][check][]" value="' . $v ['name'] . '" onclick= "return false;" checked /></td>			              
			              <td><input type="checkbox" name="info[permissions][admin][delete][]" value="' . $v ['name'] . '" onclick= "return false;" checked /></td>
			              <td><input type="checkbox" name="info[permissions][admin][manage][]" value="' . $v ['name'] . '" onclick= "return false;" checked /></td>
			            </tr>';
				} else { //普通角色
					$html ['body'] .= '<tr title="请设置其他角色的权限！">
			              <td>' . $v ['nickname'] . '</td>
			              <td><input type="checkbox" name="info[permissions][admin][add][]" value="' . $v ['name'] . '" ' . (in_array ( $v ['name'], $permissions ['admin']['add'] ) ? 'checked' : '') . ' /></td>
			              <td><input type="checkbox" name="info[permissions][admin][edit][]" value="' . $v ['name'] . '"  ' . (in_array ( $v ['name'], $permissions ['admin']['edit'] ) ? 'checked' : '') . ' /></td>
			              <td><input type="checkbox" name="info[permissions][admin][check][]" value="' . $v ['name'] . '" ' . (in_array ( $v ['name'], $permissions ['admin']['check'] ) ? 'checked' : '') . ' /></td>			              
			              <td><input type="checkbox" name="info[permissions][admin][delete][]" value="' . $v ['name'] . '" ' . (in_array ( $v ['name'], $permissions ['admin']['delete'] ) ? 'checked' : '') . ' /></td>
			              <td><input type="checkbox" name="info[permissions][admin][manage][]" value="' . $v ['name'] . '"  ' . (in_array ( $v ['name'], $permissions ['admin']['manage'] ) ? 'checked' : '') . ' /></td>
			            </tr>';
				}
			}
		}
		$html ['body'] .= '</table> <!--end permissions-->
			        </td>
			      </tr>
		      	</tbody>
		      	</table>';
		return $html;
	}
	
}

?>