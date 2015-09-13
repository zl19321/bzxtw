<?php

// +----------------------------------------------------------------------

// | FangFa Net [ http://www.fangfa.net ]

// +----------------------------------------------------------------------

// | File: FmobileAction.class.php

// +----------------------------------------------------------------------

// | Date: 2013-06-14

// +----------------------------------------------------------------------

// | Author: fangfa <1364119331@qq.com>

// +----------------------------------------------------------------------

// | 文件描述:  手机站管理

// +----------------------------------------------------------------------



defined ( 'IN_ADMIN' ) or die ( 'Access Denied' );



/**

 * @name 手机站管理

 *

 */

class FmobileAction extends FbaseAction {

    

    public $_fontsize = array(

        '1' =>  '18',//纯文字

        '2' =>  '10',//图文

        '3' =>  '16',//图文简介

    );

    

    /**

	 * @name初始化手机站模块

	 */

	protected function _initialize(){

	   

        parent::_initialize();

        $in = &$this->in;

        

       // if(C("IS_MOBILE") == false) $this->message(L('没有启用手机站功能！'));

        

        $in['_tablename_menu'] = 'mobilemenu';

        

        



	}

    

	/**

	*手机栏目

	**/

    public function setting(){

        

        $in = &$this->in;

        if (! $in ['_tablename_menu']) $this->message ( '没有指定栏目操作表！' );

        

        $name = $in ['_tablename_menu']; //数据表名

		$_mm = D ( parse_name($name,1) ); //实例化表模型类

        $where = array();

        $where['status'] = 1;

        $join = ' LEFT JOIN fangfa_category ON fangfa_category.catid = fangfa_mobilemenu.catid ';

        

        $data_model = $_mm->join($join)->where($where)->select();

        foreach($data_model as $k=>$v){

            $data_model[$k] = $v;

            $data_model[$k]['color'] = import('wap_'.$v['id'],INCLUDE_PATH.'wap/') == 1?'red':'black';

			//thinkphp中的import方法用于导入系统的基类库

        }

        

        //广告位

        $_ad = M('Mobilead');

        $ad = $_ad->select();

        foreach($ad as $k=>$v){

            $ad[$k] =   $v;

            $setting =  $v['setting'];

            $ad[$k]['setting']  =   eval("return {$setting};");

        }
        

        $this->assign('data_model',$data_model);

        $this->assign('ad',$ad);

        

        $this->display();

        

    }

    

	/**

	*更新模版

	**/

    public function updateModel(){

        

        $in = &$this->in;

        if (! $in ['_tablename_menu']) $this->message ( '<font color="red">没有指定栏目操作表！</font>' );

        

        $name = $in ['_tablename_menu']; 

		$_m = D ( parse_name($name,1) );

        

        $where = array();

        $where['status'] = 1;

        $where['id'] = $in['id'];

        $join = ' LEFT JOIN fangfa_category ON fangfa_category.catid = fangfa_mobilemenu.catid ';

        

        $data = $_m->join($join)->where($where)->find();        

        if($return = $this->createPhpCache($data)){
            die($this->createHtml($return,$data));

            exit;

        }else{

            die('<font style="color:red;">缓存文件创建失败！</font>');

            exit;

        }

        

        

    }



	/**

	*循环求出缓存

	**/

	public function huancun(){

		$in = &$this->in;

        if (! $in ['_tablename_menu']) $this->message ( '没有指定栏目操作表！' );

        $name = $in ['_tablename_menu'];

		$_m = D ( parse_name($name,1) );

        $where = array();

        $where['status'] = 1;

        $join = ' LEFT JOIN fangfa_category ON fangfa_category.catid = fangfa_mobilemenu.catid ';

        

        $data = $_m->join($join)->where($where)->select();		
		for($i=0;$i<count($data);$i++)

		{



		$wap_class = import('wap_'.$data[$i]['id'],INCLUDE_PATH.'wap/');

		if(empty($wap_class))
		copy(INCLUDE_PATH.'wap/wap_'.$data[$i]['controller'].'.dc',INCLUDE_PATH.'wap/wap_'.$data[$i]['id'].'.class.php');

		F("wap_".$data[$i]['id'].".class",'',INCLUDE_PATH.'wap/');

		}
		$this->message('<font class="green">模版缓存文件生成成功！</font>',U('fmobilemenu/menumanage'));

	}



