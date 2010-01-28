<?php /* Smarty version 2.6.0, created on 2010-01-27 17:43:12
         compiled from docblock.tpl */ ?>
<?php require_once(SMARTY_DIR . 'core' . DIRECTORY_SEPARATOR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'default', 'docblock.tpl', 1, false),)), $this); ?>
<?php if ($this->_tpl_vars['sdesc'] != ''): ?><p><?php echo ((is_array($_tmp=@$this->_tpl_vars['sdesc'])) ? $this->_run_mod_handler('default', true, $_tmp, '') : smarty_modifier_default($_tmp, '')); ?>
</p><?php endif; ?>
<?php if ($this->_tpl_vars['desc'] != ''): ?><div><?php echo ((is_array($_tmp=@$this->_tpl_vars['desc'])) ? $this->_run_mod_handler('default', true, $_tmp, '') : smarty_modifier_default($_tmp, '')); ?>
</div><?php endif; ?>