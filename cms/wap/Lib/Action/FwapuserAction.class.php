<?php
// +----------------------------------------------------------------------
// | FangFa Net [ http://www.fangfa.net ]
// +----------------------------------------------------------------------
// | File: FuserAction.class.php
// +----------------------------------------------------------------------
// | Date: 上午08:40:55
// +----------------------------------------------------------------------
// | Author: fangfa <1364119331@qq.com>
// +----------------------------------------------------------------------
// | 文件描述: 前台会员模块
// +----------------------------------------------------------------------

/**
 * @name前台会员模块
 *
 */
class FwapuserAction extends FbaseAction {
	
	/**
	 * 
	 * @nameUer实例
	 * @var object
	 */
	protected $_mUser = '';
	
	/**
	 * @name初始化
	 * @see FbaseAction::_initialize()
	 */
	protected function _initialize() {
		parent::_initialize();
		$this->_mUser = D ('User','admin');	
	}
	
	/**
	 * @name会员登录页面
	 * 
	 */	
	public function logins() {
		$in = &$this->in;
		$in['password']=trim($in['password']);
		$seo['seotitle'] = L ('会员面板') . C('SITE_TITLE_SEPARATOR') . L('会员中心') . C('SITE_TITLE_SEPARATOR') . C('SEOTITLE');
		$seo['seokeywords'] = C('SEOKEYWORDS');
		$seo['seodescription'] = C('SEODESCRIPTION');
	    $this->assign('seo',$seo);
        
       // if (! M()->autoCheckToken ( $in )){
		//	$this->message ( L ('<font class="red">请不要非法提交或者重复提交页面！</font>') );
        //}
        
	    //if ($_SESSION['verify'] != md5($in['verify'])) {
		//	$this->message(L('验证码输入错误！'), $this->forward);
		//}	
		$total=0;
        $username = trim($in['username']);
        $password = md5($in['password']);
		$password =trim($password);
        $datas = $this->_mUser->select();
        if(!empty($datas))
			{
			for($k=0;$k<count($datas);$k++)
			{
			if(($datas[$k]['username']==$username)&&($datas[$k]['password']==$password))
				{
            if($datas[$k]['status'] != 1)
			{
                $this->message(L ('用户信息正在审核中...'),'__ROOT__/huiyuanwap');
            }
			$total=$total+1;
			$_SESSION['xf_username']=$username;
            $this->message(L ('登录成功！'),'__ROOT__/tjwap');
			break;
				}
			}
			if($total ==0)
			{
				$this->message(L ('用户名或密码不存在! '),'__ROOT__/huiyuanwap');
			}
        }
		//else{
          //  $this->message(L ('用户名或密码不存在! '),'__ROOT__/huiyuanwap');  
        //}
        
	} 
    
}