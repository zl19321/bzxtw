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
class FblogindexAction extends FbaseAction {
	
	/**
	 * @name 初始化
	 */
	protected function _initialize() {
		parent::_initialize();
		$prefix = C('DB_PREFIX');
		//检查 REQUEST_FILE
		if (REQUEST_FILE != 'index' . C('URL_HTML_SUFFIX')) {
            if(!method_exists($this,ACTION_NAME)) {
               // $this->h404();

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
	 * @name系统首页
	 * 
	 */
	public function index() {
		// print_r($_SESSION);exit();
		$prefix = C('DB_PREFIX');
		$in = &$this->in;
		//站点信息：站点标题、关键字、描述
		$seo['seotitle'] = C('SEOTITLE');
		$seo['seokeywords'] = C('SEOKEYWORDS');
		$seo['seodescription'] = C('SEODESCRIPTION');		
		$seo['url'] = C ('SITEURL');

		$article_id = end(explode("/", $_SERVER['REQUEST_URI']));
		$arr = explode("/", REQUEST_FILE);

		$cate = M('category');
		$url = $cate->field('setting')->where("catdir='".$arr[0]."'")->select();
		eval("\$temp=".$url[0]['setting'].";");
		// print_r($temp['template']['index']);exit();
		// if($temp['template']['index'] != "blog_index.html"){
		// 	if(!$_SESSION['user']){
	 //    			header("location:".__ROOT__."/blog_index/");exit();
	 //    		}	
	 //    	}
	    	$model = new model();
		$sql = "SELECT id,class_img,class_name,b.title,b.artime,b.pname,b.ctime from fangfa_class_blog cb left join (SELECT article.title,article.create_time as artime,article.class_id,a.pname,a.ctime from fangfa_article article left join (SELECT pname,create_time ctime,class_id from fangfa_album_picture GROUP BY class_id ORDER BY create_time asc) as a ON a.class_id = article.class_id GROUP BY class_id ORDER BY artime desc) as b ON cb.id=b.class_id ORDER BY b.artime desc limit 0,6";
	    	$res = $model->query($sql);

	    	$sql3 = "SELECT id,class_img,class_name,b.title,b.artime,b.pname,b.ctime from fangfa_class_blog cb left join (SELECT article.title,article.create_time as artime,article.class_id,a.pname,a.ctime from fangfa_article article left join (SELECT pname,create_time ctime,class_id from fangfa_album_picture GROUP BY class_id ORDER BY create_time asc) as a ON a.class_id = article.class_id GROUP BY class_id ORDER BY artime desc) as b ON cb.id=b.class_id ORDER BY b.artime desc";
	    	$resall = $model->query($sql3);
	    	$this->assign('resall',$resall);
	    	
	    	$sql2 = "SELECT id,class_img,class_name,a.class_id,a.title,a.create_time,a.num from fangfa_class_blog blog LEFT JOIN (SELECT class_id,title,create_time,COUNT(class_id) as num from fangfa_article GROUP BY class_id ORDER BY num ASC) a on a.class_id=id ORDER BY num desc limit 0,5";
	    	$rec = $model->query($sql2);

	    	$count1 = M('article')->query("select SUM(count) as num from fangfa_article where class_id='".$_SESSION['class_id']."'");
			$count2 = M('album_picture')->query("select SUM(pcount) as num from fangfa_album_picture where class_id='".$_SESSION['class_id']."'");
			$count = $count1+$count2;
			$this->assign('count',$count[0]['num']);


		
	    	if($temp['template']['index'] == "class_index.html"){
	    		$this->class_index();
	    	}
	    	if($temp['template']['index'] == "blog_index.html"){
	    		$this->blogindex();
	    	}
	    	if($temp['template']['index'] == "class_cloum.html"){
	    		$this->blogindex();
	    	}
	    	if($temp['template']['index'] == "class_picture.html"){
	    		$this->class_picture();
	    	}
	    	if($temp['template']['index'] == "class_picture-view.html"){
	    		$this->viewpicture();
	    	}
	    	if($temp['template']['index'] == "class_member.html"){
	    		$this->class_member();
	    	}
	    	if($temp['template']['index'] == "class_member-view.html"){
	    		$this->memberview();
	    	}
	    	if($temp['template']['index'] == "class_article.html"){
	    		$this->class_article();
	    	}
	    	if($temp['template']['index'] == "class_article-view.html"){
	    		$this->articleview();
	    	}
	    	if($temp['template']['index'] == "teacher_index.html"){
	    		$this->teacher_index();
	    	}
	    	if($temp['template']['index'] == "teacher_index_colum.html"){
	    		$this->tea_colum();
	    	}
	    	if($temp['template']['index'] == "class_join.html"){
	    		if($_SESSION['user']){
	    			echo "<script>alert('你已加入班级，不可重复加入或加入其他班级！')</script>";
	    			echo "<script>location.href='".__ROOT__."/class_index/id/".$_SESSION['class_id']."'</script>";
	    		}
	    	}
	    	if($arr[0]){
				$this->func();
			}
	    	$mem = M('info_member')->where("username = '".$_SESSION['user']."'")->select();
			$this->assign('mem',$mem);
	    	$this->assign('rec',$rec);
	    	$this->assign('res',$res);
	    	$this->assign('class',$_SESSION['class']);
		$this->assign('seo',$seo);
		if($arr[0]){
			$this->display($temp['template']['index']);exit();
		}
		$this->display('blog_index.html');

	}
	/**
	 * 公用控制器（banner，head等）
	 */
	public function func(){
		$prefix = C('DB_PREFIX');
		$meminfo = M('info_member')->where("username='".$_SESSION['user']."'")->select();
		$classinfo = M('class_blog')->where("id = '".$_SESSION['class_id']."'")->find();
		$this->assign('classinfo',$classinfo);
		$model = new model();
		$artnum = M('article')->field('count(*) as num')->where("class_id = '".$_SESSION['class_id']."'")->select();
		$picnum = M('album_picture')->field('count(*) as num')->where("class_id = '".$_SESSION['class_id']."'")->select();
		// $artcom = M('article_comment')->field('count(*) as num')->where("type = '0' and class_id='".$_SESSION['class_id']."'")->select();
		// $piccom = M('album_comment')->field('count(*) as num')->where("type = '0' and class_id='".$_SESSION['class_id']."'")->select();
		$comnum1 = M("album_picture")->field("sum(pcomment) as num")->where("class_id = '".$_SESSION['class_id']."'")->select();
		$comnum2 = M("article")->field("sum(acomment) as num")->where("class_id = '".$_SESSION['class_id']."'")->select();
		$comnum = $comnum1[0]['num']+$comnum2[0]['num'];

	    	$type = M("article")->query("SELECT type_name,art.num from fangfa_article_type type LEFT JOIN (select author,classify,COUNT(classify) as num from fangfa_article where class_id='".$_SESSION['class_id']."' GROUP BY classify) art ON art.classify = type.type_name where class_id='".$_SESSION['class_id']."'");
		$sum = M("article")->field("count(*) as num")->where("class_id='".$_SESSION['class_id']."'")->select();
		$a = array('0'=>array('type_name'=>'全部文章'));
		$c = array_merge($a[0],$sum[0]);
		array_unshift($type,$c);

		

		$arpicom = $model->query("SELECT mem.class_name,mem.nickname,mem.role,ac.* from (select * FROM fangfa_album_comment UNION all select * from fangfa_article_comment) ac LEFT JOIN fangfa_info_member mem ON ac.user=mem.username where class_name=(select class_name from fangfa_class_blog where id='".$_SESSION['class_id']."') ORDER BY create_time desc limit 0,8");

		

		for ($i=0; $i <count($arpicom) ; $i++) { 
			$arpicom[$i]['create_time'] = date("d",time())-date("d",$arpicom[$i]['create_time']);
			
			// $nickname = $model->query("SELECT * from fangfa_info_member where username = '".$arpicom[$i]['user']."'");
			//  $arpicom[$i]['nickname'] = $nickname[0]['nickname'];
		}
		$this->assign('arpccom',$arpicom);
		$this->assign("meminfo",$meminfo);
		$this->assign('type',$type);
		$this->assign('artnum',$artnum);
		$this->assign('picnum',$picnum);
		$this->assign('comnum',$comnum);
	}
	/**
	 * 教师博客
	 */
	public function teacher_index(){
		$tealist=M('article')->where("role='1'")->order("update_time desc")->select();
		$this->assign('tealist',$tealist);
	}
	/**
	 * 教师博客详情
	 */
	public function tea_colum(){
		$recolist=M('article')->where("role='1'")->order("count asc,acomment asc,update_time desc")->select();
		$tealist=M('article')->where("role='1'")->order("update_time desc")->select();
		$this->assign('tealist',$tealist);
		$this->assign('recolist',$recolist);
	}
	/**
	 * 博客前台首页
	 */
	public function blogindex(){
		if($_SESSION['user']){
			$img=M('info_member')->where("username = '".$_SESSION['user']."'")->field("headimg")->find();
			$this->assign("headimg",$img);
			$this->assign('user',$_SESSION['user']);
			$this->assign('role',$_SESSION['role']);
		}
	}
	/**
	 * 文章列表
	 */
	public function class_article(){
		// print_r($_SESSION);exit();
		$artlist = M('article')->where("class_id = '".$_SESSION['class_id']."'")->ORDER("update_time desc")->select();
		// print_r(M('article')->getLastSql());exit();
		$this->assign('art',$artlist);
	}
	/**
	 * 文章操作
	 */
	public function doart(){
		$in = &$this->in;
		if($in['act'] == "com"){
			if(md5($in['yzm']) != $_SESSION['verify']){
				echo "no";exit();
			}else{
				$data['user'] = $in['user'];
				$data['art_id'] = $in['picid'];
				$data['type'] = $in['type'];
				$data['content'] = $in['cont'];
				$data['create_time']  = time();
				M('article')->query("UPDATE fangfa_article set acomment = acomment+1 where id='".$in['picid']."'");
				M('article_comment')->add($data);exit();
			}
		}
		$article = M('article')->where("id = '".$in['id']."'")->select();
		switch ($article[0]['arole']) {
			case '0':$article[0]['arole']="学生";
				# code...
				break;
			case '1':$article[0]['arole']="老师";
				# code...
				break;
		}
		$article[0]['create_time'] = date('Y/m/d H:i:s',$article[0]['create_time']);
		echo json_encode($article);exit();
	}
	/**
	 * 文章回复列表
	 */
	public function doartcom(){
		$in=&$this->in;
		$model = new model();
		if($in['act'] == "adds"){
			$id = M("article_comment")->field('art_id')->where("id = '".$in['id']."'")->select();
			$data['content'] = $in['cont'];
			$data['pid'] = $in['id'];
			$data['art_id'] = $id[0]['art_id'];
			$data['type'] = '1';
			$data['create_time'] = time();
			$data['user'] = $_SESSION['user'];

			M('article_comment')->add($data);exit();
		}
		$comment = $model->query("SELECT c.*,m.username,m.nickname,m.role,m.headimg from fangfa_article_comment c LEFT JOIN fangfa_info_member m ON c.user=m.username where pid='".$in['pid']."' and type='1'");
		for($i=0;$i<count($comment);$i++){
			$comment[$i]['create_time']=date("Y/m/d H:i:s",$comment[$i]['create_time']);
			switch ($comment[$i]['role']) {
				case '0':$comment[$i]['role']="学生";
					break;
				case '1':$comment[$i]['role']="老师";
					break;
			}
		}
		echo json_encode($comment);exit();
	}
	/**
	 * 文章详情
	 */
	public function articleview(){
		$ids = end(explode("/", $_SERVER['REQUEST_URI']));
		M('article')->query("UPDATE fangfa_article set count=count+1 where id='".$ids."'");
		$art = M('article')->where("id = '".$ids."'")->select();
	
		$model = new model();
		$comer = $model->query("SELECT c.*,m.username,m.nickname,m.role,m.headimg from fangfa_article_comment c LEFT JOIN fangfa_info_member m ON c.user=m.username where art_id='".$art[0]['id']."' and type='0'");

		$this->assign('coms',$comer);
		$this->assign('user',$_SESSION['user']);
		$this->assign('art',$art);
	}
	/**
	 * 成员详情
	 */
	public function memberview(){
		$ids = end(explode("/", $_SERVER['REQUEST_URI']));
		$model = new model();
		$mems = $model->query("SELECT * from fangfa_info_member where id='".$ids."'");
		$artnum = M("article")->field("count(*) as num")->where("author = '".$mems[0]['username']."'")->select();
		$picnum = M("album_picture")->field("count(*) as num")->where("pauthor = '".$mems[0]['username']."'")->select();
		$comnum1 = M("album_comment")->field("pcomment")->where("pauthor = '".$mems[0]['username']."'")->select();
		$comnum2 = M("article_comment")->field("comnum")->where("author = '".$mems[0]['username']."'")->select();
		$comnum = $comnum1[0]['pcomment']+$comnum2[0]['comnum'];
		$arr = explode("-", $mems[0]['birth']);
		$mems[0]['birth'] = implode("/", $arr);
		$this->assign('comnum',$comnum);
		$this->assign('picnum',$picnum[0]['num']);
		$this->assign('mems',$mems);
		$this->assign('sum',$artnum[0]['num']);
	}
	/**
	 * 班级成员
	 */
	public function class_member(){
		$in = &$this->in;
		$role = 0;
		if($in['role'] == 0){
			$role = 0;
		}else if($in['role'] == 1){
			$role = 1;
		}
		$model = new model();
		$mem = $model->query("SELECT * FROM `fangfa_info_member` WHERE class_name = (select class_name from fangfa_class_blog where id='".$_SESSION['class_id']."') and role = '".$role."'");
		if($in['role'] != null){
			echo json_encode($mem);exit();
		}
		$this->assign('member',$mem);
	}
	/**
	 * 图片操作
	 */
	public function dopic(){
		$in = &$this->in;
		if($in['act'] == "com"){
			if(md5($in['yzm']) != $_SESSION['verify']){
				echo "no";exit();
			}else{
				$data['user'] = $in['user'];
				$data['picture_id'] = $in['picid'];
				$data['type'] = $in['type'];
				$data['content'] = $in['cont'];
				$data['create_time']  = time();
				M('album_picture')->query("UPDATE fangfa_album_picture set pcomment = pcomment+1 where id='".$in['picid']."'");
				M('album_comment')->add($data);exit();
			}
		}
		$picture = M('album_picture')->where("id = '".$in['id']."'")->select();
		switch ($picture[0]['prole']) {
			case '0':$picture[0]['prole']="学生";
				# code...
				break;
			case '1':$picture[0]['prole']="老师";
				# code...
				break;
		}
		$picture[0]['create_time'] = date('Y/m/d H:i:s',$picture[0]['create_time']);
		echo json_encode($picture);exit();
	}
	/**
	 * 照片回复列表
	 */
	public function docom(){
		$in=&$this->in;
		$model = new model();
		if($in['act'] == "adds"){
			$id = M("album_comment")->field('picture_id')->where("id = '".$in['id']."'")->select();
			$data['content'] = $in['cont'];
			$data['pid'] = $in['id'];
			$data['picture_id'] = $id[0]['picture_id'];
			$data['type'] = '1';
			$data['create_time'] = time();
			$data['user'] = $_SESSION['user'];

			M('album_comment')->add($data);exit();
		}
		//in['pid'] 改为了$in['id'];
		$comment = $model->query("SELECT c.*,m.username,m.nickname,m.role,m.headimg from fangfa_album_comment c LEFT JOIN fangfa_info_member m ON c.user=m.username where pid='".$in['pid']."' and type='1'");
		for($i=0;$i<count($comment);$i++){
			$comment[$i]['create_time']=date("Y/m/d H:i:s",$comment[$i]['create_time']);
			switch ($comment[$i]['role']) {
				case '0':$comment[$i]['role']="学生";
					break;
				case '1':$comment[$i]['role']="老师";
					break;
			}
		}
		echo json_encode($comment);exit();
	}
	/**
	 * 班级图片
	 */
	public function viewpicture(){
		$ids = end(explode("/", $_SERVER['REQUEST_URI']));
		$album = M('album')->field("name")->where("id='".$ids."'")->select();
		$picture = M('album_picture')->where("aid = '".$ids."'")->select();
		$model = new model();
		$comer = $model->query("SELECT c.*,m.username,m.nickname,m.role,m.headimg from fangfa_album_comment c LEFT JOIN fangfa_info_member m ON c.user=m.username where picture_id='".$picture[0]['id']."' and type='0'");

		$this->assign('coms',$comer);
		$this->assign('album',$album);
		$this->assign('picture',$picture);
		$this->assign('user',$_SESSION['user']);
	}
	/**
	 * 班级相册
	 */
	public function class_picture(){
		$pic = M('album')->where("class_id='".$_SESSION['class_id']."'")->ORDER("update_time desc")->select();
		$this->assign('pic',$pic);
	}
	/**
	 * 班博首页
	 */
	public function class_index(){
		$id = end(explode("/", $_SERVER['REQUEST_URI']));
		if($id){
			$_SESSION['class_id'] = $id;
		}

		$classinfo = M('class_blog')->where("id = '".$_SESSION['class_id']."'")->find();
		$this->assign('classinfo',$classinfo);

		$article = M('article')->where("class_id = '".$_SESSION['class_id']."'")->ORDER("top desc,update_time desc")->select();
		for($i=0;$i<count($article);$i++){
			switch ($article[$i]['arole']) {
				case '0':$article[$i]['arole']="学生";
					break;
				case '1':$article[$i]['arole']="老师";
					break;
			}
		}
		

		$album = M('album')->where("class_id='".$_SESSION['class_id']."'")->ORDER("update_time desc")->limit("0,4")->select();
		
		$this->assign('album',$album);
		$this->assign('art',$article);
		
	}
	
	/**
	 * @name验证码
	 */
	public function verify() {
		import ( "ORG.Util.Image" );
		Image::buildImageVerify (4,1,png,48,22,'verify');
	}
	

	/**
	 * @name用户注册
	 * 
	 */
	public function register() {		
		$in = &$this->in;
		if (!C('USER_OPEN_REGISTER')) { // 检查是否开启前台用户注册
			$this->message('系统暂未开放用户注册功能！');
		}
		if ($in['ajax']) $this->_ajax_register();
		if ($this->ispost()) {
			if(md5($in['yzm'])==$_SESSION['verify']){
				$_SESSION['verify']="";
				$blog = M('class_blog');
				$member = M('info_member');
				$data['class_name'] = $in['class_name'];
				$data['admin_uname'] = $in['admin_uname'];
				$data['admin_pass'] = md5($in['admin_pass']);
				$data['admin_name'] = $in['admin_name'];
				$data['add_time'] = time();
				$data['type'] = "class";

				$info['username'] = $in['admin_uname'];
				$info['password'] = md5($in['admin_pass']);
				$info['nickname'] = $in['admin_name'];
				$info['class_name'] = $in['class_name'];
				$info['add_time'] = time();
				$info['role'] = '2';

				$res = $blog->data($data)->add();
				$rs = $member->data($info)->add();
				if($res||$rs){
					header("location:".__ROOT__."/blog_index/");
				}else{
					$this->remes(L("提交失败"));
				}
			}else{
				$this->remes('验证码输入不正确');
			}

		}
		$url = array_search(array('Findex','register'), C('_routes_'));
		!empty($url) && $seo['url'] = C ('SITEURL') . '/' . $url;
		$seo['seotitle'] = L('用户注册') . C('SITE_TITLE_SEPARATOR') .C('SEOTITLE');
		$seo['seokeywords'] = C('SEOKEYWORDS');
		$seo['seodescription'] = C('SEODESCRIPTION');
		$this->assign('forward',$seo['url']);
		$this->assign('seo',$seo);		
		$this->display('register.html');
	}
	
	/**
	 * @name处理注册时候的ajax请求
	 * 输出json字符串
	 */
	protected function _ajax_register() {
		$in = &$this->in;
		import('User', INCLUDE_PATH);
		$_user = get_instance_of('User');
		switch ($in['do']) {
			case 'checkusername':
				$username = trim($in['info']['username']);
				if (!$_user->getByUsername($username)) {
					die('true');
				} else {
					die('false');
				}	
				break;
			case 'checkemail':
				$email = trim($in['info']['email']);
				if (!$_user->getByEmail($email)) {
					die('true');
				} else {
					die('false');
				}				
				break;
			default:
				break;
		}
	}
	
	/**
	 * @name登入系统
	 * 
	 */
	public function login() {
		$in = &$this->in;
		$prefix = C('DB_PREFIX');
		$blog = M('info_member');
		$pass = md5($in['password']);
		$sql = "select * from fangfa_info_member where username='".$in['username']."' and password='".$pass."'";
		$res = $blog -> query($sql);
		if($res){
			$class_id = M('class_blog')->field('id')->where("class_name = '".$res[0]['class_name']."'")->select();
			$_SESSION['user'] = $in['username'];
			$_SESSION['bid'] = $res[0]['id'];
			$_SESSION['role'] = $res[0]['role'];
			$_SESSION['class_id'] = $class_id[0]['id'];

			M('info_member')->query("UPDATE fangfa_info_member set lastlogin = ".time()." where id = '".$res[0]['id']."'");
			header("location:".__ROOT__."/backstage/");
		}else{
			$this->remes("登录失败，请稍后再试！");
		}
	}
	/**
	 * 退出
	 */
	public function logout(){
		Session::clear();
	}
	/**
	 * @name网站地图
	 *
	 */
	public function sitemap() {
	    $in = &$this->in;
	    $_category = D ('Category','admin');	    
	    $url = array_search(array('Findex','sitemap'), C('_routes_'));
	    !empty($url) && $seo['url'] = C ('SITEURL') . '/' . $url;
	    $seo['seotitle'] = L('网站地图') . C('SITE_TITLE_SEPARATOR') .C('SEOTITLE');
		$seo['seokeywords'] = C('SEOKEYWORDS');
		$seo['seodescription'] = C('SEODESCRIPTION');	
	    //TODO 生成sitemap的ul li标签
	    $data = '';
	    $this->assign('data',$data);
	    $this->assign('seo',$seo);
	    $this->display('sitemap.html');
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