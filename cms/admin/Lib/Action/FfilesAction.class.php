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
// | 文件描述: 文件管理
// +----------------------------------------------------------------------

defined ( 'IN_ADMIN' ) or die ( 'Access Denied' );
//定义文件名输出编码：保存文件的时候 windows 输出gbk  其他*nx 输出 utf-8
defined('OUT_ENCODE') or define('OUT_ENCODE', IS_WIN ? 'gbk' : 'utf-8');
/**
 * @name 文件管理
 *
 */
class FfilesAction extends FbaseAction {

	/**
	 * @name图片库列表
	 */
	public function images() {
		$in = &$this->in;
		if ($in ['do'] && $in ['do'] == 'cutimg')
			$this->cutimg ();
		if ($in ['do'] && $in ['do'] == 'delete')
			$this->delete_image();
		if (empty ( $in ['path'] ) || $in ['path'] == '/')
			$in ['path'] = '';
		$path = &$in ['path']; //当前浏览路径
		//当前物理路径
		$upload_dir = FANGFACMS_ROOT . C ( 'UPLOAD_DIR' ) . 'images/';
		$upload_url = C ( 'UPLOAD_URL' ) . 'images/';
		//可以显示的图片类型
		$images_ext = explode ( '|', C ( 'UPLOAD_IMAGES_ALLOWEXT' ) );
		$now_dir = realpath ( $upload_dir . $path );
		if (! file_exists ( $now_dir )) {
			$this->error ( '参数错误！' );
		}
		//遍历当前文件夹
		$handle = opendir ( $now_dir );
		if (false !== $handle) {
			$files = array (); //所有文件
			while ( false !== ($file = readdir ( $handle )) ) {
				if ($file{0} != "." ) {
					$filename = $now_dir . '/' . $file;
					$isdir = ( int ) is_dir ( $filename );
					$filetype = filetype ( $filename );
					if ($filetype == 'file') {
						$fileinfo = pathinfo ( $filename );
						if (empty ( $fileinfo ['extension'] ) || ! in_array ( strtolower ( $fileinfo ['extension'] ), $images_ext )) {
							continue;
						}
					} else if ($isdir) {
						//屏蔽FrontPage扩展目录和linux隐蔽目录
						if (preg_match ( "/^_(.*)$/", $filename ))
							continue;
						if (preg_match ( "/^\.(.*)$/", $filename ))
							continue;
					}
					//得到相对路径
					$url = realpath ( $now_dir . '/' . $file );
					$url = str_replace ( realpath ( $upload_dir ), '', $url );
					$url = str_replace ( '\\', '/', $url );
					$url = rtrim($url,'/\\');
					$files [] = array (
						'isdir' => $isdir,
						'filename' => auto_charset($file,OUT_ENCODE,'utf-8'),
						'filemtime' => filemtime ( $filename ),
						'filetype' => $filetype,
						'filesize' => byte_format ( filesize ( $filename ) ),
						'url' => $url,
						'imageurl' => auto_charset('images/' . ltrim($url, '\\/'),OUT_ENCODE,'utf-8'),
						'fullurl' => auto_charset(WEB_PUBLIC_PATH . '/' . $upload_url . $url,OUT_ENCODE,'utf-8'),
					);
				}
			}
			closedir ( $handle );
		}
		//数据
		$path = empty ( $path ) ? '/' : $path;
		if ($path != '/') { //父级目录
			$path_arr = explode ( '/', $path );
			array_pop ( $path_arr );
			$parent_path = implode ( '/', $path_arr );
		} else {
			$parent_path = $path;
		}

		$data = array (
			'files' => $files,
			'path' => $path,
			'parent_path' => $parent_path,
			'opener_id' => $in ['opener_id'],
			'upload_dir' => C('UPLOAD_DIR'),
		);
		if (!empty($in['shower_id'])) $data['shower_id'] = $in['shower_id'];
		usort($data['files'], array($this, 'imageFileSort'));
		$this->assign ( 'data', $data );
		$this->assign('forward',urlencode ($_SERVER['REQUEST_URI']));
		$this->display ();
	}

