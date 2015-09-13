<?php
// +----------------------------------------------------------------------
// | FangFa Net [ http://www.fangfa.net ]
// +----------------------------------------------------------------------
// | File: Fcollect.class.php
// +----------------------------------------------------------------------
// | Date: 2011-11-02
// +----------------------------------------------------------------------
// | Author: mark <376727439@qq.com>
// +----------------------------------------------------------------------
// | 文件描述: 信息采集管理
// +----------------------------------------------------------------------


defined ( 'IN_ADMIN' ) or die ( 'Access Denied' );
/**
 * @name 栏目管理控制器
 *
 */
class FcollectAction extends FbaseAction {

	/**
	 * 结点地址
	 * */
	protected $source_url;
	
	/**
	 * 结点编码
	 * */
	protected $source_lang;
	
	/**
	 * 获取地址方式
	 * */
	protected $source_type;
	
	/**
	 * 需要替换的文字
	 * */
	protected $replace_before;
	
	/**
	 * 替换后的文字
	 * */
	protected $replace_after;
	/**
	 * 目标站源文件
	 * */
	protected $html;
	
	/**
	 * 目标站文章获取规则
	 * */
	protected $replace;
	
	protected $Count;
	protected $Items;
	protected $SourceString;
	protected $medias = array();
	protected $media_infos= array();
	protected $Links;
	protected $BaseUrl;
	protected $Title;
	protected $HomeUrl;
	protected $BaseUrlPath;
	/**
	 * @name初始化
	 */
	protected function _initialize() {
		parent::_initialize ();
		$in = &$this->in;
	}

	/**
	 * @name添加栏目
	 */
	public function add() {
		$in = &$this->in;
		if ($this->ispost()) {
			if (D("Collect")->add($in)) {
				$this->message("添加成功！", U("fcollect/manage"));
			}else {
				$this->message("添加失败！", U("fcollect/manage"));
			}
			
		}
		$category = D("Category")->where("modelid=1")->order("catid")->findAll();
		$this->assign("category",$category);
		$this->display();
	}

	public function manage(){
		$in = &$this->in;
		$_collect = D("Collect");
		
		$data = $_collect->field("c_id,name,into_catid,create_time,update_time")->order("c_id DESC")->findAll();
		$this->assign("data",$data);
		$this->display();
		
	}
	/**
	 * 修改结点信息
	 * */
	public function edit(){
		$in = &$this->in;
		$_collect = D("Collect");
		
		if ($this->ispost()) {
			$in['replace'] = var_export ( $in ['replace'], true );
			if (false === $_collect->where("c_id=".$in['c_id'])->save($in)) {
				$this->message("内容更新失败！",$this->forward);
			}else {
				$this->message("内容更新成功！",$this->forward);
			}
		}
		$data = $_collect->where("c_id=".$in['c_id'])->find();
		$category = D("Category")->where("modelid=1")->order("catid")->findAll();
		$this->assign("category",$category);
		$this->assign("data",$data);
		$this->display();
	}
	/**
	 * 删除结点及结点下缓存文章
	 * */
	public function del(){
		$in = &$this->in;
		if(empty($in['c_id'])){
			$this->message("参数错误");
		}
		$_collect = D("Collect");
		$_page    = D("CollectPage");
		$bool = $_collect->where("c_id=".$in['c_id'])->delete();
		if ($bool) {
			$_page->where("c_id=".$in['c_id'])->delete();
			$this->message("删除结点及节点下临时数据成功！");
		}else {
			$this->message("删除结点失败！");
		}
	}
	/**
	 * 采集结点文章
	 * 如果临时数据内没有采集信息，才采集
	 * */
	public function collect(){
		$in = &$this->in;
		$_collect_page = D("CollectPage");
		$count = $_collect_page->where("c_id=".$in['c_id'])->count();
		if ($count > 0){
			redirect(U("fcollect/collect_page?c_id=".$in['c_id']));exit();
		}
		$this->assign("c_id",$in['c_id']);
		$this->display();
	}
	
