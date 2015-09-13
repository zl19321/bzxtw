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
// | 文件描述: setting表模型， 系统定义变量
// +----------------------------------------------------------------------

class SettingModel extends Model{

	/**
	 * 设置$var的值
	 * @param string $var
	 * @param string $value
	 * @param string $key
	 * @param boolean $cache
	 */
	public function set($var,$value,$key,$cache = true) {
		$where = array(
			'var' => $var
		);
		$key && $where['key'] = $key;
		$data = $this->where($where)->find();
		if (is_null($data)) { //如果数据表中没有此$var的值，则添加
			$data = array(
				'var' => $var,
				'value' => $value,
			);
			$key && $data['key'] = $key;
			if (false === parent::add($data)) {
				$result = false;
			}
		} else { //更新数据表中的内容
			$data['value'] = $value;
			if (false === $this->where($where)->save($data)) {
				$result = false;
			}
		}
		//自动更新配置文件
		$cache && $this->cacheAll();
		return $result;
	}


	/**
	 * 更新所有分组的配置文件
	 *
	 */
	public function cacheAll() {
		$config = $this->findAll();
		if (is_array($config)) {
			$config_data = array();
			foreach ($config as $k=>$v) {
				$v['var'] = strtoupper($v['var']);
				$config_data[$v['var']] = $v['value'];
			}
            return F ('config.cache', $config_data, ALL_CACHE_PATH);//写入配置缓存文件
		}
		return false;
	}
}
