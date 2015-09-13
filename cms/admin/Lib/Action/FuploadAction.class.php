<?php
// +----------------------------------------------------------------------
// | FangFa Net [ http://www.fangfa.net ]
// +----------------------------------------------------------------------
// | File: FuploadAction.class.php
// +----------------------------------------------------------------------
// | Date: 2010-4-28
// +----------------------------------------------------------------------
// | Author: fangfa <1364119331@qq.com>
// +----------------------------------------------------------------------
// | 文件描述: 文件上传处理
// +----------------------------------------------------------------------

defined ( 'IN_ADMIN' ) or die ( 'Access Denied' );

//定义文件名输出编码： windows 输出gbk  其他*nx 输出 utf-8
defined('OUT_ENCODE') or define('OUT_ENCODE', IS_WIN ? 'gbk' : 'utf-8');
/**
 * @name 文件上传处理
 *
 */
class FuploadAction extends FbaseAction {
	/**
	 * 由于firefox在处理flash的session方面的BUG，我们需要单独处理flash的session，所以在基础类中不作权限判断
	 *
	 * @var boolean
	 */
	protected $needAuth = false;

	/**
	 * @name处理模型上传字段的上传动作，包括普通附件和图片
	 */
	public function fieldupload() {
		$in = &$this->in;
		$in ['fieldid'] = intval ( $in ['fieldid'] );
		if (! $in ['fieldid']) {
			return ;
		}
		$_field = D ( 'ModelField' );
		$field_data = $_field->find ( $in ['fieldid'] );
		//允许上传类型
		$fileext = explode ( '|', ! empty ( $field_data ['setting'] ['upload_allowext'] ) ? $field_data ['setting'] ['upload_allowext'] : C ( 'UPLOAD_IMAGES_ALLOWEXT' ) );
		//允许上传大小
		$allowSize = ! empty ( $field_data ['setting'] ['upload_maxsize'] ) ? $field_data ['setting'] ['upload_maxsize'] : C ( 'UPLOAD_MAXSIZE' );
		$allowSize = intval ( $allowSize );
		//字段定义的默认缩略图宽高
		//print_r($field_data);
		if ( in_array($field_data['formtype'], array('images', 'thumb'))) {//如果是图片
			$this->assign('uploadImage',$uploadImage = true);
			if ( $field_data['setting']['isthumb']) {//是否产生缩略图
				$this->assign('width',$field_data['setting']['thumb_width']);
				$this->assign('height',$field_data['setting']['thumb_height']);
			}
		}
		if (! empty ( $_FILES )) { //处理上传
			//判断类型，如果不是上传图片交给_filesUpload()处理
			if(!$uploadImage) {
				$this->_filesUpload ( $field_data );
				exit;
			}
			//处理session
			if ($in['sid'] != session_id()) {
				session_destroy(); //情况当前session
				session_id($in['sid']); //重新定义session
				session_start();
			}
			//判断权限
			if (!parent::checkRbac('','',true)) {
				die(json_encode(
					array(
					'code' => 'n',
					'info' => '没有权限进行此操作！'
					)
				));
			}
			import ( 'ORG.Net.UploadFile' );
			$upload = get_instance_of ( 'UploadFile' ); // 实例化上传类
			$upload->maxSize = $allowSize*1024;  //转成Byte
			$upload->allowExts = $fileext;
			$upload->savePath = FANGFACMS_ROOT . C ( 'UPLOAD_DIR' ) .'images/';
			//文件保存命名规则
			$upload->saveRule = C ( 'UPLOAD_FILE_RULE' );
			if ($upload->upload ()) {
				$fileinfo = $upload->getUploadFileInfo ();
				$result = array (
					'code' => 'y',
					'info' => 'images/' . $fileinfo [0]['savename'],
					'upload_dir' => __ROOT__ . '/' . C ( 'UPLOAD_DIR' ),
					'type'	=>	'image',
					'name'	=>	htmlspecialchars($fileinfo[0]['name']),
				);
                
                $upload_dir = FANGFACMS_ROOT . C ( 'UPLOAD_DIR' );
		        $filepath = $upload_dir . 'images/' . $fileinfo [0]['savename'];
                
                import('ImageResize',INCLUDE_PATH);
                $_imageResize = new ImageResize();
                $_imageResize->load($filepath);
                
                $img = getimagesize($filepath);
                
                if(C("is_cut") == 1){
                    
                    $cutSize = C('CUT_SIZE') < 475?475:C('CUT_SIZE');
                                                
                    if($img[0] > $cutSize){
                        $bl = round($cutSize/$img[0],2);
                        $width = $cutSize;
                        $height = ceil($bl*$img[1]);
                        $_imageResize->resize($width,$height);
                        $_imageResize->save($filepath,false);
                    }     
                
                }                                
				//缩略图、添加水印等
				if ($field_data ['setting'] ['isthumb']) { //生成缩略图
	                import ('ORG.Util.Image');
	                $thumbMaxWidth = $field_data ['setting'] ['thumb_width'] ? $field_data ['setting'] ['thumb_width'] : C ( 'UPLOAD_THUMB_WIDTH' );
					$thumbMaxHeight = $field_data ['setting'] ['thumb_height'] ? $field_data ['setting'] ['thumb_height'] : C ( 'UPLOAD_THUMB_HEIGHT' );
					$imageFileName = FANGFACMS_ROOT . C ( 'UPLOAD_DIR' ) . 'images/' . $fileinfo[0]['savename'];
					$thumbFileName = dirname($imageFileName) . '/thumb/' . basename($imageFileName);
					$imageFileName = auto_charset($imageFileName, 'utf-8', OUT_ENCODE);
					$thumbFileName = auto_charset($thumbFileName, 'utf-8', OUT_ENCODE);
	                Image::thumb($imageFileName, $thumbFileName, $thumbMaxWidth, $thumbMaxHeight);
				}
				if ($field_data ['setting'] ['iswatermark']) {//加水印
	                import ('ORG.Util.Image');
					$waterPath = FANGFACMS_ROOT . 'public/' . (! empty ( $field_data ['setting'] ['water_path'] ) ? $field_data ['setting'] ['water_path'] : C ( 'UPLOAD_WATER_PATH' ) );
					$waterPlace = $field_data ['setting'] ['water_palce'] ? $field_data ['setting'] ['water_palce'] : C ( 'UPLOAD_WATER_PLACE' );
					$waterTrans = C ( 'UPLOAD_WATER_TRANS' );
					$imageFileName = FANGFACMS_ROOT . C ( 'UPLOAD_DIR' ) . 'images/' . $fileinfo[0]['savename'];
					$imageFileName = auto_charset($imageFileName, 'utf-8', OUT_ENCODE);
					Image::waterMark($imageFileName, $waterPath, $waterTrans, 90 ,$waterPlace);
				}
			} else {
				$result = array (
					'code' => 'n',
					'info' => $upload->getErrorMsg ()
				);
			}
			die ( json_encode ( $result ) );
		}
		//判断权限
		parent::checkRbac();
		//给uploadify设定参数
		if (is_array ( $fileext )) {
			foreach ( $fileext as $k => $v ) {
				$fileext [$k] = '*.' . $v;
			}
			$fileext = implode ( ';', $fileext );
		} else {
			return;
		}
		$args = array (
			'm' => 'fupload',
			'a' => 'fieldupload',
			'fieldid' => $field_data ['fieldid'],
			'dosubmit' => 1,
			'sid' => session_id(),  //传递PHPSESSID
			'upload_maxsize' => $allowSize,
		);
		$scriptData = json_encode ( $args );
		$data = array (
			'fileext' => $fileext,
			'scriptData' => $scriptData,
			'allowSize' => $allowSize,
		);
		$data['multi'] = isset($in['multi']) ? $in['multi'] : false;  //是否支持多文件或多图 true支持 false 不支持
		$data['multi_name'] = (!empty($in ['multi_name']) ? $in ['multi_name'] : 'info['. $field_data['field'] .'][]'); //多图或多文件返回表单隐藏域name
		if (!empty($in['opener_id'])) $data['opener_id'] = $in ['opener_id'];  //单图返回表单id
		if (!empty($in['shower_id'])) $data['shower_id'] = $in ['shower_id']; //图片展示容器
		$this->assign ( 'data', $data );
		$this->display ();
	}

