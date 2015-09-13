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
class FblogadminAction extends FbaseAction {
	
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
		$in = &$this->in;
		// print_r($_SESSION);exit();
		if(!$_SESSION['user']){
    			header("location:".__ROOT__."/blog_index/");exit();
    		}

		$article_id = end(explode("/", $_SERVER['REQUEST_URI']));
		$arr = explode("/", REQUEST_FILE);
		//站点信息：站点标题、关键字、描述
		$seo['seotitle'] = C('SEOTITLE');
		$seo['seokeywords'] = C('SEOKEYWORDS');
		$seo['seodescription'] = C('SEODESCRIPTION');		
		$seo['url'] = C ('SITEURL');

		$cate = M('category');
		$url = $cate->field('setting')->where("catdir='".$arr[0]."'")->select();
		empty($url)?'':eval("\$temp=".$url[0]['setting'].";");

		if($temp['template']['index'] == "admin_article.html"){
			$this->article();
		}
		if($temp['template']['index'] == "admin_article_type.html"){
			$this->arttype();
		}
		if($temp['template']['index'] == "admin_picture.html"){
			$this->adpicture();
		}
		if($temp['template']['index'] == "admin_picture_manage.html"){
			$this->picmanage();
		}
		if($temp['template']['index'] == "admin_picture_upload.html"){
			$this->picupload();
		}
		if($temp['template']['index'] == "admin_member_change.html"){
			$this->memchan();
		}
		$this->assign('role',$_SESSION['role']);
		$types = M('article_type')->where('class_id = '.$_SESSION['class_id'])->order("sort asc")->select();

		$member = M("info_member");
		$info = $member->where("id='".$_SESSION['bid']."'")->select();
		$birthdate = explode("-",$info[0]['birth']);
		$this->assign('bdate',$birthdate);
		$this->assign('info',$info);

		$sqll ="select class_name from fangfa_info_member where id='".$_SESSION['bid']."'";
		$classname = $member->query($sqll);
		$this->assign('class',$classname[0]['class_name']);
		$this->assign('seo',$seo);
		// $types['num'] = 0;
		foreach ($types as $k => $v) {
			$types[$k]['num'] = $k+1;
		}
		$this->assign('type',$types);

		$mem = M('info_member')->where("username = '".$_SESSION['user']."'")->select();
		$this->assign('mem',$mem);

		$meminfo = M('info_member')->where("username='".$_SESSION['user']."'")->select();
		$this->assign("meminfo",$meminfo);
		if($in['id']&&$in['act']=="editarticle"){
			$types = M('article_type')->order("sort asc")->select();
			//查询当前被修改的文章信息
			$articledetail = M("article")->where(array("id"=>$in['id']))->find();
			$this->assign("article",$articledetail);
			$this->assign('id',$in['id']);
			$this->assign('types',$types);
			$this->display('admin_article_change.html');exit();
		}
		if($arr[0]){
			$this->display($temp['template']['index']);exit();
		}

