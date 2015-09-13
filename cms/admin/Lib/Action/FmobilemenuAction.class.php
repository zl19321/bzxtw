<?php



// +----------------------------------------------------------------------

// | FangFa Net [ http://www.fangfa.net ]

// +----------------------------------------------------------------------

// | File: FmobilemenuAction.class.php

// +----------------------------------------------------------------------

// | Date: 2013-06-08

// +----------------------------------------------------------------------

// | Author: fangfa <1364119331@qq.com>

// +----------------------------------------------------------------------

// | 文件描述:  手机站栏目管理

// +----------------------------------------------------------------------





defined ( 'IN_ADMIN' ) or die ( 'Access Denied' );

//defined ( 'IN_ADMIN' )检测IN_ADMIN这个常量是不是存在

/**

 * @name 手机站栏目管理

 *

 */

class FmobilemenuAction extends FbaseAction {

    

    /**

	 * @name初始化手机站栏目模块

	 */

	protected function _initialize(){

	   

        parent::_initialize();

        $in = &$this->in;

	    $in['_tablename'] = 'mobilemenu';

      //  if(C("IS_MOBILE") == false) $this->message(L('没有启用手机站功能！'));



	}

    

    /**

	 * @name手机站栏目列表

	 */

    public function menumanage(){

        

        $in = &$this->in;

        if (! $in ['_tablename']) $this->message ( '没有指定操作表！' );

        

		$name = $in ['_tablename']; //数据表名

		$_m = D ( parse_name($name,1) ); //实例化表模型类

		//parse_name()字符串风格转换(0:将Java风格转换为C的风格;1:将C风格转换为Java的风格)

        $where = array();

        $order = ' sort ASC,id ASC ';

        $data = $_m->where($where)->order($order)->select();

        $this->assign('data',$data);

        

        $this->display();

        

        

    }

    

    /**

	 * @name添加手机站栏目

	 */

    public function add(){

        

        $in = &$this->in;

        

        $name = $in ['_tablename']; //数据表名

		$_m = D ( parse_name($name,1) ); //实例化表模型类

        

        if($this->ispost()){

            $in['info']['create_time'] = time();

            $in['info']['setting']     = var_export($in['info']['setting'],true);

			//var_export返回一个合法的可以合法赋值的值,加true可以赋给一个变量,不加true则不能赋值给一个变量

            $in['info']['catdir']     = $in['info']['url'].'.wap';

            $wap = $_m->where('catdir = "'.$in['info']['catdir'].'" OR url = "'.$in['info']['url'].'"')->find();

            if($wap) $this->message('栏目目录已存在，栏目创建失败！');

            

            $return = $_m->add($in['info']);

            if($return){

                $this->message('栏目创建成功！');

            }else{

                $this->message('栏目创建失败！');

            }

        }

        

        $_table = D('Category','admin');

        $where = array();

        $where['controller'] = array(' IN ','"fcontent","fpage","fguestbook","fjob","fsalenet","fvote","fask"');


        $_getCatid = $_m->getUseCatid(1);//获已有栏目,第一个参数表示栏目的status值,第二个参数为不需要的mobilemenu的id值



        if($_getCatid){

            $where['catid'] = array(' NOT IN ',$_getCatid);//挑选出还没有被选取的符合条件的栏目

        }

        $category = $_table->where($where)->select();



        $this->assign('category',$category);

        

        $this->display();

        

    }

    

    /**

	 * @name更新手机站栏目

	 */

