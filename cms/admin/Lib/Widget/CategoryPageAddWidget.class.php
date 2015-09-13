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


class CategoryPageAddWidget extends Widget {
	
	/**
	 * 参数为栏目数据，数组形式，widget数组信息
	 * @param array $data
	 */
	public function render($data) {
		$this->_category = D ( 'Category' );
		if (! is_array ( $data )) {
			$data = array ();
		}
		//返回html代码
		import ( 'Html', INCLUDE_PATH );
		//各种配置
		$setting = &$data ['setting'];		
		//默认继承上级模板
		if (!empty($data['parentid']) && empty($data ['setting'] ['template']['show'])) {
			$parent_data = F ('category_'.$data['parentid']);
			if (empty($setting ['template']['show'])) {
				$setting ['template']['show'] = $parent_data ['setting'] ['template']['show'];
			}
		}
		//权限
		$permissions = &$data ['permissions']; 
		$html ['head'] = '<li><a href="javascript:;" rel="tabsContent3">模板</a></li>' ."\n";		
		${'action_show'} = 'checked';
		$html ['body'] = '
			<table cellpadding="0" cellspacing="0" class="tabcontent" id="tabsContent3" style="display:none">
				<tbody>
				<tr title="选择页面所使用的模板">
		          <th width="150">使用模板<span>该页面显示所使用的模板</span></th>
		          <td>' . Html::template ( 'info[setting][template][show]', $setting ['template']['show']? $setting ['template']['show']:'page/show.html' ,'class="input" title="请选择页面使用的模板"') . '</td>
		      	</tr>';
		/*
		//权限html
		$_role = D ( 'Role' );
		//所有可用的非开发人员角色
		$role_data = $_role->where ( "`status`='1'" )->findAll ();
		
		//模块所具有的节点
		$_node = D ( 'Node' );
		$controller = ucfirst ( $controller );
		$html ['body'] .= '
			      <tr title="设置各管理角色对该页面的管理权限">
			        <td colspan="2" align="left">
			          <table cellpadding="0" width="100%" cellspacing="1" class="table_list">			            
			            <tr>
			              <th>角色名称</th>
			              <th>录入</th>
			              <th>编辑</th>
			              <th>排序</th>
			              <th>删除</th>-->
			            </tr>
			            ';
		if (is_array ( $role_data )) {
			foreach ( $role_data as $v ) {
				if ($v ['role_id'] == '1') { //超级管理员，[admin]表示后台权限
					$html ['body'] .= '<tr>
			              <td>' . $v ['name'] . '</td>
			              <td><input type="checkbox" name="info[permissions][admin][add][]" value="' . $v ['role_id'] . '" onclick= "return false;" checked /></td>
			              <td><input type="checkbox" name="info[permissions][admin][edit][]" value="' . $v ['role_id'] . '" onclick= "return false;" checked /></td>			              
			              <td><input type="checkbox" name="info[permissions][admin][sort][]" value="' . $v ['role_id'] . '" onclick= "return false;" checked /></td>
			              <td><input type="checkbox" name="info[permissions][admin][delete][]" value="' . $v ['role_id'] . '" onclick= "return false;" checked /></td>
			              <td><input type="checkbox" name="info[permissions][admin][manage][]" value="' . $v ['role_id'] . '" onclick= "return false;" checked /></td>
			            </tr>';
				} else { //普通角色
					$html ['body'] .= '<tr>
			              <td>' . $v ['name'] . '</td>
			              <td><input type="checkbox" name="info[permissions][admin][add][]" value="' . $v ['role_id'] . '" ' . (in_array ( $v ['role_id'], $permissions ['admin']['add'] ) ? 'checked' : '') . ' /></td>
			              <td><input type="checkbox" name="info[permissions][admin][edit][]" value="' . $v ['role_id'] . '"  ' . (in_array ( $v ['role_id'], $permissions ['admin']['edit'] ) ? 'checked' : '') . ' /></td>			              
			              <td><input type="checkbox" name="info[permissions][admin][sort][]" value="' . $v ['role_id'] . '"   ' . (in_array ( $v ['role_id'], $permissions ['admin']['sort'] ) ? 'checked' : '') . ' /></td>
			              <td><input type="checkbox" name="info[permissions][admin][delete][]" value="' . $v ['role_id'] . '" ' . (in_array ( $v ['role_id'], $permissions ['admin']['delete'] ) ? 'checked' : '') . ' /></td>
			              <td><input type="checkbox" name="info[permissions][admin][manage][]" value="' . $v ['role_id'] . '"  ' . (in_array ( $v ['role_id'], $permissions ['admin']['manage'] ) ? 'checked' : '') . ' /></td>
			            </tr>';
				}
			}
		}*/
		$html ['body'] .= '	  </table>
			        </td>
			      </tr>
			    </tbody>
			    </table>';
		return $html;
	}	

}

?>