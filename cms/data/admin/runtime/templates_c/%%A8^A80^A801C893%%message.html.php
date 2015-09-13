<?php /* Smarty version 2.6.26, created on 2015-09-13 02:41:53
         compiled from default/Public/message.html */ ?>
<!DOCTYPE html PUBLIC '-//W3C//DTD XHTML 1.0 Transitional//EN' 'http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd'>
<html>
<head>
<title>系统提示</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta http-equiv='Refresh' content='<?php echo $this->_tpl_vars['waitSecond']; ?>
;URL=<?php echo $this->_tpl_vars['jumpUrl']; ?>
'>
<link type="text/css" rel="stylesheet"
	href="<?php echo @_PUBLIC_; ?>
style/main.css" />
</head>
<body>
<div id="message">
<ul class="l">
	<img src="<?php echo @_PUBLIC_; ?>
images/message_pic_01.jpg" />
</ul>
<ul class="r">
	<li><span><?php echo $this->_tpl_vars['message']; ?>
</span><br> 系统将在 <b class="green"><?php echo $this->_tpl_vars['waitSecond']; ?>
</b>
	秒后自动跳转，如果不想等待，直接点击<a href="<?php echo $this->_tpl_vars['jumpUrl']; ?>
">这里</a>立即跳转。 
	</li>
</ul>
<ul class="clearit"></ul>
</div>
</body>
</html>