	public function edit(){

		$in = &$this->in;

        

        if (! $in ['_tablename']) $this->message ( '没有指定操作表！' );

        

		if ($in ['ajax']) {

			$this->_edit_ajax ();

            exit;

		}

        $name = $in ['_tablename']; //数据表名

		$_m = D ( parse_name($name,1) ); //实例化表模型类

        if($this->ispost()){

            $in['info']['setting']     = var_export($in['info']['setting'],true);

           //$in['info']['catdir']     = $catdir['catdir'];

            $return = $_m->where('id = '.$in['id'])->save($in['info']);

            if($return !==false){

                $this->message('栏目编辑成功！');

            }else{

                $this->message('栏目编辑失败！');

            }

            

        }

        

        $_table = D('Category','admin');//D中的admin表示Model所在的项目

        $where = array();

        $where['controller'] = array(' IN ','"fcontent","fpage"');

        

        

        $_getCatid = $_m->getUseCatid(1,$in['id']);//排除了当前栏目和栏目中已有栏目的catid

        if($_getCatid){

            $where['catid'] = array(' NOT IN ',$_getCatid);

        }

        

        $category = $_table->where($where)->select();

        $this->assign('category',$category);

        

        $data = $_m->where(' id = '.$in['id'])->find();

        

        $category_edit = $_m->getOnesCategory($data['catid']);

        $_array_key = array_search( $category_edit['controller'],$_m->_returnController );

        $setting = $data['setting'];

        $setting = eval("return {$setting};");

        $data['otherHTML'] = self::_tpl_($_array_key,$setting['template']);//加入html代码



        $this->assign('data',$data);

       

        

        $this->display();

        

	}

    

    /**

	 * @name异步排序

	 */

	public function _edit_ajax(){

		$in = &$this->in;

		$_model = M('Mobilemenu');

		switch ($in['ajax']) {

			case 'sort':  //排序

				$in['id'] && $in['id'] = (int)substr($in['id'],5);

				if ($in['id'] && !empty($in['sort'])) {

					$data = $_model->find($in['id']);

					if (is_array($data)) {

						$data['sort'] = $in['sort'];

						if (false !== $_model->save($data)) {

							//更新缓存

							die($data['sort']);

						}

					}

				}

				break;

			default:

				break;

		}

		exit ();

	}

    

    

    /**

	 * @name异步获取模版

	 */

    

    public function ajaxSetting(){

        

        $in = &$this->in;

        

        $data = array();



        if(!isset($in['catid'])){

            $data['error'] = '请选择栏目！';

            die(json_encode($data));

        }    

        

        $name = $in ['_tablename']; //数据表名

		$_m = D ( parse_name($name,1) ); //实例化表模型类

        $category = $_m->getOnesCategory($in['catid']);

        

        if(isset($category)){

            $_array_key = array_search($category['controller'],$_m->_returnController);
            
			$category['otherHTML'] = self::_tpl_($_array_key,$_m->_tplArray[$category['modelid']]);			
            
			die(json_encode($category));        

        }else{

            $data['error'] = '未获取到指定栏目！';

            $data['otherHTML'] = self::_tpl_(-1);

            die(json_encode($data));

        }



    }

    

    /** 

     * @name筛选模版

     */



