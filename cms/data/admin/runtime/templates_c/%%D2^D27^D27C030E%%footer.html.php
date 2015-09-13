<?php /* Smarty version 2.6.26, created on 2015-09-13 02:41:56
         compiled from default/Public/footer.html */ ?>
<script type="text/javascript" src="__ROOT__/min/?b=admin/Public&f=js/validation/jquery.validate.js,js/jquery.dialog.js,js/page.js"></script>
<?php if (! empty ( $this->_tpl_vars['note'] )): ?>
<script language="javascript">
JqueryDialog.OpenMessage('<?php echo $this->_tpl_vars['note']['title']; ?>
','<?php echo $this->_tpl_vars['note']['content']; ?>
',400,150, false, false, true);
</script>
<?php endif; ?>
</body>
</html>