	/**
	 * @name文件上传处理
	 * @param array $field_data 字段信息
	 */
	protected function _filesUpload($field_data) {
		$in = &$this->in;
		$m_path = ($field_data['formtype'] == 'video' ? 'media/' : 'files/');  //判断是视屏还是文件，存放在不同目录下

		$fileext = explode ( '|', ! empty ( $field_data ['setting'] ['upload_allowext'] ) ? $field_data ['setting'] ['upload_allowext'] : C ( 'UPLOAD_ATTACHMENT_ALLOWEXT' ) );
		//允许上传大小
		$allowSize = ! empty ( $field_data ['setting'] ['upload_maxsize'] ) ? $field_data ['setting'] ['upload_maxsize'] : C ( 'UPLOAD_MAXSIZE' );
		$allowSize = intval ( $allowSize ) * 1024;
		if (! empty ( $_FILES )) { //处理上传
			//处理session
			if ($in['sid'] != session_id()) {
				session_destroy(); //情况当前session
				session_id($in['sid']); //重新定义session
				session_start();
			}
			if (!parent::checkRbac('','',true)) {
				die(json_encode(
					array(
					'code' => 'n',
					'info' => '没有权限进行此操作！'
					)
				));
			}
			import ( 'ORG.Net.UploadFile' );
			$upload = get_instance_of ( 'UploadFile' ); // 实例化上传类
			$upload->maxSize = $allowSize;
			$upload->allowExts = $fileext;
			$upload->savePath = FANGFACMS_ROOT . C ( 'UPLOAD_DIR' ) . $m_path;
			//文件保存命名规则
			$upload->saveRule = C ( 'UPLOAD_FILE_RULE' );

			if ($upload->upload ()) {
				$fileinfo = $upload->getUploadFileInfo ();
				$result = array (
					'code' => 'y',
					'info' => $m_path . $fileinfo[0]['savename'],
					'type' => 'file',
					'name' => htmlspecialchars($fileinfo[0]['name']),
					'size' => byte_format($fileinfo[0]['size']),
					'size_input' => (isset($field_data['setting']['size_input']) ? $field_data['setting']['size_input'] : '')
				);
			} else {
				$result = array (
					'code' => 'n', 'info' => $upload->getErrorMsg ()
				);
			}
			die ( json_encode ( $result ) );
		}
		//判断权限
		parent::checkRbac();
		exit ();
	}