    public function _tpl_($key,$val = false){

        

        $category['otherHTML'] = '<tbody>';


	   switch ($key){
           case 3:
		   case 5:
			case 6:
		        $category['otherHTML'] .= '<tr>';

                $category['otherHTML'] .= '<th width="150">列表页<span>该栏目首页所使用的模板</span></th>';

                $category['otherHTML'] .= '<td>';

                $category['otherHTML'] .= '<label><input name="info[setting][template][index]" value="'.$val['index'].'" id="tb_inpute10552c937977179b3412b678936f042" class="input" title="请选择频道页模板" type="text"></label>';

                $category['otherHTML'] .= '<label><input class="dialog" alt="?m=ffiles&a=tpl_wap&opener_id=tb_inpute10552c937977179b3412b678936f042&TB_iframe=true&height=400&width=500" title="模板库" id="upload_img_e10552c937977179b3412b678936f042" value="选择模板" type="button"></label>';

                $category['otherHTML'] .= '</td>';

                $category['otherHTML'] .= '</tr>';
				    $category['otherHTML'] .= '<tr>';

                $category['otherHTML'] .= '<th width="150">详细页模板<span>栏目下详细内容显示所使用的模板</span></th>';

                $category['otherHTML'] .= '<td>';

                $category['otherHTML'] .= '<label><input name="info[setting][template][show]" value="'.$val['show'].'" id="tb_input1ec1ed730b02c096de68d4cc2ef2b2a8" class="input valid" title="请选择内容页模板" type="text"></label>';

                $category['otherHTML'] .= '<label><input class="dialog" alt="?m=ffiles&a=tpl_wap&opener_id=tb_input1ec1ed730b02c096de68d4cc2ef2b2a8&TB_iframe=true&height=400&width=500" title="模板库" id="upload_img_1ec1ed730b02c096de68d4cc2ef2b2a8" value="选择模板" type="button"></label>';

                $category['otherHTML'] .= '</td>';

                $category['otherHTML'] .= '</tr>';
				    $category['otherHTML'] .= '<tr>';

                $category['otherHTML'] .= '<th width="150">结果页模板<span>栏目下详细内容显示所使用的模板</span></th>';

                $category['otherHTML'] .= '<td>';

                $category['otherHTML'] .= '<label><input name="info[setting][template][send]" value="'.$val['send'].'" id="tb_input1ec1ed730b02c096de68d4cc2ef2b2a8" class="input valid" title="请选择内容页模板" type="text"></label>';

                $category['otherHTML'] .= '<label><input class="dialog" alt="?m=ffiles&a=tpl_wap&opener_id=tb_input1ec1ed730b02c096de68d4cc2ef2b2a8&TB_iframe=true&height=400&width=500" title="模板库" id="upload_img_1ec1ed730b02c096de68d4cc2ef2b2a8" value="选择模板" type="button"></label>';

                $category['otherHTML'] .= '</td>';

                $category['otherHTML'] .= '</tr>';
           
                 break;
		    case 0:
		    case 2:	
			case 4:
                $category['otherHTML'] .= '<tr>';

                $category['otherHTML'] .= '<th width="150">频道页模板<span>该栏目首页所使用的模板</span></th>';

                $category['otherHTML'] .= '<td>';

                $category['otherHTML'] .= '<label><input name="info[setting][template][index]" value="'.$val['index'].'" id="tb_inpute10552c937977179b3412b678936f042" class="input" title="请选择频道页模板" type="text"></label>';

                $category['otherHTML'] .= '<label><input class="dialog" alt="?m=ffiles&a=tpl_wap&opener_id=tb_inpute10552c937977179b3412b678936f042&TB_iframe=true&height=400&width=500" title="模板库" id="upload_img_e10552c937977179b3412b678936f042" value="选择模板" type="button"></label>';

                $category['otherHTML'] .= '</td>';

                $category['otherHTML'] .= '</tr>';
				
				
            case 1:
           		
                $category['otherHTML'] .= '<tr>';

                $category['otherHTML'] .= '<th width="150">内容页模板<span>栏目下详细内容显示所使用的模板</span></th>';

                $category['otherHTML'] .= '<td>';

                $category['otherHTML'] .= '<label><input name="info[setting][template][show]" value="'.$val['show'].'" id="tb_input1ec1ed730b02c096de68d4cc2ef2b2a8" class="input valid" title="请选择内容页模板" type="text"></label>';

                $category['otherHTML'] .= '<label><input class="dialog" alt="?m=ffiles&a=tpl_wap&opener_id=tb_input1ec1ed730b02c096de68d4cc2ef2b2a8&TB_iframe=true&height=400&width=500" title="模板库" id="upload_img_1ec1ed730b02c096de68d4cc2ef2b2a8" value="选择模板" type="button"></label>';

                $category['otherHTML'] .= '</td>';

                $category['otherHTML'] .= '</tr>';

                break;
            
              
		   default:

                $category['otherHTML'] .= '<tr>';

                $category['otherHTML'] .= '<th width="150">频道页模板<span>该栏目所使用的模板</span></th>';

                $category['otherHTML'] .= '<td>请先选择您要绑定的栏目模型！'.$key.'</td>';

                $category['otherHTML'] .= '</tr>';
				

        }
		
		


        $category['otherHTML'] .= '</tbody>';



        return $category['otherHTML'];

        

    }

    

    public function delete(){

        

        $in = &$this->in;

        

        if (! $in ['_tablename']) $this->message ( '没有指定操作表！' );



        $name = $in ['_tablename']; //数据表名

		$_m = D ( parse_name($name,1) ); //实例化表模型类

        

        if($_m->where("id = ".$in['id'])->delete()){

            $this->message ( '栏目删除成功！' );

        }else{

            $this->message ( '栏目删除失败！' );

        }

        

    }

    

    

    

    

}