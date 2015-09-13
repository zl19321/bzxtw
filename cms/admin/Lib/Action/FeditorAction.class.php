<?php
// +----------------------------------------------------------------------
// | FangFa Net [ http://www.fangfa.net ]
// +----------------------------------------------------------------------
// | File: FdbAction.class.php
// +----------------------------------------------------------------------
// | Date: 2010-5-5
// +----------------------------------------------------------------------
// | Author: Chao <eddy0909@126.com>
// +----------------------------------------------------------------------
// | 文件描述: 编辑器文件管理(目前只支持kindeditor)
// +----------------------------------------------------------------------


defined('IN_ADMIN') or die('Access Denied');

//定义文件名输出编码： windows 输出gbk  其他*nx 输出 utf-8
defined('OUT_ENCODE') or define('OUT_ENCODE', IS_WIN ? 'gbk' : 'utf-8');
/**
 * @name 编辑器文件管理
 *
 */
class FeditorAction extends FbaseAction {


	/**
	 * @name编辑器管理入口
	 */
	public function manage () {
        $in = &$this->in;
        $in['type'] && $in['type'] = trim($in['type']);
        if($in['type'] && in_array($in['type'],array('kind','tinymce'))) {
            $this->{$in['type'].'_manage'}();
        } else {
            die('参数错误！');
        }
	}

	/**
	 * @name上传入口
	 */
	public function upload() {
        $in = &$this->in;
        $in['type'] && $in['type'] = trim($in['type']);
        if($in['type'] && in_array($in['type'],array('kind','tinymce'))) {
            $this->{$in['type'].'_upload'}();
        }
	}
	
