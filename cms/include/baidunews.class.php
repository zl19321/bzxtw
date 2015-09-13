<?php
// +----------------------------------------------------------------------
// | FangFa Net [ http://www.fangfa.net ]
// +----------------------------------------------------------------------
// | File: sitemaps.class.php
// +----------------------------------------------------------------------
// | Date: 2010 18:04:29
// +----------------------------------------------------------------------
// | Author: 王超 <wangchao20000@163.com>
// +----------------------------------------------------------------------
// | 文件描述: baidu news 生成类
// +----------------------------------------------------------------------
class baidunews
{
	var $file_name;
	var $webname;
	var $baidunews_items = array();
	var $num = 100;
    var $limit = 100;
    var $updateperi = '';
	function baidunews($webname='', $adminemail = '', $updateperi, $num = 100)
	{
        $this->updateperi = $updateperi ? intval($updateperi): '40';
		$this->limit = intval($num);
		$this->baidunews = "<?xml version=\"1.0\" encoding=\"utf-8\" ?>\n";
		$this->baidunews .= "<document>\n";
		$this->baidunews .= "<webSite>$webname</webSite>\n";
		$this->baidunews .= "<webMaster>$adminemail </webMaster>\n";
		$this->baidunews .= "<updatePeri>$this->updateperi </updatePeri>\n";
	}
	function set_xml()
	{
		if($this->limit > 100 || $this->limit < 1)
		{
			$this->limit = 10;
		}

		foreach($this->baidunews_items AS $k => $v)
		{
			$title = htmlspecialchars($v['title']);
			$link = htmlspecialchars($v['link']);
			$description = htmlspecialchars(strip_tags($v['description']));
			$text = htmlspecialchars(strip_tags($v['content']));
            $image = $v['thumb'];

			$keywords = htmlspecialchars($v['seokeywords']);
			$category = htmlspecialchars($v['category_name']);
			$author = htmlspecialchars($v['username']);
			$source = htmlspecialchars($v['siteurl']);
			$pubdate = htmlspecialchars(date('Y-m-d H:i', $v['update_time']));

			$this->baidunews .= "<item>\n";
			$this->baidunews .= "<title>$title </title>\n";
			$this->baidunews .= "<link>$link </link>\n";
			$this->baidunews .= "<description>$description </description>\n";
			$this->baidunews .= "<text>$text </text>\n";
			$this->baidunews .= "<image>$image </image>\n";

			$this->baidunews .= "<keywords>$keywords </keywords>\n";
			$this->baidunews .= "<category>$category </category>\n";
			$this->baidunews .= "<author>$author </author>\n";
			$this->baidunews .= "<source>$source </source>\n";
			$this->baidunews .= "<pubDate>$pubdate </pubDate>\n";
			$this->baidunews .= "</item>\n";
		}
		$this->baidunews .= "</document>\n";

		$fp = fopen($this->file_name, 'wb');
		fwrite($fp,$this->baidunews);
		fclose($fp);
	}
	function get_xml()
	{
	}
}