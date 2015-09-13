<?php



// +----------------------------------------------------------------------

// | FangFa Net [ http://www.fangfa.net ]

// +----------------------------------------------------------------------

// | File: FmobileadAction.class.php

// +----------------------------------------------------------------------

// | Date: 2013-06-14

// +----------------------------------------------------------------------

// | Author: fangfa <1364119331@qq.com>

// +----------------------------------------------------------------------

// | 文件描述:  手机站广告管理

// +----------------------------------------------------------------------





defined ( 'IN_ADMIN' ) or die ( 'Access Denied' );

/**

 * @name 手机站广告管理

 *

 */

class FmobileadAction extends FbaseAction {

    

    protected $classes = array(

    

        '1' =>  '图片',

        '2' =>  '文字',

    

    );

    

    /**

	 * @name初始化手机站广告模块

	 */

	protected function _initialize(){

	   

        parent::_initialize();

        $in = &$this->in;

	    $in['_tablename'] = 'mobilead';

        

     //   if(C("IS_MOBILE") == false) $this->message(L('没有启用手机站功能！'));



	}

    

    /**

	 * @name手机站广告列表

	 */

    public function manage(){

        

        $in = &$this->in;

        

        if (! $in ['_tablename']) $this->message ( '没有指定操作表！' );

        

		$name = $in ['_tablename']; //数据表名

		$_m = D ( parse_name($name,1) ); //实例化表模型类

        $where = array();

        $order = ' aid DESC ';

        $data = $_m->where($where)->order($order)->select();

        $this->assign('data',$data);

        

        $this->display();

        

        

    }

    

    /**

	 * @name添加手机站广告

	 */    

    

    public function add(){
        $in = &$this->in;
        if (! $in ['_tablename']) $this->message ( '没有指定操作表！' );


        if($this->ispost()){
            $name = $in ['_tablename']; 
            $_m = D ( parse_name($name,1) ); 
            $in['info']['create_time']  = time();
            $in['info']['out_time']     = strtotime($in['info']['out_time']);
			if(empty($in['info']['out_time'] )) $in['info']['out_time']=null;
			
            if($in['info']['classes'] == 1){
                $in['info']['notes'] = '<img src="__PUBLIC__/uploads/'.$in['info']['notes'].'"/>';
            }
            if($_m->add($in['info'])){
                $this->message ( '广告添加成功！' , $in['forward'] );
            }else{
			 $this->message ( '广告添加失败！' , $in['forward'] );
            }

            

        }

        

        $this->forward = $this->in ['forward'] ? $this->in ['forward'] : (isset ( $_SERVER ['HTTP_REFERER'] ) ? $_SERVER ['HTTP_REFERER'] : '');

		$this->assign ( 'forward', $this->forward );



        $this->assign('classes',$this->classes);

        

        $this->display();

        

        

        

    }

    

    /**

	 * @name更新手机站广告

	 */

	public function edit(){

		$in = &$this->in;

        

        if (! $in ['_tablename']) $this->message ( '没有指定操作表！' );

        

        $name = $in ['_tablename']; 

        $_m = D ( parse_name($name,1) ); 

        

        $where = array();

        $where['aid'] = $in['aid'];

        

        if($this->ispost()){

            $in['info']['out_time']     = strtotime($in['info']['out_time']);
			if(empty($in['info']['out_time'] )) $in['info']['out_time']=null;
			
            if($in['info']['classes'] == 1){
                $in['info']['notes'] = '<img src="__PUBLIC__/uploads/'.$in['info']['notes'].'"/>';
            }
            

            if($_m->data($in['info'])->where($where)->save()){

                 $this->message ( '广告修改成功！' );

            }else{

                 $this->message ( '广告修改失败！' );

            }   

        }    

        $data = $_m->where($where)->find();
        $this->assign('data',$data);

        

        $this->display();

        

	}   

    

    public function delete(){

        $in = &$this->in;

        

        if (! $in ['_tablename']) $this->message ( '没有指定操作表！' );

        

        $name = $in ['_tablename']; 

        $_m = D ( parse_name($name,1) ); 

        

        $where = array();

        $where['aid'] = $in['aid'];

        

        if($_m->where($where)->delete()){

            $this->message ( '广告删除成功！' );

        }else{

            $this->message ( '广告删除失败！' );

        }

        

    }     

    

    /**

     * @name异步选择广告种类

     */ 

     

    public function ajaxSetting(){

        

        $in = &$this->in;

        if (! $in ['_tablename']) die('没有指定操作表！');

        if (! $in ['classes']) die('没有指定广告类型');

        die($this->classesHTML($in['classes']));

        exit;

        

    }

    

    /**

     * @name返回广告类型对应的html

     */ 

     

    public function classesHTML($classes,$setting){

        

        switch ($classes){

            

            case 1:

                return '<label>图片：<input id="info_ad_pic" class="input" type="text" style="width:260px" size="50" maxlength="255" value="'.$setting['notes'].'" name="info[notes]"></label><label><input id="upload_info_ad_pic" class="dialog" type="button" value="上传图片" title="从电脑上传图片" alt="?m=fupload&a=fieldupload&fieldid=28&opener_id=info_ad_pic&height=250&width=500"></label><br/><label>链接：<input type="text" class="input" value="'.$setting['url'].'" name="info[url]"  /></label>';

                break;

            case 2:

                return '<label><textarea id="text" class="textarea" rows="10" cols="60" name="info[notes]">'.$setting['notes'].'</textarea><label>
				<label>链接：<input type="text" class="input" value="'.$setting['url'].'" name="info[url]"  /></label>';
                break;

            

        }

    } 



}