	/**

	创建缓存文件

	**/

    protected function createPhpCache($data){

        

        if(!isset($data)){

            return '栏目不存在！';

            exit;

        }else{

            $wap_class = import('wap_'.$data['id'],INCLUDE_PATH.'wap/');

            if(empty($wap_class))

			copy(INCLUDE_PATH.'wap/wap_'.$data['controller'].'.dc',INCLUDE_PATH.'wap/wap_'.$data['id'].'.class.php');

            //copy将前面的文件复制给后面的文件

            $dc = F("wap_".$data['id'].".class",'',INCLUDE_PATH.'wap/');//thinkphp内置方法F快速文件数据读取和保存

            return $dc;

        }

        

    }

    



	/**

	**创建编辑手机栏目的栏目编辑页面

	**/

    protected function createHtml($data,$record){
        ${'checked_status_'.$data['status']} = ' checked="checked" ';
        $data['name'] = empty($data['name'])?$record['name']:$data['name'];
        $html = '<p id="returnHtmlp"></p><form action="" id="form1" method="post">';
        switch ($record['controller']){
            case 'fcontent':

                $html .= '显示条数：<input type="text" class="input" name="info[size]" value="'.$data['size'].'" /><br/>';

                $html .= '显示样式：<input type="radio" '.$checked_status_1.' name="info[status]" value="1" />文字 <input type="radio" '.$checked_status_2.' name="info[status]" value="2" />图片+文字 <input type="radio"'.$checked_status_3.' name="info[status]" value="3" />图片+文字+描述<br/>';

                $html .= '显示排序：<input type="text" class="input" name="info[sort]" value="'.$data['sort'].'" /><br/>';

                $html .= '附加条件：<input type="text" class="input" name="info[where]" value="'.$data['where'].'" /><br/>';

                $html .= '模块描述：<input type="text" class="input" name="info[name]" value="'.$data['name'].'" />';

                $html .= '<input type="hidden" name="info[controller]" value="'.$data['controller'].'" />';

                break;

            case 'fpage':

                $html .= '文字内容：<textarea class="textarea" name="info[description]">'.$data['description'].'</textarea><br/>';   

                $html .= '模块描述：<input type="text" class="input" name="info[name]" value="'.$data['name'].'" />';

                $html .= '<input type="hidden" name="info[controller]" value="'.$data['controller'].'" />';

                break;
             case 'fguestbook':
			     $html .= '显示条数：<input type="text" class="input" name="info[size]" value="'.$data['size'].'" /><br/>';

                $html .= '显示样式：<input type="radio" '.$checked_status_1.' name="info[status]" value="1" />文字 <input type="radio" '.$checked_status_2.' name="info[status]" value="2" />图片+文字 <input type="radio"'.$checked_status_3.' name="info[status]" value="3" />图片+文字+描述<br/>';

                $html .= '显示排序：<input type="text" class="input" name="info[sort]" value="'.$data['sort'].'" /><br/>';

                $html .= '附加条件：<input type="text" class="input" name="info[where]" value="'.$data['where'].'" /><br/>';

                $html .= '模块描述：<input type="text" class="input" name="info[name]" value="'.$data['name'].'" />';

                $html .= '<input type="hidden" name="info[controller]" value="'.$data['controller'].'" />';

                break;
             case 'fjob':
                    $html .= '显示条数：<input type="text" class="input" name="info[size]" value="'.$data['size'].'" /><br/>';

                $html .= '显示样式：<input type="radio" '.$checked_status_1.' name="info[status]" value="1" />文字 <input type="radio" '.$checked_status_2.' name="info[status]" value="2" />图片+文字 <input type="radio"'.$checked_status_3.' name="info[status]" value="3" />图片+文字+描述<br/>';

                $html .= '显示排序：<input type="text" class="input" name="info[sort]" value="'.$data['sort'].'" /><br/>';

                $html .= '附加条件：<input type="text" class="input" name="info[where]" value="'.$data['where'].'" /><br/>';

                $html .= '模块描述：<input type="text" class="input" name="info[name]" value="'.$data['name'].'" />';

                $html .= '<input type="hidden" name="info[controller]" value="'.$data['controller'].'" />';

                break;
            case 'fsalenet':
                   $html .= '显示条数：<input type="text" class="input" name="info[size]" value="'.$data['size'].'" /><br/>';

                $html .= '显示样式：<input type="radio" '.$checked_status_1.' name="info[status]" value="1" />文字 <input type="radio" '.$checked_status_2.' name="info[status]" value="2" />图片+文字 <input type="radio"'.$checked_status_3.' name="info[status]" value="3" />图片+文字+描述<br/>';

                $html .= '显示排序：<input type="text" class="input" name="info[sort]" value="'.$data['sort'].'" /><br/>';

                $html .= '附加条件：<input type="text" class="input" name="info[where]" value="'.$data['where'].'" /><br/>';

                $html .= '模块描述：<input type="text" class="input" name="info[name]" value="'.$data['name'].'" />';

                $html .= '<input type="hidden" name="info[controller]" value="'.$data['controller'].'" />';

                break;

             case 'fvote':
                          $html .= '显示条数：<input type="text" class="input" name="info[size]" value="'.$data['size'].'" /><br/>';

                $html .= '显示样式：<input type="radio" '.$checked_status_1.' name="info[status]" value="1" />文字 <input type="radio" '.$checked_status_2.' name="info[status]" value="2" />图片+文字 <input type="radio"'.$checked_status_3.' name="info[status]" value="3" />图片+文字+描述<br/>';

                $html .= '显示排序：<input type="text" class="input" name="info[sort]" value="'.$data['sort'].'" /><br/>';

                $html .= '附加条件：<input type="text" class="input" name="info[where]" value="'.$data['where'].'" /><br/>';

                $html .= '模块描述：<input type="text" class="input" name="info[name]" value="'.$data['name'].'" />';

                $html .= '<input type="hidden" name="info[controller]" value="'.$data['controller'].'" />';

                break;
			case 'fask':
                         $html .= '显示条数：<input type="text" class="input" name="info[size]" value="'.$data['size'].'" /><br/>';

                $html .= '显示样式：<input type="radio" '.$checked_status_1.' name="info[status]" value="1" />文字 <input type="radio" '.$checked_status_2.' name="info[status]" value="2" />图片+文字 <input type="radio"'.$checked_status_3.' name="info[status]" value="3" />图片+文字+描述<br/>';

                $html .= '显示排序：<input type="text" class="input" name="info[sort]" value="'.$data['sort'].'" /><br/>';

                $html .= '附加条件：<input type="text" class="input" name="info[where]" value="'.$data['where'].'" /><br/>';

                $html .= '模块描述：<input type="text" class="input" name="info[name]" value="'.$data['name'].'" />';

                $html .= '<input type="hidden" name="info[controller]" value="'.$data['controller'].'" />';

                break;  			      
		   default:
                  die("该模块不存在！");			

        }

       
        $html .= '<input type="hidden" name="id" value="'.$record['id'].'" /><input type="button" onclick="save_cache();" value="保存"/></form>';

        return $html;

        

    }

    

