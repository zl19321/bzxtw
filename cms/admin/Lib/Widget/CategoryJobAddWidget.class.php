<?php
// +----------------------------------------------------------------------
// | FangFa Net [ http://www.fangfa.net ]
// +----------------------------------------------------------------------
// | File: ShowSystemSetWidget.class.php
// +----------------------------------------------------------------------
// | Date: 2010-9-17
// +----------------------------------------------------------------------
// | Author: 王超
// +----------------------------------------------------------------------
// | 文件描述: 添加普通时候的扩展设置输出widget
// +----------------------------------------------------------------------


class CategoryJobAddWidget extends Widget {
	
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
		          <th width="150">列表页模板<span>留言列表页所使用的模板</span></th>
		          <td>' . Html::template ( 'info[setting][template][index]', $setting ['template']['index']? $setting ['template']['index'] : 'job/index.html','class="required input" title="请选择列表页模板"' ) . '</td>
		      	</tr>
		      	<tr>
		          <th>职位详情 页模板<span>显示职位详情的页面模板</span></th>
		          <td>' . Html::template ( 'info[setting][template][show]', $setting ['template']['show']? $setting ['template']['show'] : 'job/show.html', 'class="input" title="请选择职位详情 页模板"' ) . '</td>
		      	</tr>
		      	<tr>
		          <th>在线填写简历页 模板</th>
		          <td>' . Html::template ( 'info[setting][template][send]', $setting ['template']['send']? $setting ['template']['send'] : 'job/send.html', 'class="input" title="请选择在线填写简历 页模板"' ) . '</td>
		      	</tr>
		      	<tr>
		          <th>是否在线发简历必须登录</th>
		          <td><input type="radio" name="info[setting][islogin]" value=1 '.($setting['islogin']==1?'checked':'').' />是 <input type="radio" name="info[setting][islogin]" value=0 '.($setting['islogin']!=1?'checked':'').' />否</td>
		      	</tr>
		      	<tr>
		          <th>允许上传的简历大小</th>
		          <td><input type="text" name="info[setting][maxsize]" value="'.($setting['maxsize']?$setting['maxsize']:300).'" size=12 />K</td>
		      	</tr>
		      	<tr>
		          <th>允许上传的简历文件后缀<span>请用"|"分隔</span></th>
		          <td><input type="text" name="info[setting][allow_ext]" value="'.($setting['allow_ext']?$setting['allow_ext']:'rar|zip|doc|xls|wps|et').'" /></td>
		      	</tr>
		      	<tr>
		      	  <th>权限设置<span>各管理员对该栏目的操作权限</span></th>
			      <td>
			      <!--start permissions-->
				    <table cellpadding="0" cellspacing="0" width="510px">	
				      <thead>			      
				      <tr>
				        <td>角色</td>
				        <td width="30">录入</td>
				        <td width="30">编辑</td>
				        <td width="30">审核</td>				        
				        <td width="30">删除</td>
				        <td width="50">信息管理</td>
				        <td width="50">简历查看</td>
				        <td width="50">简历删除</td>
                        <td width="50">简历显示</td>
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
			              <td><input type="checkbox" name="info[permissions][admin][resume_manage][]" value="' . $v ['name'] . '" onclick= "return false;" checked /></td>
			              <td><input type="checkbox" name="info[permissions][admin][resume_delete][]" value="' . $v ['name'] . '" onclick= "return false;" checked /></td>
                          <td><input type="checkbox" name="info[permissions][admin][resume_show][]" value="' . $v ['name'] . '" onclick= "return false;" checked /></td>
			            </tr>';
				} else { //普通角色
					$html ['body'] .= '<tr title="请设置其他角色的权限！">
			              <td>' . $v ['nickname'] . '</td>
			              <td><input type="checkbox" name="info[permissions][admin][add][]" value="' . $v ['name'] . '" ' . (in_array ( $v ['name'], $permissions ['admin']['add'] ) ? 'checked' : '') . ' /></td>
			              <td><input type="checkbox" name="info[permissions][admin][edit][]" value="' . $v ['name'] . '"  ' . (in_array ( $v ['name'], $permissions ['admin']['edit'] ) ? 'checked' : '') . ' /></td>
			              <td><input type="checkbox" name="info[permissions][admin][check][]" value="' . $v ['name'] . '" ' . (in_array ( $v ['name'], $permissions ['admin']['check'] ) ? 'checked' : '') . ' /></td>			              
			              <td><input type="checkbox" name="info[permissions][admin][delete][]" value="' . $v ['name'] . '" ' . (in_array ( $v ['name'], $permissions ['admin']['delete'] ) ? 'checked' : '') . ' /></td>
			              <td><input type="checkbox" name="info[permissions][admin][manage][]" value="' . $v ['name'] . '"  ' . (in_array ( $v ['name'], $permissions ['admin']['manage'] ) ? 'checked' : '') . ' /></td>
			              <td><input type="checkbox" name="info[permissions][admin][resume_manage][]" value="' . $v ['name'] . '"  ' . (in_array ( $v ['name'], $permissions ['admin']['resume_manage'] ) ? 'checked' : '') . ' /></td>
			              <td><input type="checkbox" name="info[permissions][admin][resume_delete][]" value="' . $v ['name'] . '"  ' . (in_array ( $v ['name'], $permissions ['admin']['resume_delete'] ) ? 'checked' : '') . ' /></td>
			              <td><input type="checkbox" name="info[permissions][admin][resume_show][]" value="' . $v ['name'] . '"  ' . (in_array ( $v ['name'], $permissions ['admin']['resume_show'] ) ? 'checked' : '') . ' /></td>
                        </tr>';
				}
			}
		}
		$html ['body'] .= '</table> <!--end permissions-->
			        </td>
			      </tr>
		      	</tbody></table>';
		return $html;
	}
	
}

?>