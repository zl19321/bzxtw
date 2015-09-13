<?php
// +----------------------------------------------------------------------
// | FangFa Net [ http://www.fangfa.net ]
// +----------------------------------------------------------------------
// | File: Field.class.php
// +----------------------------------------------------------------------
// | Date: 2010-5-7
// +----------------------------------------------------------------------
// | Author: fangfa <1364119331@qq.com>
// +----------------------------------------------------------------------
// | 文件描述: 字段类接口以及字段操作处理工厂类
// +----------------------------------------------------------------------


/**
 * 字段类接口，定义所有字段类必须实现的方法
 * 各字段类型对应的类都在 include/field/ 下面
 * @author Administrator
 *
 */
interface FieldInterface {
	//后台表单输出
	public function form();		
	//配置输出
	public function setting();	
}

/**
 * 字段操作处理类
 * @author Administrator
 *
 */
class Field {
	/**
	 * 对象实例容器
	 * @var array
	 */
	public static $instance = array ();
	
	//$GLOBALS['loadFiles']为需要加载的外部资源文件
	
	/**
	 * 取得对应的field实例对象
	 * @param $class
	 */
	protected static function get_instance_of($class) {
		if (! empty ( $class )) {
			$className = ucfirst ( strtolower ( $class ) ) . 'Field';
			if (! isset ( self::$instance [$class] )) {
				$file = INCLUDE_PATH . 'field/' . $className . '.class.php';
				if (file_exists($file)) {
					import ( $className, INCLUDE_PATH . 'field/' );
					self::$instance [$class] = get_instance_of ( $className );
				}				
			}
		}
	}
	
	/**
	 * 对应field的名称
	 * @param string $class
	 */
	public static function name($class) {
		if (! empty ( $class )) {
			$file = INCLUDE_PATH . 'field/' . ucfirst ( strtolower ( $class ) ) . 'Field.class.php';
			if (file_exists ( $file )) {
				$file = ucfirst ( strtolower ( $class ) ) . 'Field.class.php';
				self::get_instance_of ( $class );
			} else {
				return strtoupper ( $class );
			}
		}
		if (isset ( self::$instance [$class] ) && ! empty ( $class ))
			return self::$instance [$class]->name;
		else
			return false;
	}
	
	
	
	/**
	 * 对应field的后台表单
	 * @param string $class  字段类型
	 * @param array $option  字段配置
	 */
	public static function form($class = '', $option) {
		if (! empty ( $class )) {
			self::get_instance_of ( $class );
		}
		if (isset ( self::$instance [$class] ) && ! empty ( $class )) {		
			if (method_exists(self::$instance[$class],'form'))	
				$html = self::$instance [$class]->form ( $option );
			if (! empty ( $html )) {
				//$result = ! empty ( $option ['parent_css'] ) ? ('class="' . $option ['parent_css'] . '"') : '' ;
				//处理父级标签样式  修改日期   2011-07-20  马东
				$result_left = ! empty ( $option ['parent_css'] ) ? ('<span class="' . $option ['parent_css'] . '">') : '' ;
				$result_right = ! empty ( $option ['parent_css'] ) ? ('</span>') : '' ;
				$result = $result_left . $html . $result_right;
				return $result;
			}
		}
		return ;
	
	}
	
	/**
	 * 对应field的配置项输出
	 * @param string $class  字段类型 
	 * @param array $option  字段配置
	 */
	public static function setting($class, $option) {
		if (! empty ( $class )) {
			self::get_instance_of ( $class );
		}
		if (isset ( self::$instance [$class] ) && ! empty ( $class )) {
			if (method_exists(self::$instance[$class],'setting'))
				$result = self::$instance [$class]->setting ( $option );
		} else {
			$result = '';
		}
		return $result;
	}
	
	/**
	 * 创建物理数据表
	 * @param $class
	 * @param $model  数据表模型对象
	 * @param $tableName  要操作的数据表
	 * @param $data 添加字段时候用户填写的表单数据
	 */
	public static function addField($class, $model = '', $tableName = '', $data = array()) {
		if (! empty ( $class )) {
			self::get_instance_of ( $class );
		}
		if (isset ( self::$instance [$class] ) && ! empty ( $class )) {
			if (method_exists(self::$instance[$class],'addField'))
				$result = self::$instance [$class]->addField ( $model, $tableName, $data );
		} else {
			$result = false;
		}
		return $result;
	}
	
	/**
	 * 字段的前台页面输出
	 * 处理字段的新增的内容
	 * @param string $class 字段类型
	 * @param string $field 字段名称
	 * @param string $vlaue 字段值
	 * @param array $option 字段配置
	 */
	public static function add($class, $field, $value, $setting) {
		if (! empty ( $class )) {
			self::get_instance_of ( $class );
		}
		if (isset ( self::$instance [$class] ) && ! empty ( $class )) {
			if (method_exists(self::$instance[$class],'add'))
				$result = self::$instance [$class]->add ( $field, $value, $setting );
			else $result = $value;
		} else {
			$result = $value;
		}
		return $result;
	}
	
	/**
	 * 处理字段提交的更新内容
	 * @param string $class 字段类型
	 * @param string $field 字段名称
	 * @param string $vlaue 字段值
	 * @param array $option 字段配置
	 */
	public static function update($class, $field, $vlaue, $option ) {
		if (! empty ( $class )) {
			self::get_instance_of ( $class );
		}
		if (isset ( self::$instance [$class] ) && ! empty ( $class )) {
			if (method_exists(self::$instance[$class],'update'))
				$result = self::$instance [$class]->update ( $field, $vlaue, $option  );
		} else {
			$result = false;
		}
		return $result;
	}
	
	
	/**
	 * 格式化查询到的字段值，易于前台直接输出
	 * @param string $class 字段类型
	 * @param string $field 字段名称
	 * @param string $data  内容数组(基础表，扩展表所有数据)引用，因为一个字段的格式化内容可能需要知道其他字段的值
	 */
	public static function output($class, $field, $config, &$data) {
		if (! empty ( $class )) {
			self::get_instance_of ( $class );
		}
		if (isset ( self::$instance [$class] ) && ! empty ( $class )) {
			if (method_exists(self::$instance[$class],'output')){
				$result = self::$instance [$class]->output ( $field, $config, $data );
			}
			else $result = $value;
		} else {
			$result = $value;
		}
		return $result;
	}

}

?>