	/**
	 * 开始采集结点
	 * */
	public function starturl(){
		$in = &$this->in;
		header( 'Content-Type:   text/html;   charset=utf-8 ');
		echo "开始采集......<br/>";
		ob_flush();
		flush();
		$_collect = D("Collect");
		$collect = $_collect->where("c_id=".$in['c_id'])->find();
	
		$this->source_type = $collect['source_type'];
		$this->source_url = $collect['source_url'];
		$this->HomeUrl = $collect['profiex_url'];
		$this->source_lang = $collect['source_lang'];
		$this->replace_after = $collect['replace_after'];
		$this->replace_before = $collect['replace_before'];
		$this->replace = $collect['replace'];
		
		//分析url
		$this->site_url();
		foreach ($this->source_url as $k => $t){
			$this->html = "";
			$this->BaseUrlPath = $t;
		
			if ($s = fopen( $t ,"r" )) { // base64_decode
			    $this->html = stream_get_contents($s, -1);
				fclose( $s );
			}else {
				continue;
			}
			
			//对原文件编码和替换文字的处理
			$this->html_deal($this->html);
			
			if( trim($collect['html_start']) !='' && trim($collect['html_end']) != '' ) {
	            $area = $collect['html_start'].'[var:区域]'.$collect['html_end'];
	            $this->html = $this->html_area($this->html, '[var:区域]', $area);
	        }
	        //获取区域内
	        $this->area_url();//$this->Links;
			
		}
		
		if(empty($this->Links)){
			echo "没有找到可用的链接，请检查设置是否正确<br/>";
			echo "<a href=\"#\" onclick=\"window.parent.location = '".U("fcollect/manage")."'\">返回列表</a>";
			exit();
		}
		$collect['have_is'] = trim($collect['have_is']);
		$collect['have_not'] = trim($collect['have_not']);
		//开始采集内容
		foreach ( $this->Links as $r => $link){
			//筛选包含指定字符串的链接
			if ($collect['have_is']!= '' && !strstr($link['link'],$collect['have_is'])){
				continue;
			}
			if ($collect['have_not'] != '' && $collect['have_is'] != $collect['have_not'] && strstr($link['link'],$collect['have_not'])){
				continue;
			}
			$this->get_page($link);
		}
	   	
		$collect = $_collect->where("c_id=".$in['c_id'])->save(array("update_time"=>time()));
		
		echo "<a href=\"#\" onclick=\"window.parent.location = '".U("fcollect/collect_page?c_id=".$in['c_id'])."'\">查看采集结果</a>";
		
	}
	
	/**
	 * 处理结点源文件，替换编号和替换文字
	 * */
	private function html_deal(&$input){
		//替换编码
		if ($this->source_lang != 'utf-8') {
			$input = iconv($this->source_lang,"utf-8",$input);
		}
		
		//替换文字
		if (empty($this->replace_before) || empty($this->replace_after)) return false;
		$this->replace_before = explode(",",$this->replace_before);
		$this->replace_after = explode(",",$this->replace_after);
		
		foreach ($this->replace_before as $k => $t){
			if (empty($t)) continue;
			//处理替换前词组为多个，替换后的词组为一个的情况。
			if (isset($this->replace_after[$k])){
				$after = $this->replace_after[$k];
			}else {
				$after = $this->replace_after[0];
			}
			$input = str_replace($t,$after,$input);
		}
	}
	/**
	 * 获取结点链接
	 */
	private function site_url(){
		if ($this->source_type == "hand") {//手动设置url
			$this->source_url = explode("\r\n", $this->source_url);
		}
		
		foreach ($this->source_url as $k => $t){
			if (empty($t)) {
				unset($this->source_url[$k]);
			}
		}
	}
	
