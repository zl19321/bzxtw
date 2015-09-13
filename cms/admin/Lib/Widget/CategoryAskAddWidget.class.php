<?php
class CategoryAskAddWidget extends Widget {
	
	/**
	 * 参数为栏目数据，数组形式，widget数组信息
	 * @param array $data
	 */
	public function render($data) {
		$this->_category = D ( 'Category' );
		if (! is_array ( $data )) {
			$data = array ();
		}
		$setting = &$data['setting'];
		//默认继承上级模板
		if (!empty($data['parentid'])) {
			$parent_data = F ('category_'.$data['parentid']);			
		}
		if (empty($setting ['template']['index'])) {
			$data['setting'] ['template']['index'] = 'ask/index.html';
		}
		if (empty($setting ['template']['show'])) {
			$data['setting'] ['template']['show'] = 'ask/show.html';
		}
		if (empty($setting ['template']['form'])) {
			$data['setting'] ['template']['form'] = 'ask/form.html';
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
					<th width="150">问答提问表单页模板</th>
		          <td>' . Html::template ( 'info[setting][template][form]', $setting ['template']['form'],'class="input" title="提问表单页模板"' ) . '</td>
				</tr>
				<tr>
		          <th width="150">问答列表页模板</th>
		          <td>' . Html::template ( 'info[setting][template][index]', $setting ['template']['index'],'class="input" title="问答列表页模板"' ) . '</td>
		      	</tr>
		      	<tr>
		          <th>问答详细页模板</th>
		          <td>' . Html::template ( 'info[setting][template][show]', $setting ['template']['show'], 'class="input" title="问答详细页模板"' ) . '</td>
		      	</tr>
		      	<tr>
		      		<th>是否允许游客提问</th>
		      		<td><input type="radio" name="info[setting][allowAsk]" value=1 '.($setting['allowAsk']!==0?'checked':'').' />是 <input type="radio" name="info[setting][allowAsk]" value=0 '.($setting['allowAsk']===0?'checked':'').' />否</td>
		      	</tr>
		      	<tr>
		      		<th>是否允许游客回答</th>
		      		<td><input type="radio" name="info[setting][allowAnswer]" value=1 '.($setting['allowAnswer']!==0?'checked':'').' />是 <input type="radio" name="info[setting][allowAnswer]" value=0 '.($setting['allowAnswer']===0?'checked':'').' />否</td>
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
				        <td width="30">删除</td>
				        <td width="100">管理</td>
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
			              <td><input type="checkbox" name="info[permissions][admin][delete][]" value="' . $v ['name'] . '" onclick= "return false;" checked /></td>
			              <td><input type="checkbox" name="info[permissions][admin][manage][]" value="' . $v ['name'] . '" onclick= "return false;" checked /></td>
			            </tr>';
				} else { //普通角色
					$html ['body'] .= '<tr title="请设置其他角色的权限！">
			              <td>' . $v ['nickname'] . '</td>
			              <td><input type="checkbox" name="info[permissions][admin][add][]" value="' . $v ['name'] . '" ' . (in_array ( $v ['name'], $permissions ['admin']['add'] ) ? 'checked' : '') . ' /></td>
			              <td><input type="checkbox" name="info[permissions][admin][edit][]" value="' . $v ['name'] . '"  ' . (in_array ( $v ['name'], $permissions ['admin']['edit'] ) ? 'checked' : '') . ' /></td>
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