	/**
	 * @name kindeditor
	 */
	private function kind_manage() {
        $in = &$this->in;
        //图片扩展名
        if($in['uploadfiletype'] == 'image'){
        	$ext_arr = array('gif', 'jpg', 'jpeg', 'png', 'bmp');
        	//根目录路径，可以指定绝对路径，比如 /var/www/attached/
        	$root_path =  FANGFACMS_ROOT . C ( 'UPLOAD_DIR' ) . 'images/';
        	//根目录URL，可以指定绝对路径，比如 http://www.yoursite.com/attached/
        	$root_url = __ROOT__ . '/' . C ( 'UPLOAD_DIR' ) . 'images/';
        }else if($in['uploadfiletype'] == 'flash'){
        	$ext_arr = array('flv', 'swf');
        	//根目录路径，可以指定绝对路径，比如 /var/www/attached/
        	$root_path =  FANGFACMS_ROOT . C ( 'UPLOAD_DIR' ) . 'media/';
        	//根目录URL，可以指定绝对路径，比如 http://www.yoursite.com/attached/
        	$root_url = __ROOT__ . '/' . C ( 'UPLOAD_DIR' ) . 'media/';
        }else if($in['uploadfiletype'] == 'media'){
        	$ext_arr = array('mp3','wav','wma','wmv','mid','avi','mpg','asf','rm','rmvb');
        	//根目录路径，可以指定绝对路径，比如 /var/www/attached/
        	$root_path =  FANGFACMS_ROOT . C ( 'UPLOAD_DIR' ) . 'media/';
        	//根目录URL，可以指定绝对路径，比如 http://www.yoursite.com/attached/
        	$root_url = __ROOT__ . '/' . C ( 'UPLOAD_DIR' ) . 'media/';
        }else{
			die("非法操作！");
		}
        //根据path参数，设置各路径和URL
        if (empty($in['path'])) {
            $current_path = realpath($root_path) . '/';
            $current_url = $root_url;
            $current_dir_path = '';
            $moveup_dir_path = '';
        } else {
            $current_path = realpath($root_path) . '/' . $in['path'];
            $current_url = $root_url . $in['path'];
            $current_dir_path = $in['path'];
            $moveup_dir_path = preg_replace('/(.*?)[^\/]+\/$/', '$1', $current_dir_path);
        }
        //排序形式，name or size or type
        $order = empty($in['order']) ? 'name' : strtolower($in['order']);

        //不允许使用..移动到上一级目录
        if (preg_match('/\.\./', $current_path)) {
            echo 'Access is not allowed.';
            exit;
        }
        //最后一个字符不是/
        if (!preg_match('/\/$/', $current_path)) {
            echo 'Parameter is not valid.';
            exit;
        }
        //目录不存在或不是目录
        if (!file_exists($current_path) || !is_dir($current_path)) {
            echo 'Directory does not exist.';
            exit;
        }

        //遍历目录取得文件信息
        $file_list = array();
        if ($handle = opendir($current_path)) {
            $i = 0;
            while (false !== ($filename = readdir($handle))) {
                if ($filename{0} == '.') continue;
                $file =$current_path . $filename;
                if (is_dir($file)) {
                    $file_list[$i]['is_dir'] = true; //是否文件夹
                    $file_list[$i]['has_file'] = (count(scandir($file)) > 2); //文件夹是否包含文件
                    $file_list[$i]['filesize'] = 0; //文件大小
                    $file_list[$i]['is_photo'] = false; //是否图片
                    $file_list[$i]['filetype'] = ''; //文件类别，用扩展名判断
                } else {
                    $file_list[$i]['is_dir'] = false;
                    $file_list[$i]['has_file'] = false;
                    $file_list[$i]['filesize'] = filesize($file);
                    $file_list[$i]['dir_path'] = '';
                    $file_ext = strtolower(array_pop(explode('.', trim($file))));
                    $file_list[$i]['is_photo'] = in_array($file_ext, $ext_arr);
                    $file_list[$i]['filetype'] = $file_ext;
                    if(!$file_list[$i]['is_photo']){//将不属于上传类型的文件过滤掉
						 continue;
						}
                }
                $file_list[$i]['filename'] = auto_charset($filename,OUT_ENCODE,'utf-8'); //文件名，包含扩展名
                $file_list[$i]['datetime'] = date('Y-m-d H:i:s', filemtime($file)); //文件最后修改时间
                $i++;
            }
            closedir($handle);
        }
        if(count($file_list)==1 && !$file_list[0]['is_dir'] && !$file_list[0]['is_photo']){//没找到符合的文件类型，清空数组
			array_pop($file_list);
		}else if(count($file_list)>1 && !$file_list[count($file_list)-1]['is_dir'] && !$file_list[count($file_list)-1]['is_photo']){
			array_pop($file_list);//去掉数组最后一个键值为false的键
		}
		if( !empty($file_list) ) {//对文件输出排序
			$file_list = $this -> kind_order($file_list, $order);
		}
        $result = array();
        //$result['ext_arr'] = $file_list;
        //相对于根目录的上一级目录
        $result['moveup_dir_path'] = $moveup_dir_path;
        //相对于根目录的当前目录
        $result['current_dir_path'] = $current_dir_path;
        //当前目录的URL
        $result['current_url'] = $current_url;
        //文件数
        $result['total_count'] = count($file_list);
        //文件列表数组
        $result['file_list'] = $file_list;

        //输出JSON字符串
        header('Content-type: application/json; charset=UTF-8');
        die(json_encode($result));

	}
	/**
	 * @name 对文件排序输出
	 * */
	private function kind_order($file_list, $order){
		//name     or size     or type
		//filename or filesize or filetype
		$key = $order == "name" ? "filename" : $key;
		$key = $order == "size" ? "filesize" : $key;
		$key = $order == "type" ? "filetype" : $key;
		$count = count($file_list);
		for($i = 0; $i < $count; $i ++) {
			for($j= $i + 1; $j < $count; $j ++) { 
				$temp = ""; 
				if ($file_list[$i][$key] > $file_list[$j][$key]){ 
					$temp = $file_list[$i]; 
					$file_list[$i] = $file_list[$j]; 
					$file_list[$j] = $temp; 
				}                 
			} 
		} 
		return $file_list;
	}
	/**
	 * @name上传
	 */
	private function kind_upload() {
		$in = &$this->in;
		//定义允许上传的文件扩展名
		if($in['uploadfiletype']=='image'){//判断上传文件的类型
			$ext_arr = array('gif', 'jpg', 'jpeg', 'png', 'bmp');
			//文件保存目录路径
			$save_path = FANGFACMS_ROOT . C ( 'UPLOAD_DIR' ) . 'images/'.date("Y/m/",time());
			//文件保存目录URL
			$save_url = __ROOT__ . '/' . C ( 'UPLOAD_DIR' ) . 'images/'.date("Y/m/",time());
		}else if($in['uploadfiletype']=='flash'){
			$ext_arr = array('flv', 'swf');
			//文件保存目录路径
			$save_path = FANGFACMS_ROOT . C ( 'UPLOAD_DIR' ) . 'media/';
			//文件保存目录URL
			$save_url = __ROOT__ . '/' . C ( 'UPLOAD_DIR' ) . 'media/';
		}else if($in['uploadfiletype']=='media'){
			$ext_arr = array('mp3','wav','wma','wmv','mid','avi','mpg','asf','rm','rmvb');
			//文件保存目录路径
			$save_path = FANGFACMS_ROOT . C ( 'UPLOAD_DIR' ) . 'media/';
			//文件保存目录URL
			$save_url = __ROOT__ . '/' . C ( 'UPLOAD_DIR' ) . 'media/';
		}else{
			$this->alert("非法操作！");
		}
		//$ext_arr = array('gif', 'jpg', 'jpeg', 'png', 'bmp');
		//最大文件大小
		$max_size = 1000000;
		
		//有上传文件时
		if (empty($_FILES) === false) {
			//原文件名
			$file_name = $_FILES['imgFile']['name'];
			//服务器上临时文件名
			$tmp_name = $_FILES['imgFile']['tmp_name'];
			//文件大小
			$file_size = $_FILES['imgFile']['size'];
			//检查文件名
			if (!$file_name) {
				$this->alert("请选择文件。");
			}
			//检查目录
			if (@mk_dir($save_path) === false) {
				$this->alert("上传目录不存在。");
			}
			//检查目录写权限
			if (@is_writable($save_path) === false) {
				$this->alert("上传目录没有写权限。");
			}
			//检查是否已上传
			if (@is_uploaded_file($tmp_name) === false) {
				$this->alert("临时文件可能不是上传文件。");
			}
			//检查文件大小
			if ($file_size > $max_size) {
				$this->alert("上传文件大小超过限制。");
			}
			//获得文件扩展名
			$temp_arr = explode(".", $file_name);
			$file_ext = array_pop($temp_arr);
			$file_ext = trim($file_ext);
			$file_ext = strtolower($file_ext);
			//获得文件名
			$filname_noext = array_pop($temp_arr);
			$filname_noext = trim($filname_noext);
			$filname_noext = iconv('utf-8',OUT_ENCODE,$filname_noext);//对名称编码，解决中文名称问题
			//检查扩展名
			if (in_array($file_ext, $ext_arr) === false) {
				$this->alert("上传文件扩展名是不允许的扩展名");
			}
			//新文件名
			// mark  2011-8-12 修改文件名称和保存路径，防止同目录上传文件数量过大而访问过慢的问题
			$new_file_name = date("YmdHis") . rand(10000, 99999) . '.' . $file_ext; 
//			$i=1;
//			while(file_exists($save_path . '/' . $filname_noext.'('.$i.').' . $file_ext)){
//				$i++;
//			}
//			$new_file_name = $filname_noext.'('.$i.').' . $file_ext;
			//移动文件
			$file_path = $save_path . $new_file_name;
			
			if (move_uploaded_file($tmp_name, $file_path) === false) {
				$this->alert("上传文件失败。");
			}
			@chmod($file_path, 0644);
			$file_url = $save_url . $new_file_name;
			$file_url = iconv(OUT_ENCODE,'UTF-8',$file_url);
			header('Content-type: text/html; charset=UTF-8');
			die(json_encode(array('error' => 0, 'url' => $file_url)));
		}
	}

	/**
	 * @name返回json数据
	 * @param unknown_type $msg
	 */
    private function alert($msg) {
        header('Content-type: text/html; charset=UTF-8');
        echo json_encode(array('error' => 1, 'message' => $msg));
        exit;
    }

    /**
     * @name tinymce管理
     */
	private function tinymce_manage() {

	}

	/**
	 * @name tinymce上传
	 */
	private function tinymce_upload() {

	}



}