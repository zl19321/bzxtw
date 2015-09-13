<?php /* Smarty version 2.6.26, created on 2015-07-14 11:45:39
         compiled from index.html */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'ffwapmenu', 'index.html', 4, false),)), $this); ?>
<html>
<head></head>
<body>
<?php echo smarty_function_ffwapmenu(array('pagesize' => '2','sort' => "sort asc,create_time desc",'to' => 'data'), $this);?>

<ul>
<?php $_from = $this->_tpl_vars['data']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['v']):
?>
<li>
<a href="<?php echo $this->_tpl_vars['v']['url']; ?>
"><?php echo $this->_tpl_vars['v']['name']; ?>
</a>
</li>
<?php endforeach; endif; unset($_from); ?>
</ul>
</body>
</html>