    public function saveCache(){

        

        $in = &$this->in;



        F("wap_".$in['id'].".class",$in['info'],INCLUDE_PATH.'wap/');

        $datas['text'] = '<span style="color:red;">缓存成功！</span>';

        $datas['id']   = $in['id']; 

        die(json_encode($datas));//通过json编码进行编译~默认编码是utf-8

        exit;

        

    }  

	/**

	**创建首页的模版

	**/

    public function create(){

        

       

        

        $in = &$this->in;

        if (! $in ['_tablename_menu']) $this->message ( '没有指定操作表！' );

        if ($in['model'] == '') $this->message( '没有需要生成首页的模块id，WAP首页生成失败！' );

        

        $name = $in ['_tablename_menu']; 

		$_m = D ( parse_name($name,1) );

        $DB_PREFIX = C('DB_PREFIX');

        $join = ' LEFT JOIN '.$DB_PREFIX.'category AS c ON c.catid = '.$DB_PREFIX.'mobilemenu.catid ';

        

        $_ad = M('Mobilead');

        foreach($in['model'] as $v){

            

            $v = explode('_',$v);



            if($v[0] != 'ad'){



                $v = $v[0];

            }else{

                $html = $_ad->where('aid = '.$v[1])->find();

                $return[] = '<div class="ad">'.$html['notes'].'</div>';

                continue;

            }



            $wap_box1 = F('wap_'.$v.'.class','',INCLUDE_PATH.'wap/');

            $wap_box1['htmlsort'] = $wap_box1['sort'];

            $wap_box1['stylestatus'] = $wap_box1['status'];

            $where = $DB_PREFIX.'mobilemenu.id = '.$v;

            $wap_box2 = $_m->field($DB_PREFIX.'mobilemenu.*,c.name AS realname')->join($join)->where($where)->find();

            

            $wap_box = array_merge($wap_box1,$wap_box2);



            $return[] = $this->returnHTML($wap_box);



        }

        $html = '{{include file="system/header.html"}}';

        $html .= '<div class="bodyer">';

        foreach($return as $k=>$v){

            $html .= $v;

        }

        $html .= '</div>';

        $html .= '{{include file="system/footer.html"}}';

        



        $filename = array('header','footer');

        foreach($filename as $v){

            if (!file_exists('public/theme/wap/system/'.$v.'.html')) {

                copy(INCLUDE_PATH.'wap/wap_system_'.$v.'.dc','public/theme/wap/system/'.$v.'.html');

            }

        }

        



        file_put_contents('public/theme/wap/index.html',$html,LOCK_EX);

        $this->message ( 'wap首页创建成功！' );

           

    }

    

