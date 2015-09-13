<?php
class FpressAction extends FbaseAction
{
	protected $_category = '';
	protected $_category_data = '';
	
	protected $_data ;
	protected $_p = "";
	protected $_action ="";

	/**
	 * @name初始化
	 */
	protected function _initialize()
	{
		
		parent::_initialize();
		//查找要执行的动作
		$this->_p = D ('Press');
		$where = array(
			'url' => $this->_urls['dburl'],
			'status' => '9'
		);
		$this->_data = $this->_p->where($where)->find();
		if (!empty($this->_data)) {
			
			$this->_action = 'show';
		} else {  //栏目页			
			if ($this->_urls['dburl'] == 'index' . C ('URL_HTML_SUFFIX')) {
				$this->_action = 'index';
			} else {
				if($this->view()){
					
				}else{
					$this->h404();
				}
			}
		}
	}
	/**
	 * @name分发操作
	 */
	 
	public function _empty() {
		if (ACTION_NAME == '_empty') {
			$this->h404();
		} else {  //初始化，分析得到要分发到的action			
			if (method_exists($this,$this->_action)) {				
				$this->_category_data = F ("category_".CATID);
				$this->{$this->_action}();
			}
		}
	}
	/**
	 * @name投票列表
	 */
	public function index() {
		$in = &$this->in;		
		//seo设置
		$seo['seotitle'] = $this->_category_data['seotitle'] ? $this->_category_data['seotitle'] : $this->_category_data['name'];
		$seo['seokeywords'] = $this->_category_data['seokeywords'] ? $this->_category_data['seokeywords'] : $this->_category_data['name'];
		$seo['seodescription'] = $this->_category_data['seodescription'] ? $this->_category_data['seodescription'] : $this->_category_data['description'];		
		//指定当前页面唯一链接			
		if ($this->_page > 1) {
			$seo['url'] = C ('SITEURL') . $this->_category_data['url'] . 'index_' . $this->_page . C('URL_HTML_SUFFIX');
		} else {
			$seo['url'] = C ('SITEURL') . $this->_category_data['url'];
		}
		//字符替换
		$seo = parent::meta_replace($seo);		
		$this->assign('seo',$seo); //meta信息
		$this->assign('cat',$this->_category_data);	//栏目信息	
		$this->assign('p',$this->_page); //当前页码
		$this->display($this->_category_data['setting']['template']['index']);
	}
	/**
	 * @name投票列表
	 */
	public function show(){
		import('Pager',INCLUDE_PATH);
		$in = &$this->in;
		//查询具体记录的所有相关信息：  扩展表、统计表、tag
		$this->_category_data = F ("category_".CATID);
		$this->assign('cat',$this->_category_data);
		$this->assign('comment_open', C('CONTENT_COMMENT_OPEN')); //是否开启评论
		//获取数据
		$options = array(
			'where' => array('url'=>$this->_urls['dburl']),
		);
		
		//格式化输出数据
		import('Field',INCLUDE_PATH);
		$fieldArr = D('ModelField')->where("`modelid`='".$this->_category_data['modelid']."'  AND `systype`<>'2'  AND `status`='1'")->order(' `sort` ASC')->findAll();
	
		foreach ($fieldArr as $k=>$v) {
			$value = F ('modelField_'.$v['fieldid']);
			if (is_array($value)) {  //组合成 array($formtype1=>'',$formtype2=>'',$formtype3=>'',)的形式
				$field[$value['field']] = $value;
			}
		}
		if (is_array($field)) {
			foreach ($field as $k=>$v)  {
				Field::output($v['formtype'],$k, $v['setting'],$this->_data);
			}
		}
		//seo设置
		$this->_data = parent::meta_replace($this->_data);
		$seo['seotitle'] = &$this->_data['seotitle'];
		$seo['seokeywords'] = &$this->_data['seokeywords'];
		$seo['seodescription'] = &$this->_data['seodescription'];
		//内容分页
		$pager = new Pager($data['content'],$this->_page);			
		$data['pages'] = $pager->navbar($this->_category_data['url'] . $this->_urls['baseurl'] . '_{page}.html');
		
		$this->assign('seo',$seo);
		$this->assign('data',$this->_data);
		$this->display($this->_category_data['setting']['template']['show']);
	}
	public function view(){
		$in = &$this->in;
		$i = 0;	
		header("Content-type: text/html; charset=utf-8");
		$data = $this->_p->where("pid=".$in['pid']." and status=9")->find();
		$category_data = F ("category_".$data['catid']);
		//格式化输出数据
		import('Field',INCLUDE_PATH);
		$fieldArr = D('ModelField')->where("`modelid`='".$category_data['modelid']."'  AND `systype`<>'2'  AND `status`='1'")->order(' `sort` ASC')->findAll();
	
		foreach ($fieldArr as $k=>$v) {
			$value = F ('modelField_'.$v['fieldid']);
			if (is_array($value)) {  //组合成 array($formtype1=>'',$formtype2=>'',$formtype3=>'',)的形式
				$field[$value['field']] = $value;
			}
		}
		if (is_array($field)) {
			foreach ($field as $k=>$v)  {
				Field::output($v['formtype'],$k, $v['setting'],$data);
			}
		}
		//定义flash文件
		$upload = C("PRESS_DIR");
		$dir = $upload .  $in['pid'];
		$sound = $dir."/bgSound";
		$Bimg  = $dir."/Bimg_3";
		$img   = $dir."/img";
		$thumb = $dir."/thumb_3";
		$dirs = array($dir, $sound,$Bimg, $img, $thumb );
		foreach ($dirs as $t){
			if(!is_dir($t))  {
				mkdir($t, 0777);
			}
		}

		if (!empty($data['music'])){
			copy( str_replace(__ROOT__ . "/", "", $data['music']), $sound."/bgsound1.mp3");
		}
		if ( !empty($data['up']) ) {
			$i++;
			copy(str_replace(__ROOT__ . "/", "", $data['up']), $img."/page_cover.jpg");
		}
		if ( !empty($data['down']) ) {
			$i++;
			copy(str_replace(__ROOT__ . "/", "", $data['down']), $img."/back_cover.jpg");
		}
		copy("public/flash/onLine.swf", $dir."/onLine.swf");
		$j = 1;
		foreach ($data['images'] as $v){
			$image = str_replace(__ROOT__ . "/", "", $v['image']);
			$path = pathinfo($image);
			$t = copy($image, $img."/".$j.".".$path['extension']);
			if($t){ $i++; $j ++; }
		}
		$exe = $dir."/index.php";
		$f = fopen($exe, "w+");
$str = <<< END
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>SHOWPIECE总第12期</title>
</head>
<style type="text/css">
	body{margin:0;padding:0; background:#000}
	.wrap{margin:auto}
</style>
<body>
	<div class="wrap" style="text-align:center">
		<object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=9,0,28,0" width="1024" height="768">
			<param name="movie" value="onLine.swf?Maxpage=$i" />
			<param name="quality" value="high" />
			<embed src="onLine.swf?Maxpage=$i" quality="high" pluginspage="http://www.adobe.com/shockwave/download/download.cgi?P1_Prod_Version=ShockwaveFlash" type="application/x-shockwave-flash" width="1024" height="768"></embed>
		</object>
	</div>
</body>
</html>
END;
		fwrite($f,$str );
		fclose($f);
		redirect(__ROOT__."/" . $dir);
		
	}
	
}

?>
