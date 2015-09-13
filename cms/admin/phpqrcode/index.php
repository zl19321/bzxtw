<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>二维码生成器</title>
<style>
<!--
.f1{color:#FE596A;}
.f2{color:#006bd0;}
-->
</style>
</head>
<body>
<?php    
/*
 * PHP QR Code encoder
 *
 * Exemplatory usage
 *
 * PHP QR Code is distributed under LGPL 3
 * Copyright (C) 2010 Dominik Dzienia 
<deltalab at poczta dot fm>
*
    * This library is free software; you can redistribute it and/or
    * modify it under the terms of the GNU Lesser General Public
    * License as published by the Free Software Foundation; either
    * version 3 of the License, or any later version.
    *
    * This library is distributed in the hope that it will be useful,
    * but WITHOUT ANY WARRANTY; without even the implied warranty of
    * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
    * Lesser General Public License for more details.
    *
    * You should have received a copy of the GNU Lesser General Public
    * License along with this library; if not, write to the Free Software
    * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA
    */
    echo "<h1>二维码生成器</h1><hr/>";
    include "qrlib.php";
    $errorCorrectionLevel = 'L';
    $matrixPointSize = 4;
    if (isset($_REQUEST['size']))  $matrixPointSize = min(max((int)$_REQUEST['size'], 1), 10);
    $data = isset($_REQUEST['data']) ? trim($_REQUEST['data']) : "";
    if (isset($_REQUEST['data'])) { 
		//it's very important!
		if (empty($data)) die('二维码内容不能为空! <a href="?">返回</a>');
		//set it to writable location, a place for temp generated PNG files
		$PNG_TEMP_DIR = dirname(__FILE__).'.'.DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR."public".DIRECTORY_SEPARATOR."uploads".DIRECTORY_SEPARATOR."brcode".DIRECTORY_SEPARATOR.date ('Y').DIRECTORY_SEPARATOR.date ('m').DIRECTORY_SEPARATOR;

		if (! is_dir ( $PNG_TEMP_DIR ))  mk_dir ( $PNG_TEMP_DIR );
		//html PNG location prefix
        $MID_PATH    = 'uploads/brcode/'.date ('Y/m', time () ).'/';
		$PNG_WEB_DIR = './../../public/'.$MID_PATH;
        
		// user data
		$filename  = $PNG_TEMP_DIR.times().$matrixPointSize.'.png';
		QRcode::png($data, $filename, $errorCorrectionLevel, $matrixPointSize, 2);   
		//display generated file 
		echo '<img src="'.$PNG_WEB_DIR.basename($filename).'" /><hr/>';   
    }  
    //config form
    echo '<form action="index.php" method="post" onsubmit="return validate();">
		  内容:&nbsp;<textarea name="data" id="data">"'.(isset($_REQUEST['data'])?htmlspecialchars($data):'http://www.fangfa.net/').'"</textarea>
		  &nbsp;尺寸:&nbsp;
		  <select name="size">';
	for($i=1;$i<=10;$i++) echo'<option value="'.$i.'"'.(($matrixPointSize==$i)?' selected':'').'>'.'尺寸：'.$i.'</option>';
	echo '</select>&nbsp;<input type="submit" value="生成二维码"></form><hr/>';
    if(isset($_REQUEST['data'])  && $data)
		echo '<strong class="f1">地址：</strong>'.$MID_PATH.basename($filename). '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;[<a href="#" title="Copy To Clipboard" onclick="Javascript:copyToClipboard(\''.$MID_PATH.basename($filename).'\');alert(\'已拷贝至剪贴板\');"  class="f2">复制地址</a>]<hr/>';
     echo '<strong class="f1">说明：</strong>内容是网址 选择(尺寸：1)生成的二维码图片为29px*29px，其它尺寸宽高则乘以其倍数。<hr/>';
    //benchmark
	//循环创建目录
	function mk_dir($dir, $mode = 0755){
	  if (is_dir($dir) || @mkdir($dir,$mode)) return true;
	  if (!mk_dir(dirname($dir),$mode)) return false;
	  return @mkdir($dir,$mode);
	}
	/**
	 * 生成唯一的时间串、用户重命名上传文件
	 */
	function times() {
		return date('YmdHis',time()).rand(100,199);
	}
?>
<script language="javascript">
validate = function(){
  var data = (document.getElementById("data").value).replace(/^\s*|\s*$/g, "");
  if(data.length == 0){alert('二维码内容不能为空');return false;} return true;
}
copyToClipboard = function(txt){
 if(window.clipboardData){
    window.clipboardData.clearData();
    window.clipboardData.setData("Text", txt);
 }
 else if(navigator.userAgent.indexOf("Opera") != -1){
   //暂时无方法:-(
 }
 else if (window.netscape){
  try{
    netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
  }
  catch (e){
    alert("您的firefox安全限制限制您进行剪贴板操作，请打开’about:config’将signed.applets.codebase_principal_support’设置为true’之后重试");
    return false;
  }
  var clip  = Components.classes['@mozilla.org/widget/clipboard;1'].createInstance(Components.interfaces.nsIClipboard);
  if (!clip) return;
  var trans = Components.classes['@mozilla.org/widget/transferable;1'].createInstance(Components.interfaces.nsITransferable);
  if (!trans) return;
  trans.addDataFlavor('text/unicode');
  var str = new Object();
  var len = new Object();
  var str = Components.classes["@mozilla.org/supports-string;1"].createInstance(Components.interfaces.nsISupportsString);
  var copytext = txt;
  str.data     = copytext;
  trans.setTransferData("text/unicode", str, copytext.length*2);
  var clipid = Components.interfaces.nsIClipboard;
  if (!clip) return false;
  clip.setData(trans,null,clipid.kGlobalClipboard);
 }
}
</script>
</body>
</html>