    public function returnHTML($wap_box){

        

        $html = '';

        

        if(!is_array($wap_box)) return false;



        switch($wap_box['controller']){

            case 'Fwapcontent':

                $html .= '<div class="clear"></div>';

                $html .= '<div class="news">';

                $html .= '<div class="nav1">'.$wap_box['realname'].'<span><a href="__ROOT__/'.$wap_box['url'].'">更多>></a></span></div>';

                $html .= '<div class="nav'.$wap_box['stylestatus'].'_main">';

                $html .= '{{fflist catid='.$wap_box['catid'].' url="'.$wap_box['url'].'" pagesize='.$wap_box['size'].' tlength='.$this->_fontsize[$wap_box['stylestatus']].' dlength=20 isthumb=1 sort="'.$wap_box['htmlsort'].'" to=wap}}';

                $html .= '<ul>';

                $html .= '{{foreach from=$wap item=v}}';

                $html .= '<li>';

                if($wap_box['stylestatus'] == 1){

                    $html .= '<span><p><a href="{{$v.url}}">{{$v.title}}<font style="float:right;">{{$v.create_time|date_format:"%Y-%m-%d"}}</font></a></p></span>';

                }elseif($wap_box['stylestatus'] == 2){

                    $html .= '<span><p><a href="{{$v.url}}">{{$v.title}}</a></p><p>{{$v.description}}</p></span>';

                }elseif($wap_box['stylestatus'] == 3){

                    $html .= '<img src="{{$v.thumb}}" />';

                    $html .= '<span><p><a href="{{$v.url}}">{{$v.title}}</a></p><p>{{$v.description}}</p></span>';

                }

                $html .= '</li>';

                $html .= '{{/foreach}}';

                $html .= '</ul>';

                $html .= '</div>';

                $html .= '</div>';

            break;

            case 'Fwappage':

                $html .= '<div class="clear"></div>';

                $html .= '<div class="about">';

                $html .= '<div class="nav1">'.$wap_box['realname'].'<span><a href="__ROOT__/'.$wap_box['url'].'">更多>></a></span></div>';

                $html .= '<div class="nav1_main">'.$wap_box['description'].'</div>';

                $html .= '</div>';

            break;

        }

        

        return $html;

        

    }

       

    

}

    



