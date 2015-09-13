<?php

// +----------------------------------------------------------------------

// | FangFa Net [ http://www.fangfa.net ]

// +----------------------------------------------------------------------

// | File: FindexAction.class.php

// +----------------------------------------------------------------------

// | Date: Thu Apr 22 15:43:11 CST 2010

// +----------------------------------------------------------------------

// | Author: fangfa <1364119331@qq.com>

// +----------------------------------------------------------------------

// | 文件描述: 前台首页

// +----------------------------------------------------------------------



defined ( 'IN' ) or die ( 'Access Denied' );

/**

 * @name 前台首页

 *

 */

class FindexAction extends FbaseAction {

	

	/**

	 * @name 初始化

	 */

	protected function _initialize() {

		parent::_initialize();

		//检查 REQUEST_FILE
		if (REQUEST_FILE != 'index' . C('URL_HTML_SUFFIX')) {

            if(!method_exists($this,ACTION_NAME)) {

                $this->h404();

            }

		}

	}

	

	/**

	 * @name空模块

	 * 

	 */

	public function _empty() {

		if (ACTION_NAME == '_empty') {

			$this->h404();

		} else {  //分发到index

			$this->index();

		}

	}



		/**

	 * @name验证码

	 */

	public function verify() {

		import ( "ORG.Util.Image" );

		Image::buildImageVerify ();

	}



	/**

	 * @name系统首页

	 * 

	 */

	public function index() {

		$in = &$this->in;		

		//站点信息：站点标题、关键字、描述

		$seo['seotitle'] = C('SEOTITLE');

		$seo['seokeywords'] = C('SEOKEYWORDS');

		$seo['seodescription'] = C('SEODESCRIPTION');		

	    $seo['url'] = C ('SITEURL');

		$_category=M('Category');

		$index_description=$_category->where('catid=18')->find();//显示在首页的公司简介

		$this->assign('index_description',$index_description);

		$this->assign('description',$index_description['description']);

		$this->assign('seo',$seo);

		$this->display('index.html');

	}

	



    /**

	 * @name链接重定向

	 *

	 */

    public function redirect() {

        $in = &$this->in;

        if(!$in['u']) $this->h404();

        $url = urldecode($in['u']);

        redirect($url);

        exit;

    }



	 /**

	 * @name链接重定向

	 *

	 */

    public function form() {

		$seo['seotitle'] = C('SEOTITLE');

		$this->assign('seo',$seo);

		$this->display('form.html');

    }



}

?>