		$this->display('admin_member_add.html');

	}
	/**
	 * 成员信息修改
	 */
	public function memchan(){
		$editid = end(explode("/", $_SERVER['PATH_INFO']));
		$editinfo = M("info_member")->where(array("id"=>$editid))->find();
		$this->assign("editinfo",$editinfo);
		$this->assign("id",$editid);
	}
	/**
	 * 成员信息修改提交
	 */
	public function memberchange(){
		$in = &$this->in;
		$data['role'] = $in['role'];
		$data['username'] = $in['username'];
		$data['nickname'] = $in['nickname'];
		$data['password'] = md5($in['password']);
		$res = M('info_member')->where(array("id"=>$in['id']))->save($data);
		if($res){
			$this->remes("修改成功！");
		}else{
			$this->remes("修改失败或未做任何修改！");
		}
	}
	/**
	 * 成员列表
	 */
	// public function admin_member(){
	// 	$num = M('info_member')->query("SELECT role,COUNT(role) from fangfa_info_member where class_name = (select class_name from fangfa_class_blog where id = '".$_SESSION['class_id']."') GROUP BY role");
	// 	print_r($num);exit();
	// 	$this->assign('num',$num);
	// }
	/**
	 * 上传相片
	 */
	public function picupload(){
		$albname = M('album')->where("class_id = '".$_SESSION['class_id']."'")->select();
		$this->assign('albname',$albname);
	}
	/**
	 * 相片操作
	 */
	public function picact(){
		$in=&$this->in;
		if($in['act'] == "change"){
			$data['pname'] = $in['name'];
			M('album_picture')->where("id='".$in['id']."'")->save($data);exit();
		}else if($in['act'] == "cover"){
			$model = new model();
			$model->query("UPDATE fangfa_album set cover = (SELECT picture from fangfa_album_picture where id = '".$in['id']."') where id='".$in['albid']."'");exit();
		}else if($in['act'] == "del"){
			for($i=0;$i<count($in['id']);$i++){
				if(empty($in['id'][0])){
					array_shift($in['id']);
				}
			}
			$id = implode(",", $in['id']);
			M('album_picture')->where("id in (".$id.")")->delete();exit();
		}else if($in['act'] == "move"){
			for($i=0;$i<count($in['id']);$i++){
				if(empty($in['id'][0])){
					array_shift($in['id']);
				}
			}
			$id = implode(",", $in['id']);
			$model = new model();
			$model->query("UPDATE fangfa_album_picture set aid = (SELECT id from fangfa_album where name = '".$in['albname']."') where id in (".$id.")");exit();
		}
	}
	/**
	 * 相册操作
	 */
	public function albumact(){
		$in=&$this->in;
		if($in['name']){
			M('album')->where("id='".$in['id']."'")->save($in);exit();
		}else{
			M('album')->where("id='".$in['id']."'")->delete();
			M('album_picture')->where("aid='".$in['id']."'")->delete();exit();
		}
	}
	/**
	 * 相册管理
	 */
	public function picmanage(){
		$arr = explode("/", $_SERVER['PATH_INFO']);
		$aid = end($arr);
		$img = M('album_picture')->where("aid = '".$aid."'")->order("create_time desc")->select();
		$alb = M('album')->where("id='".$aid."'")->select();
		$albname = M('album')->where("class_id = '".$img[0]['class_id']."'")->order("create_time asc")->select();
		$this->assign('albname',$albname);
		$this->assign('img',$img);
		$this->assign('alb',$alb);
	}
	/**
	 * 后台图片
	 */
	public function adpicture(){
		$pic = M('album')->query("SELECT * from fangfa_album as album LEFT JOIN (select aid,COUNT(aid) as num from fangfa_album_picture GROUP BY aid) as picture on picture.aid=album.id where class_id = '".$_SESSION['class_id']."'");
		for($i=0;$i<count($pic);$i++){
			if(empty($pic[$i]['cover'])){
				$res = M('album_picture')->where("aid='".$pic[$i]['id']."'")->order("create_time asc")->limit(1)->field('picture')->select();
				$pic[$i]['cover'] = $res[0]['picture'];
				M('album')->query("UPDATE fangfa_album set cover='".$res[0]['picture']."' where id='".$pic[$i]['id']."'");
			}
		}
		$this->assign('pic',$pic);
	}
	/**
	 * 文章类型操作
	 */
	public function arttype(){
		// $arttype = M('article_type');
		// $types = $arttype->order('sort asc,act_time desc')->select();
		// $this->assign('type',$types);
	}
	/**
	 * 文章类型操作
	 */
	public function dotype(){
		$in = &$this->in;
		$arttype = M('article_type');
		$in['act_time'] = time();
		if($in['act'] == 'edit'){
			$arttype->where("id='".$in['id']."'")->save($in);exit();
		}else if($in['act'] == 'delete'){
			$arttype->where("id='".$in['id']."'")->delete();exit();
		}else if($in['act'] == 'sort'){
			$arttype->where("id='".$in['id']."'")->save($in);exit();
		}else if($in['act'] == 'add'){
			$rs = $arttype->field('type_name')->where("type_name = '".$in['type_name']."'")->select();
			if($rs){
				echo json_encode('ycz');exit();
			}else{
				$data['type_name'] = $in['type_name'];
				$rs = $arttype ->field('sort')->order("sort desc")->limit(1)->select();
				$data['class_id'] = $_SESSION['class_id'];
				$data['sort'] = $rs[0]['sort']+1;
				$data['act_time'] = time();
				$arttype->add($data);exit();
			}
		}
	}
	/**
	 * 修改资料
	 */
	public function edit(){
		$in = &$this->in;
		$member = M("info_member");
		//图片处理
		if($_SERVER['REQUEST_METHOD'] == 'POST'){
			$file = $_FILES['headimg'];
			if($file['name']){
				$arrType=array('image/jpg','image/gif','image/png','image/bmp','image/jpeg');
				$max_size='5000000';      // 最大文件限制（单位：byte）
				$upfile='./public/uploads/images/blog_headimg'; //图片目录路径
				// $file=$_FILES['upfile'];
				if($_SERVER['REQUEST_METHOD']=='POST'){ //判断提交方式是否为POST
				    	if(!is_uploaded_file($file['tmp_name'])){ //判断上传文件是否存在
					    	$this->message(L('文件不存在！'));
				    	}
			   
			  		if($file['size']>$max_size){  //判断文件大小是否大于500000字节
					    $this->message(L('上传文件太大！'));
			   		} 
			  		if(!in_array($file['type'],$arrType)){  //判断图片文件的格式
			     			$this->message(L('上传文件格式不对！'));
			   		}
					if(!file_exists($upfile)){  // 判断存放文件目录是否存在
						mkdir($upfile,0777,true);
					} 
			      		$imageSize=getimagesize($file['tmp_name']);
					$img=$imageSize[0].'*'.$imageSize[1];
					$fname=$file['name'];
					$ftype=explode('.',$fname);
					$picName=$upfile."/".time().$fname;
			   
			   		if(file_exists($picName)){
					    	$this->message(L('同文件名已存在！'));
			     		}
			   		if(!move_uploaded_file($file['tmp_name'],$picName)){  
					   	$this->message(L('移动文件出错！'));
			    		}
				}  
		   
		    	
		      		$imageSize=getimagesize($file['tmp_name']);
				$img=$imageSize[0].'*'.$imageSize[1];
				$fname=$file['name'];
				$ftype=explode('.',$fname);
				$picName=$upfile."/".time().$fname;
			}
		}

		$data['nickname'] = $in['nickname'];
		$data['selfintro'] = $in['selfintro'];
		$data['sex'] = $in['sex'];
		$data['birth'] = $in['year']."-".$in['month']."-".$in['day'];
		$data['headimg'] = $picName;
		$res = $member->where("id='".$_SESSION['bid']."'")->save($data);
		if($res){
			$this->remes("修改成功！");
		}else{
			$this->remes("修改失败！");
		}
	}
	/**
	 * 班级设置
	 */
	public function classset(){
		$in = &$this->in;
		$blog = M("class_blog");
		//图片处理
		if($_SERVER['REQUEST_METHOD'] == 'POST'){
			$file = $_FILES['class_img'];
			if($file['name']){
				$arrType=array('image/jpg','image/gif','image/png','image/bmp','image/jpeg');
				$max_size='5000000';      // 最大文件限制（单位：byte）
				$upfile='./public/uploads/images/class_headimg'; //图片目录路径
				// $file=$_FILES['upfile'];
				if($_SERVER['REQUEST_METHOD']=='POST'){ //判断提交方式是否为POST
				    	if(!is_uploaded_file($file['tmp_name'])){ //判断上传文件是否存在
					    	$this->message(L('文件不存在！'));
				    	}
			   
			  		if($file['size']>$max_size){  //判断文件大小是否大于500000字节
					    $this->message(L('上传文件太大！'));
			   		} 
			  		if(!in_array($file['type'],$arrType)){  //判断图片文件的格式
			     			$this->message(L('上传文件格式不对！'));
			   		}
					if(!file_exists($upfile)){  // 判断存放文件目录是否存在
						mkdir($upfile,0777,true);
					} 
			      		$imageSize=getimagesize($file['tmp_name']);
					$img=$imageSize[0].'*'.$imageSize[1];
					$fname=$file['name'];
					$ftype=explode('.',$fname);
					$picName=$upfile."/".time().$fname;
			   
			   		if(file_exists($picName)){
					    	$this->message(L('同文件名已存在！'));
			     		}
			   		if(!move_uploaded_file($file['tmp_name'],$picName)){  
					   	$this->message(L('移动文件出错！'));
			    		}
				}  
		   
		    	
		      		$imageSize=getimagesize($file['tmp_name']);
				$img=$imageSize[0].'*'.$imageSize[1];
				$fname=$file['name'];
				$ftype=explode('.',$fname);
				$picName=$upfile."/".time().$fname;
			}
		}

		$data['class_intro'] = $in['class_intro'];
		$data['class_img'] = $picName;
		$data['is_add'] = $in['is_add'];
		$data['is_access'] = $in['is_access'];
		$data['is_examine'] = $in['is_examine'];
		$res = $blog->where("class_name='".$in['class_name']."'")->save($data);
		if($res){
			$this->remes("修改成功！");
		}else{
			$this->remes("修改失败！");
		}
	}
	/**
	 * 修改密码
	 */
	public function password(){
		$in = &$this->in;
		$member = M('info_member');
		$pass = $member->field("password")->where("id = '".$bid."'")->select();
		if(md5($in['password']) != $pass[0]['password']){
			$this->remes("原密码输入有误");
		}else{
			$data['password'] = md5($in['newpass']);
			$res = $member->where("id = '".$bid."'")->save($data);
			if($res){
				$this->remes("修改成功!");
			}else{
				$this->remes("修改失败！");
			}
		}
	}
	/**
	 * 文章展示页
	 */
	public function article(){
		$article = M('article');
		//查询所属班级id(括号里为班级名称)
		$model = new model();
		$article = M('article');
		$sql = "SELECT id from fangfa_class_blog where class_name = (select class_name from fangfa_info_member where username = '".$_SESSION['user']."') ";
		$res = $model->query($sql);
		$sql2 = "SELECT type,count(*) as num from fangfa_article where class_id = '".$res[0]['id']."' GROUP BY type";
		$num = $model->query($sql2);
		$nums = "";
		for($i=1;$i<=4;$i++){
			$nums[$i]=0;
		}
		foreach ($num as $key => $value) {
			$nums[$value['type']] = $value['num'];
			
		}
		$art = $article->where("type = '1' and class_id = ".$_SESSION['class_id'])->select();
		for ($i=0; $i <count($art) ; $i++) { 
			$sql3 = "SELECT COUNT(*) as num from fangfa_article_comment where art_id = '".$art[$i]['id']."' and type = 0";
			$com_num = $model->query($sql3);
			$art[$i]['com_num'] = $com_num[0]['num'];
		}
		$this->assign('art',$art);
		$this->assign('num',$nums);
	}
	/**
	 * 发布或修改文章
	 */
	public function doarticle(){
		$in = &$this->in;
		$article = M('article');
		$member = M('info_member');
		$blog = M('class_blog');
		$res = $member->field('class_name,role')->where("username='".$_SESSION['user']."'")->select();
		$cid = $blog->field('id')->where("class_name = '".$res[0]['class_name']."'")->select();
		$data['title'] = $in['title'];
		$data['author'] = $_SESSION['user'];
		$data['arole'] = $res[0]['role'];
		$data['content'] = $in['content'];
		$data['classify'] = $in['classify'];
		$data['comment'] = isset($in['comment'])?1:0;
		$data['top'] = isset($in['top'])?1:0;
		$data['allwords'] = isset($in['allwords'])?1:0;
		$data['type'] = $in['type'];
		$data['class_id'] = $cid[0]['id'];
		if($in['act'] == "modify"){
			$data['update_time'] = time();
			$rs = $article->where("id='".$in['id']."'")->save($data);
			if($rs){
				if($data['type'] == 1){
					$this->remes("修改成功");
				}else if($data['type'] == 2){
					$this->remes("已存入草稿箱");
				}
			}else{
				$this->remes("修改失败或未做任何修改");
			}
		}else{
			$data['create_time'] = time();
			$data['update_time'] = time();
			$rs = $article->add($data);
			if($rs){
				if($data['type'] == 1){
					$this->remes("发表成功");
				}else if($data['type'] == 2){
					$this->remes("已存入草稿箱");
				}
			}else{
				$this->remes("保存失败");
			}
		}
	}
	/**
	 * 删除文章
	 */
	public function delart(){
		$in = &$this->in;
		$arr = explode(",", $in['id']);
		for($i=0;$i<count($arr);$i++){
			if($arr[0] == ""){
				array_shift($arr);
			}
		}
		$arrid = implode(",", $arr);
		$res = M('article')->query("SELECT type from fangfa_article where id='".$arr[0]."'");
		if($res[0]['type'] != 3){
			M('article')->query("UPDATE fangfa_article set type = '3' where id in (".$arrid.")");exit();
		}else if($res[0]['type'] == 3){
			M('article')->query("DELETE from fangfa_article where id in (".$arrid.")");exit();
		}
	}
	/**
	 * 图片上传
	 */
	public function imguploads(){
		$in=&$this->in;
		$id = M('album')->field('id')->where("name = '".$in['album_name']."'")->select();
		if($_SERVER['REQUEST_METHOD'] == 'POST'){
			$file = $_FILES['file'];
			if($file['name']){
				$arrType=array('image/jpg','image/gif','image/png','image/bmp','image/jpeg');
				$max_size='5000000';      // 最大文件限制（单位：byte）
				$upfile='./public/uploads/images/album_picture'; //图片目录路径
				// $file=$_FILES['upfile'];
				if($_SERVER['REQUEST_METHOD']=='POST'){ //判断提交方式是否为POST
				    	if(!is_uploaded_file($file['tmp_name'])){ //判断上传文件是否存在
					    	$this->message(L('文件不存在！'));
				    	}
			   
			  		if($file['size']>$max_size){  //判断文件大小是否大于500000字节
					    $this->message(L('上传文件太大！'));
			   		} 
			  		if(!in_array($file['type'],$arrType)){  //判断图片文件的格式
			     			$this->message(L('上传文件格式不对！'));
			   		}
					if(!file_exists($upfile)){  // 判断存放文件目录是否存在
						mkdir($upfile,0777,true);
					} 
			      		$imageSize=getimagesize($file['tmp_name']);
					$img=$imageSize[0].'*'.$imageSize[1];
					$fname=$file['name'];
					$ftype=explode('.',$fname);
					$picName=$upfile."/".time().$fname;
			   
			   		if(file_exists($picName)){
					    	$this->message(L('同文件名已存在！'));
			     		}
			   		if(!move_uploaded_file($file['tmp_name'],$picName)){  
					   	$this->message(L('移动文件出错！'));
			    		}
				}  
		   
		    	
		      	$imageSize=getimagesize($file['tmp_name']);
				$img=$imageSize[0].'*'.$imageSize[1];
				$fname=$file['name'];
				$ftype=explode('.',$fname);
				$picName=$upfile."/".time().$fname;
			}
		}
		$data['picture'] = $picName;
		$data['aid'] = $id[0]['id'];
		$data['pname'] = $file['name'];
		$data['create_time'] = time();
		$data['pauthor'] = $_SESSION['user'];
		$data['class_id'] = $_SESSION['class_id'];
		M('album_picture')->add($data);
		print_r(M('album_picture')->getLastSql());
		exit();
	}
	/**
	 * 新建相册
	 */
	public function albumadd(){
		$in=&$this->in;
		$album = M('album');
		$data['name']=$in['album_name'];
		$data['author'] = $_SESSION['user'];
		$data['class_id'] = $_SESSION['class_id'];
		$data['intro'] = $in['album_intro'];
		$data['create_time'] = time();
		$data['update_time'] = time();
		$res = $album->add($data);
		if($res){
			$this->remes("相册新建成功");exit();
		}else{
			$this->remes("相册新建失败");exit();
		}
	}
	/**
	 * 带条件文章列表
	 */
	public function typechange(){
		$in = &$this->in;
		$where = "";
		if($in['classify'] == 1){
			$where = 1;
		}else{
			$where =" classify = '".$in['classify']."' ";
		}
		$art = M('article')->where("type = '".$in['type']."' and class_id = '".$_SESSION['class_id']."' and ".$where)->select();
		echo json_encode($art);exit();
	}
	/**
	 * @name验证码
	 */
	public function verify() {
		import ( "ORG.Util.Image" );
		Image::buildImageVerify (4,1,png,48,22,'verifys');
	}
	/**
	 * 修改用户信息
	 */

	/**
	 * 用户统计信息
	 */
	public function member(){
		$in = &$this->in;
		$member = M("info_member");
		if($in['act'] == "setmanager"){
			M("info_member")->query("UPDATE fangfa_info_member set role='2' where id='".$in['id']."'");
		}
		if($in['act'] == "del"){
			for($i=0;$i<count($in['id']);$i++){
				if(empty($in['id'][0])){
					array_shift($in['id']);
				}
			}
			$id = implode(",", $in['id']);
			$member->where("id in (".$id.")")->delete();exit();
		}
		$info = $member->query("select * from fangfa_info_member where role='".$in['data']."' and class_name=(select class_name from fangfa_class_blog where id='".$_SESSION['class_id']."')");
		echo json_encode($info);exit();
	}
	/**
	 * @name用户注册
	 * 
	 */
	public function addmember() {		
		$in = &$this->in;
		$member = M('info_member');
		$blog = M('class_blog');
		$res = $blog->field('class_name')->where("id = '".$_SESSION['class_id']."'")->select();
		$a = 0;
		for($i=0;$i<count($in['role']);$i++){
			switch ($in['role'][$i]) {
				case '学生':$data['role']='0';
					break;
				case '老师':$data['role']='1';
					break;
			}
			$data['username'] = $in['username'][$i];
			$data['password'] = md5($in['password'][$i]);
			$data['nickname'] = $in['nickname'][$i];
			$data['class_name'] = $res[0]['class_name'];
			$data['add_time'] = time();
			$res = $member->data($data)->add();
			if($res){
				$a++;
			}
		}
		$b = count($in['role'])-$a;
		$this->remes("成功添加".$a."条数据,".$b."条数据添加失败");
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
		$blog = M('class_blog');
		$pass = md5($in['password']);
		$sql = "select * from fangfa_class_blog where admin_uname='".$in['username']."' and admin_pass='".$pass."'";
		$res = $blog -> query($sql);
		if($res){
			header("location:".__ROOT__."/backstage/");
		}else{
			$this->remes("登录失败，请稍后再试！");
		}
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