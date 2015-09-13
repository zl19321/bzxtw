<?php
/*
 * 微博
 * */
defined('IN_ADMIN') or die('Access Denied');
/**
 * @name RBAC访问控制设置
 *
 */
class FblogAction extends FbaseAction {
	/**
	 * @name初始化
	 */
	protected function _initialize() {
		parent::_initialize();
	}
	
	/**
	 * @name 站内内容
	 *
	 */
   public function contentlist() {
   	        $in = &$this->in;
	        $content = D('content');
         	//生成树
		   	import ( 'Tree', INCLUDE_PATH );
		   	$parent_id = ($in['parentid'] ? $in['parentid'] : 0);
         	$tree = get_instance_of ( 'Tree' );
			$categorys = D('Category')->field("`catid` AS `id`,`name`,`parentid`")->where("`cattype`='cat'")->findAll();
			$tree->init ( $categorys );
			$str = "<option value='\$id' \$selected>\$spacer\$name</option>\n";
			$categorys_option = $tree->get_tree ( 0, $str, $parent_id);
			$this->assign ( 'html',$categorys_option );	//已有分类
       	 $where = ' 1';
		 $pageurl = "";
		 $seaarr = array();
       	 if($in['stitle']) {
				 $where .= ' AND title like \'%'.$in['stitle'].'%\'';
                 $pageurl .= "&q=".$in['stitle'];
				 $seaarr['stitle'] = $in['stitle'];
				  }
		 if($in['spic']) {
           		 $where .= "  AND thumb != '' ";
				 $pageurl .= "&spic=".$in['spic'];
				  $seaarr['spic'] = $in['spic'];
				}
         if(is_numeric($in['snav'])) {
		          $where .= ' AND catid = '.$in['snav'].' ';
                   $pageurl .= "&snav=".$in['snav'];
				    $seaarr['snav'] = $in['snav'];
		       }
		$this->assign("search",$seaarr);
		
		//统计模型数量
		$data ['count'] = $content->where($where)->count();
		//初始化分页类
		$_GET ['p'] = &$in ['p']; //分页类中会用到$_GET
		$Page = new Page ( $data ['count'], 10 ,$pageurl);
		//分页代码
		$data ['pages'] = $Page->show ();
		//当前页数据

		 
		 $data['info'] = $content->where($where)->order('create_time DESC')->limit ( $Page->firstRow . ',' . $Page->listRows )->select();
		 $this->assign('data',$data);	
	     $this->display();
   }
   
   /*
    * 选择内容
    * 
    */
   public function manage() {
   	$in = $this->in;
	$db_pre = C('DB_PREFIX');
   	if($_COOKIE['blogcid']) {
   		$where = 'cid='.$_COOKIE['blogcid'];
   		$_content = D("content");
   		$modelid = $_content->field("cat.modelid,cat.catdir")->join("LEFT JOIN {$db_pre}category AS cat ON cat.catid = {$db_pre}content.catid")->where($where)->find();
		$array = array(
            1=>"{$db_pre}content_article",
            2=>"{$db_pre}content_product",
            3=>"{$db_pre}content_picture",
        );
		$data = $_content->join("LEFT JOIN ".$array[$modelid['modelid']]." ON ".$array[$modelid['modelid']].".cid = {$db_pre}content.cid")->where("{$db_pre}content.cid=".$_COOKIE['blogcid'])->find();			
		$data['url'] =C('SITEURL').substr($_SERVER['SCRIPT_NAME'],0,strrpos($_SERVER['SCRIPT_NAME'],"/")).'/'.$modelid['catdir'].'/'.$data['url'];		
		setcookie("blogcid",'',-86400);
		$num=0;
		$images = array();
		if ($data['images']) {
			$img = eval("return (".$data['images'].");");
			$i = 0;
			foreach ($img as $image) {
				$images[$i]['image'] = $image[1]; //原图
				$i++;
			}
			$num += 1;
 		}
		if($data['big_pic']) $num += 1;
		if($data['thumb']) $num += 1;
		$this->assign("images",$images);
		$this->assign("tag",$num);
   		$this->assign("data",$data);
   	}
			$user = D("bloguser");
		$tence = $user->field("name")->where("type='腾讯微博' AND state=1")->select();
		$sina = $user->field("name")->where("type='新浪微博' AND state=1")->select();
		$this->assign("tence",$tence);
		$this->assign("sina",$sina);
   $this->display();
   } 

   
   /*
    * 帐号设置管理
    * 
    */
    public function usermanage() {
    	$bloguser = D("bloguser");
		if($_GET['act'] == 'update') {      //更改状态
    		$where = 'id='.$_GET['id'];
            $data = $bloguser->where($where)->find();		
		       if($data['type'] == '腾讯微博') {
		            include './plugins/tecent/Config.php';
					include './plugins/tecent/Tencent.php';
					OAuth::init($client_id, $client_secret);
					 Tencent::$debug = $debug;
					if($_GET['state'] == 1) {        //刷新
					    $_SESSION['t_refresh_token'] = $data['refresh_token'];
					    $out = OAuth::refreshToken();
					   unset($_SESSION['t_refresh_token']);
						unset($_SESSION['t_access_token']);
						unset($_SESSION['t_expire_in']);
						$redata['datelimit'] = strtotime(date('Y-m-d h:i:s',time()+$out['expires_in']));
						$redata['state'] = "1";
						$redata['access_token'] = $out['access_token'];
						$redata['refresh_token'] = $out['refresh_token'];
						$redata['openid'] = $out['openid'];
					    }else {                      //清除
						 $_SESSION['t_access_token'] = $data['access_token'];
						 $_SESSION['t_code'] = $data['code'];
						 $_SESSION['t_openid'] = $data['openid'];
					     $_SESSION['t_openkey'] = $data['openkey'];
                         OAuth::clearOAuthInfo();
						  unset($_SESSION['t_refresh_token']);
						$redata['state'] = '0';	
					 }
			   } else  {          //新浪微博
		            if($_GET['state'] == 1) {        //刷新
                       $redata['state'] = '1';
                      }  else {
					  $redata['state'] = '0';
					 }					  
			   }			
			$bloguser->where($where)->save($redata);
    	} elseif($_GET['act'] == 'del') {
    		$where = 'id='.$_GET['id'];
    		$bloguser->where($where)->delete();
    	}
    	$userlist = $bloguser->select();
        $this->assign('userlist',$userlist);
    	$this->display();
    }   
}
?>