	/**
	 * 传入链接源文件、开始截取前端HTML和结尾HTMl。
	 * 返回中间截取部分。
	 * 
	 * */
	private function html_area($html, $sptag, &$areaRule)
    {
        //用正则表达式的模式匹配
       /* if($this->noteInfos['matchtype']=='regex')
        {
            $areaRule = str_replace("/", "\\/", $areaRule);
            $areaRules = explode($sptag, $areaRule);
            $arr = array();
            if($html==''||$areaRules[0]=='')
            {
                return '';
            }
            preg_match('#'.$areaRules[0]."(.*)".$areaRules[1]."#isU", $html, $arr);
            return empty($arr[1]) ? '' : trim($arr[1]);
        }

        //用字符串模式匹配
        else
        {*/
            $areaRules = explode($sptag,$areaRule);
            
            if($html=='' || $areaRules[0]=='')
            {
                return '';
            }
            $posstart = @strpos($html,$areaRules[0]);
            if($posstart===FALSE)
            {
                return '';
            }
            $posstart = $posstart + strlen($areaRules[0]);
            $posend = @strpos($html,$areaRules[1],$posstart);
            if($posend > $posstart && $posend!==FALSE)
            {
                //return substr($html,$posstart+strlen($areaRules[0]),$posend-$posstart-strlen($areaRules[0]));
                return substr($html,$posstart,$posend-$posstart);
            }
            else
            {
                return '';
            }
        //}
    }
    
   
   /**
    * 获取区域内的链接和标题
    * */
   private function area_url()
    {
        $c = '';
        $i = 0;
        $startPos = 0;
        $endPos = 0;
        $wt = 0;
        $ht = 0;
        $scriptdd = 0;
        $attStr = '';
        $tmpValue = '';
        $tmpValue2 = '';
        $tagName = '';
        $hashead = 0;
        $slen = strlen($this->html);
        if($this->GetLinkType=='link' || $this->GetLinkType=='')
        {
            $needTags = array('a');
        }
        if($this->GetLinkType=='media')
        {
            $needTags = array('img','embed','a');
            $this->IsHead = true;
        }
        $tagbreaks = array(' ','<','>',"\r","\n","\t");
        for(;isset($this->html[$i]);$i++)
        {
            if($this->html[$i]=='<')
            {
                $tagName = '';
                $j = 0;
                for($i=$i+1; isset($this->html[$i]); $i++)
                {
                    if($j>10)
                    {
                        break;
                    }
                    $j++;
                    if( in_array($this->html[$i],$tagbreaks) )
                    {
                        break;
                    }
                    else
                    {
                        $tagName .= $this->html[$i];
                    }
                }
                $tagName = strtolower($tagName);

                //标记为注解
                if($tagName=='!--')
                {
                    $endPos = strpos($this->html,'-->',$i);
                    if($endPos !== false)
                    {
                        $i=$endPos+3;
                    }
                    continue;
                }

                //标记在指定集合内
                else if( in_array($tagName,$needTags) )
                {
                    $startPos = $i;
                    $endPos = strpos($this->html,'>',$i+1);
                    if($endPos===false)
                    {
                        break;
                    }
                    $attStr = substr($this->html,$i+1,$endPos-$startPos-1);
                    $this->set_source($attStr);
                    if($tagName=='img')
                    {
                        $this->insert_media($this->get_att('src'),'img');
                    }
                    else if($tagName=='embed'){
                        $rurl = $this->insert_media($this->get_att('src'),'embed');
                        if($rurl != '')
                        {
                            $this->media_infos[$rurl][0] = $this->get_att('width');
                            $this->media_infos[$rurl][1] = $this->get_att('height');
                        }
                    }
                    else if($tagName=='a')
                    { 
                        $this->insert_link($this->fill_url($this->get_att('href')),$this->get_inner_text($i,'a'));
                        
                    }
                }
                else
                {
                    continue;
                }
            }//End if char
        }//End for
        if($this->Title == '')
        {
            $this->Title = $this->BaseUrlPath;
        }
    }
    
    //设置属性解析器源字符串
    private function set_source($str = ''){
        $this->Count = -1;
        $this->Items = '';
        $strLen = 0;
        $this->SourceString = trim(preg_replace("/[ \t\r\n]{1,}/"," ",$str));
        $strLen = strlen($this->SourceString);
        $this->SourceString .= " "; //增加一个空格结尾,以方便处理没有属性的标记
        if($strLen>0&&$strLen<=1024){
            $this->PrivateAttParse();
        }
    }
    
