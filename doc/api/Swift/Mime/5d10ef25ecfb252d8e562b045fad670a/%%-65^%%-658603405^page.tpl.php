<?php /* Smarty version 2.6.0, created on 2010-01-27 17:42:41
         compiled from page.tpl */ ?>
<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "header.tpl", 'smarty_include_vars' => array('eltype' => 'Procedural file','class_name' => $this->_tpl_vars['name'],'hasel' => true,'contents' => $this->_tpl_vars['pagecontents'])));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>

<a name="sec-description"></a>
<h2>Page Details</h2>
<div class="docblock">
<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "docblock.tpl", 'smarty_include_vars' => array('type' => 'page','desc' => $this->_tpl_vars['desc'],'sdesc' => $this->_tpl_vars['sdesc'])));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
</div>
<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "filetags.tpl", 'smarty_include_vars' => array('tags' => $this->_tpl_vars['tags'])));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>

<?php if ($this->_tpl_vars['tutorial']): ?>
  <hr class="separator" />
  <div class="notes">Tutorial: <span class="tutorial"><?php echo $this->_tpl_vars['tutorial']; ?>
</div>
<?php endif; ?>

<?php if ($this->_tpl_vars['classes']): ?>
  <a name="sec-classes"></a>
  <h2>Classes</h2>
  <table class="detail">
    <thead>
      <tr>
        <th>Class</th>
        <th>Description</th>
      </tr>
    </thead>
    <tbody>
      <?php if (isset($this->_sections['classes'])) unset($this->_sections['classes']);
$this->_sections['classes']['name'] = 'classes';
$this->_sections['classes']['loop'] = is_array($_loop=$this->_tpl_vars['classes']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
$this->_sections['classes']['show'] = true;
$this->_sections['classes']['max'] = $this->_sections['classes']['loop'];
$this->_sections['classes']['step'] = 1;
$this->_sections['classes']['start'] = $this->_sections['classes']['step'] > 0 ? 0 : $this->_sections['classes']['loop']-1;
if ($this->_sections['classes']['show']) {
    $this->_sections['classes']['total'] = $this->_sections['classes']['loop'];
    if ($this->_sections['classes']['total'] == 0)
        $this->_sections['classes']['show'] = false;
} else
    $this->_sections['classes']['total'] = 0;
if ($this->_sections['classes']['show']):

            for ($this->_sections['classes']['index'] = $this->_sections['classes']['start'], $this->_sections['classes']['iteration'] = 1;
                 $this->_sections['classes']['iteration'] <= $this->_sections['classes']['total'];
                 $this->_sections['classes']['index'] += $this->_sections['classes']['step'], $this->_sections['classes']['iteration']++):
$this->_sections['classes']['rownum'] = $this->_sections['classes']['iteration'];
$this->_sections['classes']['index_prev'] = $this->_sections['classes']['index'] - $this->_sections['classes']['step'];
$this->_sections['classes']['index_next'] = $this->_sections['classes']['index'] + $this->_sections['classes']['step'];
$this->_sections['classes']['first']      = ($this->_sections['classes']['iteration'] == 1);
$this->_sections['classes']['last']       = ($this->_sections['classes']['iteration'] == $this->_sections['classes']['total']);
?>
        <tr>
          <td><?php echo $this->_tpl_vars['classes'][$this->_sections['classes']['index']]['link']; ?>
</td>
          <td>
            <?php if ($this->_tpl_vars['classes'][$this->_sections['classes']['index']]['sdesc']): ?>
              <?php echo $this->_tpl_vars['classes'][$this->_sections['classes']['index']]['sdesc']; ?>

            <?php else: ?>
              <?php echo $this->_tpl_vars['classes'][$this->_sections['classes']['index']]['desc']; ?>

            <?php endif; ?>
          </td>
        </tr>
      <?php endfor; endif; ?>
    </tbody>
  </table>
<?php endif; ?>

<?php if ($this->_tpl_vars['includes']): ?>
  <a name="sec-includes"></a>
  <h2>Includes</h2>
  <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "include.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
<?php endif; ?>

<?php if ($this->_tpl_vars['defines']): ?>
  <a name="sec-constants"></a>
  <h2>Constants</h2>
  <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "define.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
<?php endif; ?>

<?php if ($this->_tpl_vars['globals']): ?>
  <a name="sec-variables"></a>
  <h2>Globals</h2>
  <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "global.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
<?php endif; ?>

<?php if ($this->_tpl_vars['functions']): ?>
  <a name="sec-functions"></a>
  <h2>Functions</h2>
  <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "function.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
<?php endif; ?>

<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "footer.tpl", 'smarty_include_vars' => array('top3' => true)));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>