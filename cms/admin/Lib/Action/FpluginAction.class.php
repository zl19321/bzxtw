<?php
// +----------------------------------------------------------------------
// | FangFa Net [ http://www.fangfa.net ]
// +----------------------------------------------------------------------
// | File: FpluginAction.class.php
// +----------------------------------------------------------------------
// | Date: 2013-02-21
// +----------------------------------------------------------------------
// | Author: fangfa <1364119331@qq.com>
// +----------------------------------------------------------------------
// | 文件描述:  
// +----------------------------------------------------------------------

defined ( 'IN_ADMIN' ) or die ( 'Access Denied' );
/**
 * @name 
 *
 */
class FpluginAction extends FbaseAction {

    public function testdata(){

        $in = &$this->in;
        
        $_category = D('Category');
        $arr = array(1,2,3,4,5);
        $dbname_arr = array(1=>'fangfa_content_article',2=>'fangfa_content_product',3=>'fangfa_content_picture',4=>'fangfa_content_download',5=>'fangfa_content_video');
        $_getCatidUseModelid = $_category->getCatidUseModelid($arr);

        $this->assign('categoryArr',$_getCatidUseModelid);
        
        if($this->ispost()){
       	    //执行数据库表插入 有待优化
        	$sql_data = file_get_contents(APP_PATH.'Tpl/default/Fplugin/content.txt');
    		$sql_data = str_replace("\r\n", "\n", $sql_data);
        	$sql_lines = explode("+-+", $sql_data);
            
            //执行数据库表插入 有待优化
        	$sql_data_sidetable = file_get_contents(APP_PATH.'Tpl/default/Fplugin/sidetable.txt');
    		$sql_data_sidetable = str_replace("\r\n", "\n", $sql_data_sidetable);
        	$sql_lines_sidetable = explode("+-+", $sql_data_sidetable);
            
            $number = $in['size'] == 'diy'?$in['sizenumber']:$in['size'];
            $randomthumb = $in['randomthumb'];
            
            $attr = $in['attr'];
            $attrsize = $in['attrsize'];
            $Model = new Model(); // 实例化一个model对象 没有对应任何数据表
            foreach($in['catid'] as $c){
                
                $category = $_category->where('catid = '.$c)->find();
                $db_name = $dbname_arr[$category['modelid']];
                
                for($i = 0;$i<$number;$i++){
                    $sql = 'INSERT INTO `fangfa_content` (`catid`, `title`, `style`, `thumb`, `attr`, `description`, `status`, `sort`, `url`, `template`, `seokeywords`, `seodescription`, `seotitle`, `user_id`, `username`, `create_time`, `update_time`) VALUES ';
                    $v2 = str_replace('$CATID',$c,$sql_lines[$i]);

                    if(in_array('top',$attr) && $i < $attrsize){
                        $v2 = str_replace('$ATTR','top',$v2);
                    }elseif(in_array('hot',$attr) && $i < ($attrsize*2)){
                        $v2 = str_replace('$ATTR','hot',$v2);
                    }elseif(in_array('scroll',$attr) && $i < ($attrsize*3)){
                        $v2 = str_replace('$ATTR','scroll',$v2);
                        $v2 = str_replace('$THUMB','images/image_test.jpg',$v2);
                    }else{
                        $v2 = str_replace('$ATTR','',$v2);
                    }
                    
                    if($randomthumb){
                        $rand = rand(1,2);
                        if($rand == 2){
                            $v2 = str_replace('$THUMB','images/image_test.jpg',$v2);
                        }else{
                            $v2 = str_replace('$THUMB','',$v2);
                        }
                    }else{
                        $v2 = str_replace('$THUMB','',$v2);
                    }
                    
                    $sql .= $v2;
                    $Model->query($sql);
                    $id = mysql_insert_id();
                    $sort = rand(1,$number);
                    $sql2 = 'UPDATE fangfa_content SET url = "2013/'.$id.'.html",status = 9,sort = '.$sort.'  WHERE cid = '.$id;
                    $Model->query($sql2);
                    $sql_sidetable = 'INSERT INTO `'.$db_name.'` (`cid`, `fulltitle`, `content`) VALUES';
                    $sql_sidetable .= $sql_lines_sidetable[$i];
                    $sql_sidetable = str_replace('$CID',$id,$sql_sidetable);

                    $Model->query($sql_sidetable);
                    
                }
            }
        
        }

        $this->display();
        
    }
    