    //解析属性(仅给SetSource调用)
    private function PrivateAttParse(){
        $d = '';
        $tmpatt = '';
        $tmpvalue = '';
        $startdd = -1;
        $ddtag = '';
        $strLen = strlen($this->SourceString);
        $j = 0;

        //这里是获得标记的名称
        if($this->IsTagName){
            //如果属性是注解，不再解析里面的内容，直接返回
            if(isset($this->SourceString[2])){
                if($this->SourceString[0].$this->SourceString[1].$this->SourceString[2]=='!--'){
                    $this->Items['tagname'] = '!--';
                    return ;
                }
            }
            for($i=0;$i<$strLen;$i++)
            {
                $d = $this->SourceString[$i];
                $j++;
                if(preg_match("/[ '\"\r\n\t]/i", $d))
                {
                    $this->Count++;
                    $this->Items["tagname"]=strtolower(trim($tmpvalue));
                    $tmpvalue = ''; break;
                }
                else
                {
                    $tmpvalue .= $d;
                }
            }
            if($j>0)
            {
                $j = $j-1;
            }
        }

        //遍历源字符串，获得各属性
        for($i=$j;$i<$strLen;$i++)
        {
            $d = $this->SourceString[$i];
            //获得属性的键
            if($startdd==-1)
            {
                if($d!='=')
                {
                    $tmpatt .= $d;
                }
                else
                {
                    $tmpatt = strtolower(trim($tmpatt));
                    $startdd=0;
                }
            }

            //检测属性值是用什么包围的，允许使用 '' '' 或空白
            else if($startdd==0)
            {
                switch($d)
                {
                    case ' ':
                        continue;
                        break;
                    case '\'':
                        $ddtag='\'';
                        $startdd=1;
                        break;
                    case '"':
                        $ddtag='"';
                        $startdd=1;
                        break;
                    default:
                        $tmpvalue.=$d;
                        $ddtag=' ';
                        $startdd=1;
                        break;
                }
            }

            //获得属性的值
            else if($startdd==1)
            {
                if($d==$ddtag)
                {
                    $this->Count++;
                    if($this->CharToLow)
                    {
                        $this->Items[$tmpatt] = strtolower(trim($tmpvalue));
                    }
                    else
                    {
                        $this->Items[$tmpatt] = trim($tmpvalue);
                    }
                    $tmpatt = '';
                    $tmpvalue = '';
                    $startdd=-1;
                }
                else
                {
                    $tmpvalue.=$d;
                }
            }
        }//End for

        //处理没有值的属性(必须放在结尾才有效)如："input type=radio name=t1 value=aaa checked"
        if(empty($tmpatt) != '')
        {
            $this->Items[$tmpatt] = '';
        }
    } 
    
    private function get_att($str)
    {
        if($str == '')
        {
            return '';
        }
        $str = strtolower($str);
        if(isset($this->Items[$str]))
        {
            return $this->Items[$str];
        }
        else
        {
            return '';
        }
    }
    private function insert_media($url, $mtype)
    {
        if( preg_match("/^(javascript:|#|'|\")/", $url) )
        {
            return '';
        }
        if($url == '')
        {
            return '';
        }
        $this->medias[$url]=$mtype;
        return $url;
    }
    
    private function get_inner_text(&$pos,$tagname)
    {
        $startPos=0;
        $endPos=0;
        $textLen=0;
        $str = '';
        $startPos = strpos($this->html,'>',$pos);

        if($tagname=='title')
        {
            $endPos = strpos($this->html,'<',$startPos);
        }
        else
        {
            $endPos1 = strpos($this->html,'</a',$startPos);
            $endPos2 = strpos($this->html,'</A',$startPos);
            if($endPos1===false)
            {
                $endPos = $endPos2;
            }
            else if($endPos2===false)
            {
                $endPos = $endPos1;
            }
            else
            {
                $endPos = ($endPos1 < $endPos2 ? $endPos1 : $endPos2 );
            }
        }
        if($endPos > $startPos)
        {
            $textLen = $endPos-$startPos;
            $str = substr($this->html,$startPos+1,$textLen-1);
        }
        $pos = $startPos + $textLen + strlen("</".$tagname) + 1;
        if($tagname=='title')
        {
            return trim($str);
        }
        else
        {
            preg_match_all("/<img(.*)src=[\"']{0,1}(.*)[\"']{0,1}[> \r\n\t]{1,}/isU",$str,$imgs);
            if(isset($imgs[2][0]))
            {
                $txt = trim(Html2Text($str));
                $imgs[2][0] = preg_replace("/[\"']/",'',$imgs[2][0]);
                return "img:".$this->FillUrl($imgs[2][0]).':txt:'.$txt;
            }
            else
            {
            	$str = strip_tags($str);
                //$str = preg_replace('/<\/(.*)$/i', '', $str);
                //$str = trim(preg_replace('/^(.*)>/i','',$str));
                return $str;
            }
        }
    }
    
