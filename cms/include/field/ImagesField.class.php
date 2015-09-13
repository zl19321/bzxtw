<?php
// +----------------------------------------------------------------------
// | FangFa Net [ http://www.fangfa.net ]
// +----------------------------------------------------------------------
// | File: ImagesField.class.php
// +----------------------------------------------------------------------
// | Date: 2010 10:47:53
// +----------------------------------------------------------------------
// | Author: fangfa <1364119331@qq.com>
// +----------------------------------------------------------------------
// | 文件描述: 组图字段操作
// +----------------------------------------------------------------------
class ImagesField implements FieldInterface {

	public $name = '多图片';

	/**
	 * 字段的前台页面输出
	 * 格式化查询到的字段值  (可选方法)
	 * @param string $class 字段类型
	 * @param string $field 字段名称
	 * @param string $data  内容数组(基础表，扩展表所有数据)引用，因为一个字段的格式化内容可能需要知道其他字段的值
	 */
        //2012-12-27 王文 程序功能改进
	public function output($field, $config, &$data) {
		if (empty($data[$field])) return $data[$field];
		//$data[$field] = explode("\n", $data[$field]);
		//2011-8-31日，修改图片，存储图片名称。存入数据库以数组形式
		if(!empty($data[$field])){
			@$data[$field] = eval("return ".$data[$field].";");
			if (!is_array($data[$field])) {
				$data[$field] = array();
			}
		}else {
			$data[$field] = array();
		}
		$result = array();
		$i = 0;
		foreach ($data[$field] as $image) {
			$result[$i]['name']  = $image[0]; //图片名称
			$result[$i]['image'] = __ROOT__ . '/' . C ( 'UPLOAD_DIR' ) . $image[1]; //原图
                        $result[$i]['title'] = $image['2'];
                        $result[$i]['DESC'] = $image['3'];
			$realImage = FANGFACMS_ROOT . C('UPLOAD_DIR') . $image[1];
			$imageInfo = pathinfo($realImage);
			$realThumb = $imageInfo['dirname'] . '/thumb/' . $imageInfo['basename'];
			if (file_exists($realThumb)) { //存在缩略图，返回缩略图
				$result[$i]['thumb'] = __ROOT__ . '/' . C('UPLOAD_DIR') . str_replace(FANGFACMS_ROOT . C('UPLOAD_DIR'),'',$realThumb);
			} else {
				$result[$i]['thumb'] = $result[$i]['image'];
			}
			$i++;
		}
		$data[$field] = $result;

		return $data;
	}

	/**
	 * 处理字段提交新增内容
	 * @param string $field 字段名称
	 * @param string $vlaue 字段值
	 * @param array $option 字段配置
	 */
        //2012-12-27 王文 程序功能改进
	public function add($field,$value,$config) {
		//处理提交的多图数据
		//检查文件类型
		$data = array();
		@$arr = explode('|', $config['upload_allowext']);
		foreach ($value AS $k=>$v) {
                    if(!is_array($v)){
			$t = explode("|",$v);
			$info = pathinfo($t[1]);
			if (is_array($arr) && !in_array(strtolower($info['extension']), $arr)) {
				unset($value[$k]);
			}
			//替换名称里面的后缀名
			if (strstr($t[0], ".".$info['extension'])) {
				$t[0] = str_replace(".".$info['extension'], "", $t[0]);
			}
			$data[] = $t;
                    }
		}
                $j = 0;
                foreach($data as &$val){
                    $val[] = $value['title'][$j];
                    $val[] = $value['DESC'][$j];
                    $j++;
                }
		if( !empty($data) ){
			return var_export($data,true);
		}else {
			return "";
		}
		//$value = implode("\n", $value);
		//return trim($value);
	}

	/**
	 * 组图上传
	 */
        //2012-12-27 王文 程序功能改进
	public function form($data = '') {
		$html = "";
		$extra = &$data ['attribute']; //元素额外属性
		$html .= '
		<label><input type="button" class="dialog" alt="'.U('fupload/fieldupload?fieldid='.$data['fieldid'].'&shower_id=images_box&multi=true&height=250&width=500').'" title="从电脑上传图片" class="notips" id="upload_img_'.$data['fieldid'].'" value="上传图片" /></label>
		<div id="images_box" style="padding:10px">';
		if (!empty($data['value']) && is_array($data['value'])) {
			foreach ($data['value'] AS $image) {
				$html .= '
				<div title="双击删除此文件" style="cursor: pointer;border:solid 1px #A5A7B6;margin:2px;width:49%;float:left;" ondblclick="$(this).remove();">
                                <div style="float: left;border-right:1px solid #ccc;padding:2px;margin:2px;">
				&nbsp;<img width="150" src="' . $image['image'] . '">
				<input type="hidden" value="' . $image['name'] . "|" . str_replace(__ROOT__ . '/' . C('UPLOAD_DIR'),"", $image['image']) . '" name="info[' . $data['field'] . '][]">&nbsp;
				</div>
                                <div style="float:left;"><hr style="border:1px solid #ccc;">图片标题：<input name="info[images][title][]" value="'.$image['title'].'" type="text" class="input" style="width:105px;"><hr style="border:1px solid #ccc;">图片描述：<textarea name="info[images][DESC][]" style="width:160px;" class="textarea">'.$image['DESC'].'</textarea></div>
                                </div>
				';
			}
		}
		$html .= '</div>';
		return $html;
	}

