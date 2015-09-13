<?php

// +----------------------------------------------------------------------

// | FangFa Net [ http://www.fangfa.net ]

// +----------------------------------------------------------------------

// | File: MenuextModel.class.php

// +----------------------------------------------------------------------

// | Date: 下午02:55:35

// +----------------------------------------------------------------------

// | Author: fangfa <1364119331@qq.com>

// +----------------------------------------------------------------------

// | 文件描述: 针对前台的一些Menu操作的封装

// +----------------------------------------------------------------------

import ( 'admin.Model.MenuModel' );

class MenuextModel extends MenuModel {

	

	protected $tableName = '';

	

	protected $dataTree = array();

	

	public function getMenuDataTree($parentid, $rolenames = '') {

		$this->getMenuDataArray($parentid, $rolenames);

		$this->dataTree[] = F ('menu_'.$parentid, '', ALL_CACHE_PATH . 'menu/');

		$this->dataTree = list_to_tree($this->dataTree,'menuid' ,'parentid','child');

		return $this->dataTree[0]['child'];

	}

	

	protected function getMenuDataArray($parentid, $rolenames = '') {

		$data = $this->field("`menuid`")->where("`parentid`='{$parentid}'")->order("`sort` ASC")->findAll();

		if (is_array($data) && !empty($data)) {

			foreach ($data as $v) {

				$v = F('menu_'.$v['menuid'],'',ALL_CACHE_PATH . 'menu/');

				if (is_array ( $v )) {

					$access = false;

					if (is_array($rolenames)) {

						foreach ($rolenames as $r) {

							if (in_array($r,(array)$v['rolenames'])) $access = true;

						}

					} else {

						if (in_array($rolenames,$v['rolenames'])) $access = true;

					}

					if ($rolenames[0] == 'developer' ) $access = true;

					if ($access) {

						$this->dataTree[] = $v;

						if ( $v['hasChildren'] ) {

							$this->getMenuDataArray($v['menuid'],$rolenames);

						}						

					}

				}				

			}

		}

	}

	



}

