<?php
// +----------------------------------------------------------------------
// | FangFa Net [ http://www.fangfa.net ]
// +----------------------------------------------------------------------
// | File: FjobAction.class.php
// +----------------------------------------------------------------------
// | Date: 2010-09-17
// +----------------------------------------------------------------------
// | Author: Chao <eddy0909@126.com>
// +----------------------------------------------------------------------
// | 文件描述: 人才招聘
// +----------------------------------------------------------------------

defined('IN') or die('Access Denied!');
/**
 * @name人才招聘
 *
 */
class FjobAction extends FbaseAction {
	/**
	 * @name栏目数据
	 * @var unknown_type
	 */
	protected $_category_data = array();
	/**
	 * @name请求的文件,不包括 .html
	 * @var string
	 */
	protected $_request_file = '';
	/**
	 * @name初始化
	 *
	 */
	protected function _initialize(){
		parent::_initialize();
		$in = &$this->in;
		if (CATID) {
			$catid = CATID;
		} elseif ($in['catid']) {
			$catid = intval($in['catid']);
		} else {
			$this->message(L('缺少参数catid！'));
		};
		
		$this->_category_data = F ('category_'.$catid);
		$this->_request_file = substr(REQUEST_FILE,0,strlen(REQUEST_FILE)-strlen(C('URL_HTML_SUFFIX')));
	
		$this->assign('cat', $this->_category_data);
	}
	
	/**
	 * @name分发到对应的动作
	 */
	protected function _empty() {
		$baseurl = str_replace('?', '/', $this->_urls['baseurl']);
		$method = explode('/', $baseurl);
		$method = $method[0];
		if (method_exists($this, $method)) {
			call_user_func(array($this, $method));
		} else {
			$this->h404();
		}
	}
	
	/**
	 * @name列表頁
	 */
	public function index()
	{
		$in = &$this->in;

		$category_data = $this->_category_data;
		$seo['seotitle'] = $category_data['seotitle'] ? $category_data['seotitle'] : $category_data['name'];
		$seo['seokeywords'] = $category_data['seokeywords'] ? $category_data['seokeywords'] : $category_data['name'];
		$seo['seodescription'] = $category_data['seodescription'] ? $category_data['seodescription'] : $category_data['description'];
		
		//字符替换
		$seo = parent::meta_replace($seo);
		$this->assign('seo', $seo); //meta信息
		$this->assign('p', $this->_page); //当前页码
		$this->display($this->_category_data['setting']['template']['index']);
	}
	
	/**
	 * @name职位详情
	 */
	public function show()
	{
		$in = &$this->in;
		$in['job_id'] = intval($in['job_id']);
		if (!$in['job_id']) $this->message('参数错误');
		
		$job_data = D('Job', 'admin')->find($in['job_id']);
		$this->assign('job', $job_data);

		$category_data = $this->_category_data;
		$seo['seotitle'] = $job_data['title'] . '-' .($category_data['seotitle'] ? $category_data['seotitle'] : $category_data['name']);
		$seo['seokeywords'] = $job_data['title'];
		$seo['seodescription'] = $job_data['notes'];
		//字符替换
		$seo = parent::meta_replace($seo);
		$this->assign('seo', $seo); //meta信息
		$this->display($this->_category_data['setting']['template']['show']);
	}
	
	/**
	 * @name投递简历
	 */
	public function send()
	{
		$in = &$this->in;
		$in['job_id'] = intval($in['job_id']);
		if (!$in['job_id']) $this->message('参数错误');
		
		//判断权限
		if ($this->_category_data['setting']['islogin'] && empty($_SESSION['fuserdata']['username'])) {
			$this->message('只有登录的用户才能在线投递简历，请您先登录！');
		}
		if ($this->ispost()) {
			$this->do_send();
		}
		
		$job_data = D('Job', 'admin')->find($in['job_id']); ;
		if ($job_data['filename']) {
			$job_filename = explode('|', $job_data['filename']);
			$job['filename'] = array();
			if (count($job_filename) == 2) {
				$job_data['filename']['title'] = $job_filename[0];
				$job_data['filename']['download'] = __PUBLIC__.'/uploads/'.$job_filename[1];
			}
		}
		$this->assign('job', $job_data);

		$category_data = $this->_category_data;
		$seo['seotitle'] = '在线应聘' . $job_data['title'] . '-' .($category_data['seotitle'] ? $category_data['seotitle'] : $category_data['name']);
		$seo['seokeywords'] = $job_data['title'];
		$seo['seodescription'] = $job_data['notes'];
		//字符替换
		$seo = parent::meta_replace($seo);
		$this->assign('seo', $seo); //meta信息
		$this->display($this->_category_data['setting']['template']['send']);
	}
	
	/**
	 * @name保存投递的简历
	 */
	protected function do_send()
	{
		$in = &$this->in;
		$msg = '';
		$in['info']['job_id'] = intval($in['info']['job_id']);
		if (!$in['info']['job_id']) $msg .= '缺少参数id！ ';
		if (empty($in['info']['user_name'])) $msg .= '请输入您的姓名！ ';
		if (empty($in['info']['user_sex'])) $msg .= '请选择您的性别！ ';
		if (empty($in['info']['user_age'])) $msg .= '请输入您的年龄！ ';
		if ($in['info']['user_card'] && preg_match('/^\d{15}(\d{2}[\dXx])?/$', $in['info']['user_card'])) $msg .= '请正确输入您的身份证号码！ ';
		if ($in['info']['user_email'] && preg_match('/^.+@\w+(\.\w+)+$/', $in['info']['user_email'])) $msg .= '请正确输入您的邮箱！';
		$_model = M('job_apply');
		
		if (!$_model->autoCheckToken($_POST)) $this->message('请不要重复提交表单！');
		
		//判断文件上传，处理文件上传
        //fangfa 2013-01-13 修改判断
		if (!empty($_FILES['filename']['name'])) {
			//处理简历上传
			import("ORG.Net.UploadFile");
			$upload = get_instance_of ( 'UploadFile' ); // 实例化上传类
			$upload->maxSize  = intval($this->_category_data['setting']['maxsize'])
									? 1024*$this->_category_data['setting']['maxsize'] 
									: 307200; // 设置附件上传大小
			$upload->allowExts  = (empty($this->_category_data['setting']['allow_ext']) 
											? array('rar','zip','doc','xls','wps','et')
											: explode('|',$this->_category_data['setting']['allow_ext'])); // 设置附件上传类型
			$upload->savePath =  C ( 'UPLOAD_DIR').'files/'; // 设置附件上传目录
			$upload->saveRule = C ( 'UPLOAD_FILE_RULE' );
			if(!$upload->upload()) { // 上传错误提示错误信息
				$msg .= $upload->getErrorMsg();
			}else{ // 上传成功获取上传文件信息
				$fileinfo =  $upload->getUploadFileInfo();
				$in['info']['upload_name'] = $fileinfo [0]['name'];
				$in['info']['filename'] = 'files/' . $fileinfo [0]['savename'];
			 }
		}
		
		$in['info']['create_time'] = $in['info']['update_time'] = time();
		$this->assign('in', $in);
		if ($msg == '') {
			array_map('addslashes', $in['info']);
			if ($_model->add($in['info'])) {
				$this->message(L('提交成功！'),$this->_category_data['url']);				
			} else {
				$this->message('提交失败！' . $_model->getError());
			}
		} else {
			$this->message(L($msg),U(''));
		}
		
	}
}