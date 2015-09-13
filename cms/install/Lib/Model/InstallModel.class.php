<?php
class InstallModel extends Model {
	/**
	 * 更新配置文件
	 * @param unknown_type $data
	 */
	public function cacheConfig($config) {
		if (mk_dir(ALL_CACHE_PATH . 'config.inc.php')) {
			$data = F ('config.inc', '', ALL_CACHE_PATH);
			if (is_array($config)) {
				$config = array_change_key_case($config,CASE_UPPER);
				foreach ($config as $k=>$v) {					
					if (array_key_exists($k,$data)) {
						$data[$k] = $v['value'] ;
					}
				}
			}
			return F ('config.inc',$data,ALL_CACHE_PATH);
		} else return false;
	}
}