<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2009 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
// $Id$

class Page extends Think {
    // 起始行数
    public $firstRow	;
    // 列表每页显示行数
    public $listRows	;
    // 页数跳转时要带的参数
    public $parameter  ;
    // 分页总页面数
    public $totalPages  ;
    // 总行数
    protected $totalRows  ;
    // 当前页数
    protected $nowPage    ;
    // 分页的栏的总页数
    protected $coolPages   ;
    // 分页栏每页显示的页数
    public $rollPage = 10  ;
	// 分页显示定制
    protected $config  =	array(
    		'header'=>'条',
    		'prev'=>'上一页',
    		'next'=>'下一页',
    		'first'=>'首页',
    		'last'=>'末页',
    		'theme'=>' <ul class="l"><li>总共 %totalRow% %header% 页码 %nowPage%/%totalPage%</li></ul><ul class="r">%first% %upPage% %prePage% %linkPage% %nextPage% %downPage% %end%</ul>'
    		);    

    /**
     +----------------------------------------------------------
     * 架构函数
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param array $totalRows  总的记录数
     * @param array $listRows  每页显示记录数
     * @param array $parameter  分页跳转的参数
     +----------------------------------------------------------
     */
    public function __construct($totalRows,$listRows,$parameter='') {
        $this->totalRows = $totalRows;
        $this->parameter = $parameter;
        $this->listRows = !empty($listRows)?$listRows:C('PAGE_LISTROWS');
        $this->totalPages = ceil($this->totalRows/$this->listRows);     //总页数
        $this->coolPages  = ceil($this->totalPages/$this->rollPage);
        $this->nowPage  = !empty($_GET[C('VAR_PAGE')])?$_GET[C('VAR_PAGE')]:1;
        if(!empty($this->totalPages) && $this->nowPage>$this->totalPages) {
            $this->nowPage = $this->totalPages;
        }
        $this->firstRow = $this->listRows*($this->nowPage-1);
    }

    public function setConfig($name,$value) {
        if(isset($this->config[$name])) {
            $this->config[$name]    =   $value;
        }
    }

    /**
     +----------------------------------------------------------
     * 后台分页显示输出
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     */
    public function show() {
        if(0 == $this->totalRows) return '';
        $p = C('VAR_PAGE');
        $nowCoolPage      = ceil($this->nowPage/$this->rollPage);
        $url  =  $_SERVER['REQUEST_URI'].(strpos($_SERVER['REQUEST_URI'],'?')?'':"?").$this->parameter;
        $parse = parse_url($url);
        if(isset($parse['query'])) {
            parse_str($parse['query'],$params);
            unset($params[$p]);
            $url   =  $parse['path'].'?'.http_build_query($params);
        }
		
        //上一页  下一页
        $upRow   = $this->nowPage-1;
        $downRow = $this->nowPage+1;
        if ($this->totalPages>1 && $this->nowPage>1) {
        	 $upRow <= 0 && $upRow = 1;
        	 $upPage="<li><a href='".$url."&".$p."=$upRow' class=\"a1\">".$this->config['prev']."</a></li>";
        } else {
        	$upPage = "";
        }
       
        if ($this->nowPage<$this->totalPages) {
        	$downPage <=0 && $downPage = $this->totalPages;
       	 	$downPage="<li><a href='".$url."&".$p."=$downRow' class=\"a1\">".$this->config['next']."</a></li>";
        } else {
        	$downPage = "";
        }
		
        // << < > >>
        if($nowCoolPage == 1){
            $theFirst = "";
            $prePage = "";
        }else{  
            $preRow =  $this->nowPage-$this->rollPage;
            $prePage = "<li><a href='".$url."&".$p."=$preRow' >上".$this->rollPage."页</a></li>";  //上X页
            $theFirst = "<li><a href='".$url."&".$p."=1' >".$this->config['first']."</a></li>";
        }
        if($nowCoolPage == $this->coolPages){
            $nextPage = "";
            $theEnd="";
        }else{
            $nextRow = $this->nowPage+$this->rollPage;
            $theEndRow = $this->totalPages;
            $nextPage = "<li><a href='".$url."&".$p."=$nextRow' >下".$this->rollPage."页</a></li>";  //下x页
            $theEnd = "<li><a href='".$url."&".$p."=$theEndRow' >".$this->config['last']."</a></li>";
        }
        // 1 2 3 4 5
        $linkPage = "";
        for($i=1;$i<=$this->rollPage;$i++){
            $page=($nowCoolPage-1)*$this->rollPage+$i;
            if($page!=$this->nowPage){
                if($page<=$this->totalPages){
                    $linkPage .= "<li><a href='".$url."&".$p."=$page'>".$page."</a></li>";
                }else{
                    break;
                }
            }else{
                if($this->totalPages != 1){
                    $linkPage .= "<li><a href=\"javascript:;\" class='selected'>".$page."</a></li>";
                }
            }
        }
        $pageStr	 =	 str_replace(
            array('%header%','%nowPage%','%totalRow%','%totalPage%','%upPage%','%downPage%','%first%','%prePage%','%linkPage%','%nextPage%','%end%'),
            array($this->config['header'],$this->nowPage,$this->totalRows,$this->totalPages,$upPage,$downPage,$theFirst,$prePage,$linkPage,$nextPage,$theEnd),
            $this->config['theme']);		
        return $pageStr;
    }
    

}
?>