	/**
	 * @name图片裁剪
	 */
	protected function cutimg() {
		$in = &$this->in;
		$upload_dir = FANGFACMS_ROOT . C ( 'UPLOAD_DIR' );
		$upload_url = C ( 'UPLOAD_URL' );
		$filepath = $upload_dir . $in ['file'];
		$fileurl = $upload_url . $in ['file'];
		if ($this->ispost()) {
		  echo $fileurl;
          exit;
			$in['w'] = intval($in['w']);
			$in['h'] = intval($in['h']);
			$in['x'] = intval($in['x']);
			$in['y'] = intval($in['y']);
            $filepath = auto_charset($filepath,'utf-8',OUT_ENCODE);
            if(is_file($filepath)) {
                import('ImageResize',INCLUDE_PATH);
                $_imageResize = new ImageResize();
                $_imageResize->load($filepath);
                if ($_imageResize->cut($in['w'],$in['h'],$in['x'],$in['y'])) {
                    //保存图片、覆盖原图
                    if ($_imageResize->save($filepath,false)) {
                        die($fileurl);
                    }
                }
            }
            die('n');

		}
		$this->assign('fileurl',$fileurl);
		$this->assign('file',$in['file']);

		$this->display ( 'cutimg' );
		exit ();
	}

	/**
	 * @name模板库
	 */
	public function tpl() {
		$in = &$this->in;
		empty($in ['path']) && $in ['path'] = '/';
		$path = &$in ['path']; //当前浏览路径
		//当前物理路径
		$tpl_dir = realpath(FRONT_TEMPLATE_PATH);
		//可以显示的模板文件类型
		$images_ext = array (
			'html', 'htm'
		);
		$now_dir = realpath ( $tpl_dir . $path );
		if (! file_exists ( $now_dir )) {
			$this->error ( '参数错误！' );
		}
		import('ORG.Io.Dir');
		$dir = new Dir($now_dir);
		$data = $dir->toArray();
		if (is_array($data)) {
			foreach ($data as $k=>$v) {
				$data[$k]['path'] = rtrim(str_replace($tpl_dir,'',$v['pathname']),'/\\');
				$data[$k]['path'] = ltrim(str_replace($tpl_dir,'',$v['pathname']),'/\\');
//				$data[$k]['filename'] = auto_charset($data[$k]['filename'], OUT_ENCODE, 'utf-8');
				if (strpos($data[$k]['path'],'\\'))
					$data[$k]['path'] = str_replace('\\','/',$data[$k]['path']);
			}
		}
		//当前目录，上级目录
		$pathArr = explode('/',$path);
		array_pop($pathArr);
		if (empty($pathArr)) {
			$parent_path = '/';
		} else {
			$parent_path = '/' . implode('/',$pathArr);
		}
		usort($data, array($this, 'tplFileSort'));
		$this->assign ( 'data', $data );
		$this->assign ( 'parent_path', $parent_path );
		$this->assign ( 'now_path', $path );
		$this->assign ( 'opener_id', $in['opener_id'] );
		$this->display ();
	}

	/**
	 * @name删除图片
	 *
	 */
	protected function delete_image() {
		$in = &$this->in;
		if ($in['file']) {  //TODO
			$upload_dir = FANGFACMS_ROOT . C ( 'UPLOAD_DIR' );
			$upload_url = C ( 'UPLOAD_URL' );
			$filename = $upload_dir . $in['file'];
            $filename = auto_charset($filename,'utf-8',OUT_ENCODE);
			if (is_file($filename)) {
				@unlink($filename);
				$message = L(basename ($filename).'删除成功！');
				$this->assign('message',$message);
			}
		}
		if ($in['forward']) {
			header('location:'.urldecode($in['forward']));
		}
	}

	/**
	 * @name 对站内图片文件进行排序
	 *
	 * @param array $a
	 * @param array $b
	 * @return boolean
	 */
	protected function imageFileSort($a, $b)
	{
		if ($a['isdir']==1 && $b['isdir']==0) {
			return false;
		} else if ($a['isdir']==0 && $b['isdir']==1) {
			return true;
		} else {
			return strcmp($a["filename"], $b["filename"]);
		}
	}

	/**
	 * @name 对选择模板排序
	 * @param unknown_type $a
	 * @param unknown_type $b
	 */
	protected function tplFileSort($a, $b)
	{
		if ($a['isDir']==true && $b['isDir']==false) {
			return false;
		} else if ($a['isDir']==false && $b['isDir']==true) {
			return true;
		} else {
			return strcmp($a["filename"], $b["filename"]);
		}
	}
}