	/**
	 * @name普遍上传
	 */
	public function CommonUpload()
	{
		$in = &$this->in;

		$savePath = FANGFACMS_ROOT . C ( 'UPLOAD_DIR' ) . 'images/';
		if (!file_exists($savePath)) {
			mk_dir($savePath);
		}
		//允许上传类型
		$fileext = explode ( '|', ! empty ( $in['upload_allowext'] ) ? $in['upload_allowext'] : C ( 'UPLOAD_IMAGES_ALLOWEXT' ) );
		//允许上传大小
		$allowSize = ! empty ( $in['upload_maxsize'] ) ? $in['upload_maxsize'] : C ( 'UPLOAD_MAXSIZE' );
		$allowSize = intval ( $allowSize );
		if (! empty ( $_FILES )) { //处理上传

			$current_file = current($_FILES);  //判断是图片还是其他文件
			if (!in_array(strtolower(strrchr($current_file['name'], '.')), array('.gif', '.jpeg', '.jpg', '.png', '.bmp'))) {
				$this->_commonFilesUpload();
				exit;
			}

			//处理session
			if ($in['sid'] != session_id()) {
				session_destroy(); //情况当前session
				session_id($in['sid']); //重新定义session
				session_start();
			}

			if (!parent::checkRbac('','',true)) {
				die(json_encode(
					array(
					'code' => 'n',
					'info' => '没有权限进行此操作！'
					)
				));
			}

			import ( 'ORG.Net.UploadFile' );
			$upload = get_instance_of ( 'UploadFile' ); // 实例化上传类
			$upload->maxSize = $allowSize*1024;
			$upload->allowExts = $fileext;
			$upload->savePath = $savePath;
			//文件保存命名规则
			$upload->saveRule = C ( 'UPLOAD_FILE_RULE' );
			if ($upload->upload ()) {
				$fileinfo = $upload->getUploadFileInfo ();
				$result = array (
					'code' => 'y',
					'upload_dir' => __ROOT__ . '/' . C ( 'UPLOAD_DIR' ) . '/',
					'info' => 'images/' . $fileinfo [0]['savename'],
					'type'	=>	'image',
					'name'	=>	htmlspecialchars($fileinfo[0]['name']),
				);
				//缩略图、添加水印等
				if (C('UPLOAD_THUMB_ISTHUMB')) { //生成缩略图
	                import ('ORG.Util.Image');
	                $thumbMaxWidth = C ( 'UPLOAD_THUMB_WIDTH' );
					$thumbMaxHeight = C ( 'UPLOAD_THUMB_HEIGHT' );
					$imageFileName = FANGFACMS_ROOT . C ( 'UPLOAD_DIR' ) . 'images/' . $fileinfo[0]['savename'];
					$thumbFileName = dirname($imageFileName) . '/thumb/' . basename($imageFileName);
					$imageFileName = auto_charset($imageFileName, 'utf-8', OUT_ENCODE);
					$thumbFileName = auto_charset($thumbFileName, 'utf-8', OUT_ENCODE);
	                Image::thumb($imageFileName, $thumbFileName, $thumbMaxWidth, $thumbMaxHeight);
				}
				if (C('UPLOAD_WATER_ISWATERMARK')) {//加水印
	                import ('ORG.Util.Image');
					$waterPath = FANGFACMS_ROOT . 'public/' . C ( 'UPLOAD_WATER_PATH' ) ;
					$waterPlace = C ( 'UPLOAD_WATER_PLACE' );
					$waterTrans = C ( 'UPLOAD_WATER_TRANS' );
					$imageFileName = FANGFACMS_ROOT . C ( 'UPLOAD_DIR' ) . 'images/' . $fileinfo[0]['savename'];
					$imageFileName = auto_charset($imageFileName, 'utf-8', OUT_ENCODE);
					Image::waterMark($imageFileName, $waterPath, $waterTrans, 90 ,$waterPlace);
				}
			} else {
				$result = array (
					'code' => 'n',
					'info' => $upload->getErrorMsg ()
				);
			}
			die ( json_encode ( $result ) );
		}
		//判断权限
		parent::checkRbac();
		//给uploadify设定参数
		if (is_array ( $fileext )) {
			foreach ( $fileext as $k => $v ) {
				$fileext [$k] = '*.' . $v;
			}
			$fileext = implode ( ';', $fileext );
		} else {
			return;
		}
		$args = array (
			'm' => 'fupload',
			'a' => 'CommonUpload',
			'dosubmit' => 1,
			'sid' => session_id(),  //传递PHPSESSID
		);
		if (!empty($in['isthumb'])) $args['isthumb'] = $in['isthumb'];
		if (!empty($in['thumb_width'])) $args['thumb_width'] = $in['thumb_width'];
		if (!empty($in['thumb_height'])) $args['thumb_height'] = $in['thumb_height'];
		if (!empty($in['iswatermark'])) $args['iswatermark'] = $in['iswatermark'];
		if (!empty($in['water_path'])) $args['water_path'] = $in['water_path'];
		if (!empty($in['water_place'])) $args['water_place'] = $in['water_place'];
		if (!empty($in['upload_allowext'])) $args['upload_allowext'] = $in['upload_allowext'];

		$scriptData = json_encode ( $args );
		$data = array (
			'fileext' => $fileext,
			'scriptData' => $scriptData,
			'allowSize' => $allowSize,
		);
		$data['multi'] = isset($in['multi']) ? $in['multi'] : false;  //是否支持多文件或多图 true支持 false 不支持
		$data['multi_name'] = (!empty($in ['multi_name']) ? $in ['multi_name'] : 'files[]'); //多图或多文件返回表单隐藏域name
		if (!empty($in['opener_id'])) $data['opener_id'] = $in ['opener_id'];  //单图返回表单id
		if (!empty($in['shower_id'])) $data['shower_id'] = $in ['shower_id']; //图片展示容器
		$this->assign ( 'data', $data );
		$this->display ();
	}