	/**
	 * 配置输出
	 */
	public function setting($config = '') {
		//各项配置
		$config['thumb_height']		= $config['thumb_height'] ? $config['thumb_height'] : C ('UPLOAD_THUMB_HEIGHT');
		$config['thumb_width']		= $config['thumb_width'] ? $config['thumb_width'] : C ('UPLOAD_THUMB_WIDTH');
		$config['upload_maxsize']	= $config['upload_maxsize'] ? $config['upload_maxsize'] : C ('UPLOAD_MAXSIZE');
		$config['upload_allowext']	= $config['upload_allowext'] ? $config['upload_allowext'] : 'gif|jpg|jpeg|png|bmp';
		$config['isthumb']			= isset($config['isthumb']) ? $config['isthumb'] : C ('UPLOAD_THUMB_ISTHUMB');
		$config['iswatermark']		= isset($config['iswatermark']) ? $config['iswatermark'] : C ('UPLOAD_WATER_ISWATERMARK');
		$config['water_path']	= $config['water_path'] ? $config['water_path'] : C ('UPLOAD_WATER_PATH') ;


		if (! $config ['isthumb']) {
			$thumb_display = 'display:none';
			$checked_thumb_1 = '';
			$checked_thumb_0 = 'checked';
		} else {
			$thumb_display = '';
			$checked_thumb_1 = 'checked';
			$checked_thumb_0 = '';
		}

		if (! $config ['iswatermark']) {
			$water_display = 'display:none';
			$checked_water_1 = '';
			$checked_water_0 = 'checked';
		} else {
			$water_display = '';
			$checked_water_1 = 'checked';
			$checked_water_0 = '';
		}
		$html = '
		  <table cellpadding="2" cellspacing="1" onclick="javascript:$(\'#minlength\').val(0);$(\'#maxlength\').val(255);">
			<tr>
		      <td>允许上传的图片大小</td>
		      <td><input type="text" name="info[setting][upload_maxsize]" value="' . $config ['upload_maxsize'] . '" size="5">KB 提示：1KB=1024Byte，1MB=1024KB *</td>
		    </tr>
			<tr>
		      <td>允许上传的图片类型</td>
		      <td><input type="text" name="info[setting][upload_allowext]" value="' . $config ['upload_allowext'] . '" size="40"></td>
		    </tr>
			<tr>
		      <td>是否产生缩略图</td>
		      <td><input type="radio" name="info[setting][isthumb]" value="1" ' . $checked_thumb_1 . ' onclick="$(\'#thumb_size\').show()"/> 是 <input type="radio" name="info[setting][isthumb]" value="0" ' . $checked_thumb_0 . ' onclick="$(\'#thumb_size\').hide()"/> 否</td>
		    </tr>
			<tr id="thumb_size" style="' . $thumb_display . '">
		      <td>缩略图大小</td>
		      <td>宽 <input type="text" name="info[setting][thumb_width]" value="' . $config ['thumb_width'] . '" size="3">px 高 <input type="text" name="info[setting][thumb_height]" value="' . $config ['thumb_height'] . '" size="3">px</td>
		    </tr>
			<tr>
		      <td>是否加图片水印</td>
		      <td><input type="radio" name="info[setting][iswatermark]" value="1" ' . $checked_water_1 . ' onclick="$(\'#watermark_img\').show()"/> 是 <input type="radio" name="info[setting][iswatermark]" ' . $checked_water_0 . ' value="0"   onclick="$(\'#watermark_img\').hide()"/> 否</td>
		    </tr>
			<tr id="watermark_img" style="' . $water_display . '">
		      <td>水印图片路径</td>
		      <td><input type="text" name="info[setting][water_path]" value="' . $config ['water_path'] . '" size="40"></td>
		    </tr>
            <tr>
		      <td>裁剪工具最大宽高</td>
		      <td>最大宽 <input type="text" name="info[setting][cut_maxwidth]" value="' . $config ['cut_maxwidth'] . '" size="3">px 最大高 <input type="text" name="info[setting][cut_maxheight]" value="' . $config ['cut_maxheight'] . '" size="3">px</td>
		    </tr>            
		</table>
			';
		return $html;
	}

	/**
	 * 创建物理数据表
	 * 组图
	 * @param $model 数据表模型对象
	 * @param $tableName 要操作的数据表
	 * @param 添加字段时候用户填写的表单数据
	 */
	public function addField($model = '', $tableName = '', $data = array()) {
		if (! $model || empty ( $tableName ))
			return false;
		$sql = "ALTER TABLE `" . C ( 'DB_PREFIX' ) . $tableName . "`
				ADD `{$data['field']}` TEXT  NULL
				DEFAULT NULL
				COMMENT '{$data['name']}'";
		return $model->query ( $sql );
	}
}

?>