    public function photomanage(){
        
        $in = &$this->in;
        
        $db_pre = C('DB_PREFIX');
        
        $_model_field = D('ModelField');
        $_content = D('Content');
        //组图、缩略图
        $modelfield = $_model_field->findlist($in['modelid'],' formtype in ("thumb","images")',0,'field,setting');
        foreach($modelfield as $k=>$v){
            $set[$v['field']]['maxwidth'] = $v['setting']['cut_maxwidth'];
            $set[$v['field']]['maxheight'] = $v['setting']['cut_maxheight']; 
            if($k == 0)
                $field .= $v['field'];
            else
                $field .= ','.$v['field'];
        }

        $data = $_content->field($field)
                         ->join("LEFT JOIN {$db_pre}".$in['tablename']." as t2 ON t2.cid = {$db_pre}content.cid")
                         ->where("{$db_pre}content.cid = ".$in['cid'])
                         ->find();
                         
                         
                         
        foreach($data as $k=>$v){

            if(self::isInString($v,'array')){

                $v = eval("return $v;");
                foreach($v as $k2=>$v2){
                    $photos[$k2]['src'] = $v2[1].'?'.rand(0,9999);
                    $photos[$k2]['maxwidth'] = $set[$k]['maxwidth'];
                    $photos[$k2]['maxheight'] = $set[$k]['maxheight'];
                }
            }else{

                $imgs[$k]['src'] = $v.'?'.rand(0,9999);
                $imgs[$k]['maxwidth'] = $set[$k]['maxwidth'];
                $imgs[$k]['maxheight'] = $set[$k]['maxheight'];

            }
        }

        $imgs = array_filter($imgs);
                
        //文章内图
        $modelfield2 = $_model_field->findlist($in['modelid'],' formtype in ("editor")',0,'field');
        foreach($modelfield2 as $k=>$v){
            if($k == 0)
                $field2 .= $v['field'];
            else
                $field2 .= ','.$v['field'];
        }
        
        $data2 = $_content->field($field2)
                         ->join("LEFT JOIN {$db_pre}".$in['tablename']." as t2 ON t2.cid = {$db_pre}content.cid")
                         ->where("{$db_pre}content.cid = ".$in['cid'])
                         ->find();
                         
        foreach($data2 as $k=>$v){
            $regex = '/(?<=<img src=").*(?=")/Usi';
            $str = $v;


            $matches = array();

            preg_match_all($regex, $str, $matches);
            
            $content_arr = $matches[0];
            
            foreach($content_arr as $k=>$v){
                $str = explode('../../../uploads/',$v);
                $contents[] = $str[1];
            }

        }

        
        $this->assign('set',$set);
        $this->assign('imgs',$imgs);
        $this->assign('photos',$photos);
        $this->assign('contents',$contents);
        $this->assign('cid',$in['cid']);
        $this->assign('tablename',$in['tablename']);
        $this->assign('modelid',$in['modelid']);
        
        $this->display();
        

    }
    
    function isInString($haystack, $needle) { 
        //防止$needle 位于开始的位置 
        $haystack = '-_-!' . $haystack; 
        return (bool)strpos($haystack, $needle); 
    } 
    
    public function cutit(){
        
        $in = &$this->in;
        
		$upload_dir = FANGFACMS_ROOT . C('UPLOAD_DIR');
		$filepath = $upload_dir . $in ['src'];
        
        $img = getimagesize($filepath);
         
        $this->assign('img',$img);
        $this->assign('maxwidth',$in['maxwidth']);
        $this->assign('maxheight',$in['maxheight']);
        $this->assign('src',$in['src']);
        $this->assign('cid',$in['cid']);
        $this->assign('tablename',$in['tablename']);
        $this->assign('modelid',$in['modelid']);
        
        $this->display();
    }
    
    public function cutphoto(){
        
        $in = &$this->in;
       
		$upload_dir = FANGFACMS_ROOT . C ( 'UPLOAD_DIR' );
        $src = explode('?',$in['src']);
		$filepath = $upload_dir . $src[0];
        
        

        
		if ($this->ispost()) {
			$in['w'] = intval($in['w']);
			$in['h'] = intval($in['h']);
			$in['x1'] = intval($in['x1']);
			$in['y1'] = intval($in['y1']);

            if(is_file($filepath)) {

                import('ImageResize',INCLUDE_PATH);
                $_imageResize = new ImageResize();
                $_imageResize->load($filepath);
                if ($_imageResize->cut($in['w'],$in['h'],$in['x1'],$in['y1'])) {
                    //保存图片、覆盖原图
                    if ($_imageResize->save($filepath,false)) {
                        $this->message('图片裁剪成功！','admin.php?m=fplugin&a=photomanage&cid='.$in['cid'].'&tablename='.$in['tablename'].'&modelid='.$in['modelid']);
                    }
                }
            }
            die('n');

		}
        
    }
    
    public function clear_sql(){
        
        $in = &$this->in;
        
        
        
    }

}

?>