	/**
	 * @name普通的文件上传处理
	 * @param array $field_data 字段信息
	 */
	protected function _commonFilesUpload() {
		$in = &$this->in;
		$fileext = explode ( '|', ! empty ( $in['upload_allowext'] ) ? $in['upload_allowext'] : C ( 'UPLOAD_ATTACHMENT_ALLOWEXT' ) );
		//允许上传大小
		$allowSize = ! empty ( $in['upload_maxsize'] ) ? $in['upload_maxsize'] : C ( 'UPLOAD_MAXSIZE' );
		$allowSize = intval ( $allowSize ) * 1024;
		if (! empty ( $_FILES )) { //处理上传
			//处理session
			if ($in['sid'] != session_id()) {
				session_destroy(); //情况当前session
				session_id($in['sid']); //重新定义session
				session_start();
			}
			if (!parent::checkRbac('','',true)) {
				die(json_encode(
					array(
					'code' => 'n',
					'info' => '没有权限进行此操作！'
					)
				));
			}
			import ( 'ORG.Net.UploadFile' );
			$upload = get_instance_of ( 'UploadFile' ); // 实例化上传类
			$upload->maxSize = $allowSize * 1024;
			$upload->allowExts = $fileext;
			$upload->savePath = FANGFACMS_ROOT . C ( 'UPLOAD_DIR' ) .'files/';
			//文件保存命名规则
			$upload->saveRule = C ( 'UPLOAD_FILE_RULE' );

			if ($upload->upload ()) {
				$fileinfo = $upload->getUploadFileInfo ();
				$result = array (
					'code' => 'y',
					'info' => 'files/' . $fileinfo[0]['savename'],
					'type'	=>	'file',
					'name'	=>	htmlspecialchars($fileinfo[0]['name']),
				);
			} else {
				$result = array (
					'code' => 'n', 'info' => $upload->getErrorMsg ()
				);
			}
			die ( json_encode ( $result ) );
		}
		//判断权限
		parent::checkRbac();
		exit ();
	}
}
?>