    private function fill_url($surl)
    {
        $i = $pathStep = 0;
        $dstr = $pstr = $okurl = '';

        $surl = trim($surl);
        if($surl == '')
        {
            return '';
        }
        $pos = strpos($surl,'#');
        if($pos>0)
        {
            $surl = substr($surl,0,$pos);
        }
        if($surl[0]=='/')
        {
            $okurl = $this->HomeUrl."/".$surl;
        }
        else if($surl[0]=='.')
        {
            if(!isset($surl[2]))
            {
                return '';
            }
            else if($surl[0]=='/')
            {
                $okurl = $this->BaseUrlPath."/".substr($surl,2,strlen($surl)-2);
            }
            else
            {
                $urls = explode('/',$surl);
                foreach($urls as $u)
                {
                    if($u=='..')
                    {
                        $pathStep++;
                    }
                    else if($i<count($urls)-1)
                    {
                        $dstr .= $urls[$i].'/';
                    }
                    else
                    {
                        $dstr .= $urls[$i];
                    }
                    $i++;
                }
                $urls = explode('/',$this->BaseUrlPath);
                if(count($urls) <= $pathStep)
                {
                    return '';
                }
                else
                {
                    $pstr = '';
                    for($i=0;$i<count($urls)-$pathStep;$i++){ $pstr .= $urls[$i].'/'; }
                    $okurl = $pstr.$dstr;
                }
            }
        }
        else
        {
            if( strlen($surl) < 7 )
            {
                $okurl = $this->BaseUrlPath.'/'.$surl;
            }
            else if( strtolower(substr($surl,0,7))=='http://' )
            {
                $okurl = preg_replace('/^http:\/\//i', '', $surl);
            }
            else
            {
                $okurl = $this->BaseUrlPath.'/'.$surl;
            }
        }
        $okurl = str_replace("http://","",$okurl);
        $okurl = preg_replace('/\/{1,}/i', '/', $okurl);
        return 'http://'.$okurl;
    }
    
    private function insert_link($url, $atitle)
    {
        if( preg_match("/^(javascript:|#|'|\")/", $url) )
        {
            return '';
        }
        if($url == '')
        {
            return '';
        }
        if( preg_match('/^img:/', $atitle) )
        {
            list($aimg, $atitle) = explode(':txt:', $atitle);
            if(!isset($this->Links[$url]))
            {
                if($atitle != '')
                {
                    $this->Links[$url]['title'] = cn_substr($atitle,50);
                }
                else
                {
                    $this->Links[$url]['title'] = preg_replace('/img:/', '', $aimg);
                }
                $this->Links[$url]['link']  = $url;
            }
            $this->Links[$url]['image'] = preg_replace('/img:/', '', $aimg);
            $this->insert_media($this->Links[$url]['image'], 'img');
        }
        else
        {
            if(!isset($this->Links[$url]))
            {
                $this->Links[$url]['image'] = '';
                $this->Links[$url]['title'] = $atitle;
                $this->Links[$url]['link']  = $url;
            }
            else
            {
                if(strlen($this->Links[$url]['title']) < strlen($atitle)) $this->Links[$url]['title'] = $atitle;
            }
        }
        return $url;
    }
    private function get_page($link){
    	$in = &$this->in;
		$data = array();
    	
    	if ($s = fopen( $link['link'] ,"r" )) { // base64_decode
		    $page = stream_get_contents($s, -1);
			fclose( $s );
		}else {
			return false;
		}
		$this->html_deal($page);
		foreach ($this->replace as $k => $t){
			//处理需要匹配的字段。
			if (!strstr( $k,"replace" )) { continue; }
			if ($k == "replace_before" || $k == "replace_after") {unset($this->replace[$k]);}
			if (empty($t)) {continue;}
        	$data[$k] = $this->html_area($page,'[!--content--]',$t);
        	
        	//得到需要过滤的字段名称
        	$filter = "";
        	$filter = str_replace("replace", "filter",  $k);
        	if(empty($this->replace[$filter])){ continue;}
        	preg_match_all("/{fangfa}([^\"]+?){\/fangfa}/",$this->replace[$filter],$filter_preg);

        	foreach($filter_preg[1] as $preg){
				 $data[$k] = preg_replace("#".$preg."#isU", "", $data[$k]);
			}
			$data[$k] = trim( $data[$k] );
		}
		if (!empty($data['replace_time'])) {
			$data['create_time'] = (int) strtotime($data['replace_time']);
		}
		
		$data['title'] = $data['replace_title'];
		$data['content'] = $data['replace_content'];
		$data['source'] = $data['replace_source'];
		$data['thumb'] = $link['image'];
		$data['link'] = $link['link'];
		$data['c_id'] = $in['c_id'];
		echo "采集网址：".$link['link']." 完成<br/>";
		ob_flush();
		flush();
		$_collect_page = D("CollectPage");
		$_collect_page->add($data);
		
		unset($data);
    }
    /**
     * 显示结点采集到的信息列表
     * */
	public function collect_page(){
		$in = &$this->in;
		$_collect_page = D("CollectPage");
		import("ORG.Util.Page");
		$data['count'] = $_collect_page->where("c_id=".$in['c_id'])->count();
		$Page = new Page($data['count'],15);
		$data['pages'] = $Page->show();
		$data['info'] = $_collect_page->where("c_id=".$in['c_id'])->order("pid asc")->limit($Page->firstRow.",".$Page->listRows)->select();
		$this->assign("data",$data);
		$this->assign("c_id",$in['c_id']);
		$this->display();
	}
	
