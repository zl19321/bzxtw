<?php /* Smarty version 2.6.26, created on 2015-09-13 02:41:56
         compiled from default/Findex/home.html */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'date', 'default/Findex/home.html', 16, false),array('function', 'U', 'default/Findex/home.html', 22, false),)), $this); ?>
<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "default/Public/header.html", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
<script language="javaScript">
$().ready(function () {
	now = new Date(),hour = now.getHours()
	if(hour < 6){$("#time").html("凌晨好"); }
	else if (hour < 9){$("#time").html("早上好");}
	else if (hour < 12){$("#time").html("上午好");}
	else if (hour < 14){$("#time").html("中午好");}
	else if (hour < 17){$("#time").html("下午好");}
	else {$("#time").html("晚上好");}
})
</script>
<div id="home">
	<div class="welcome">
		<span id="time"></span><a href="?m=fprofile&a=pwd"><?php echo $this->_tpl_vars['userdata']['username']; ?>
</a>，欢迎您回来！
		<p>您当前的身份是<span class="red"><?php echo $this->_tpl_vars['userdata']['rolenickname']; ?>
</span><?php if ($this->_tpl_vars['userdata']['user_id'] != '999999'): ?>，上次登陆IP是<?php echo $this->_tpl_vars['userdata']['last_login_ip']; ?>
，上次登陆时间是<?php echo ((is_array($_tmp='Y-m-d H:i:s')) ? $this->_run_mod_handler('date', true, $_tmp, $this->_tpl_vars['userdata']['last_login_time']) : date($_tmp, $this->_tpl_vars['userdata']['last_login_time'])); ?>
<?php endif; ?></p>
	</div>
	<div class="btns">
		<ul>
			<li><a href="#" class="a1">内容管理</a></li>
			<?php if ($this->_tpl_vars['userdata']['user_id'] == '999999'): ?>
			<li><a href="<?php echo $this->_plugins['function']['U'][0][0]->_U(array('url' => 'fcategory/manage'), $this);?>
" class="a2">栏目管理</a></li>
			<li><a href="<?php echo $this->_plugins['function']['U'][0][0]->_U(array('url' => 'fmodel/manage'), $this);?>
" class="a3">模型模块</a></li>
			<?php endif; ?>
			<li><a href="<?php echo $this->_plugins['function']['U'][0][0]->_U(array('url' => 'fset/set'), $this);?>
" class="a4">系统设置</a></li>
			<li><a href="<?php echo $this->_plugins['function']['U'][0][0]->_U(array('url' => 'fcache/all'), $this);?>
" class="a5">更新缓存</a></li>
            
		</ul>
	</div>
</div>
<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "default/Public/footer.html", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>