	/**
	 * 查看临时数据详情
	 * */
	public function show(){
		$in = &$this->in;
		$_collect_page = D("CollectPage");
		if ($this->ispost()) {
			if(!empty($in['info']['create_time'])){
				$in['info']['create_time'] = strtotime($in['info']['create_time']);
			}
			$bool = $_collect_page->where("pid=" . $in['pid'])->save($in['info']);
			if ($bool == true) {
				$this->message("保存成功！", U("fcollect/collect_page?c_id=".$in['c_id']));
			}else {
				$this->message("保存失败！", U("fcollect/collect_page?c_id=".$in['c_id']));
			}
		}
		$data = $_collect_page->where("pid=".$in['pid'])->find();
		if (empty($data)) {
			$this->message("没有找到对应是数据");
		}
		$this->assign("data",$data);
		$this->display();
	}
	/**
	 * 删除临时数据库
	 * */
	public function del_page(){
		$in = &$this->in;
		$_collect_page = D("CollectPage");
		$bool = $_collect_page->where("pid=".$in['pid'])->delete();
		if($bool) echo "1";
		else  echo "2";
		exit();
	}
	public function del_page_all(){
		$in = &$this->in;
		$_collect_page = D("CollectPage");
		if (empty($in['info']['pid'])) {
			$this->message("参数错误！",U("fcollect/collect_page?c_id=".$in['c_id']));
		}
		$pid = implode(",",$in['info']['pid']);
		
		$bool = $_collect_page->where("pid in ($pid)")->delete();
		if($bool) 
			$this->message("操作成功！",U("fcollect/collect_page?c_id=".$in['c_id']));
		else  
			$this->message("操作失败！",U("fcollect/collect_page?c_id=".$in['c_id']));
	}
	/**
	 * 导入临时数据
	 * */
	public function explode(){
		$in = &$this->in;
		if (empty($in['info']['pid']) || empty($in['c_id'])) {
			$this->message("参数错误！");
		}
		$_collect_page = D("CollectPage");
		$_content = D("Content");
		$_collect = D("Collect");
		
		$pid = implode(",", $in['info']['pid']);
		$data = $_collect_page->where("pid in ($pid)")->findAll();
		
		$catid = $_collect->where("c_id=".$in['c_id'])->getField("into_catid");
		
		$username = $_SESSION['userdata']['username'];
		$user_id = $_SESSION['userdata']['user_id'];
		
		foreach ($data as $k=>$t){
			if($t['status'] == 1){
					continue;
			}
			unset($t['cid']);
			$t['catid'] = $catid;
			$t['username'] = $username;
			$t['user_id'] = $user_id;
			$t['update_time'] = time();
			
			$cid = $_content->add($t);
			$_collect_page->where("pid=".$t['pid'])->save(array("status"=>1));
			
		}
		$this->message("导入数据成功！");
		
	}
	
}
	
	
?>