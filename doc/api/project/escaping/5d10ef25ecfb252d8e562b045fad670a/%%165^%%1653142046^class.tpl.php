<?php /* Smarty version 2.6.0, created on 2010-01-27 17:43:46
         compiled from class.tpl */ ?>
<?php require_once(SMARTY_DIR . 'core' . DIRECTORY_SEPARATOR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'replace', 'class.tpl', 84, false),)), $this); ?>
<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "header.tpl", 'smarty_include_vars' => array('eltype' => 'class','hasel' => true,'contents' => $this->_tpl_vars['classcontents'])));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>

<a name="sec-description"></a>
<h2><?php if ($this->_tpl_vars['is_interface']): ?>Interface<?php else: ?>Class<?php endif; ?> <?php echo $this->_tpl_vars['class_name']; ?>
</h2>

<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "docblock.tpl", 'smarty_include_vars' => array('type' => 'class','sdesc' => $this->_tpl_vars['sdesc'],'desc' => $this->_tpl_vars['desc'])));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
		
<?php if ($this->_tpl_vars['implements']): ?>
  <h2>Implements interfaces:</h2>
  <ul>
    <?php if (count($_from = (array)$this->_tpl_vars['implements'])):
    foreach ($_from as $this->_tpl_vars['int']):
?><li><?php echo $this->_tpl_vars['int']; ?>
</li><?php endforeach; unset($_from); endif; ?>
  </ul>
<?php endif; ?>

<?php if ($this->_tpl_vars['tutorial']): ?>
  <hr class="separator" />
  <div class="notes">Tutorial: <span class="tutorial"><?php echo $this->_tpl_vars['tutorial']; ?>
</div>
<?php endif; ?>

<?php if (is_array ( $this->_tpl_vars['class_tree']['classes'] ) && count ( $this->_tpl_vars['class_tree']['classes'] )): ?>
<pre><?php if (isset($this->_sections['tree'])) unset($this->_sections['tree']);
$this->_sections['tree']['name'] = 'tree';
$this->_sections['tree']['loop'] = is_array($_loop=$this->_tpl_vars['class_tree']['classes']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
$this->_sections['tree']['show'] = true;
$this->_sections['tree']['max'] = $this->_sections['tree']['loop'];
$this->_sections['tree']['step'] = 1;
$this->_sections['tree']['start'] = $this->_sections['tree']['step'] > 0 ? 0 : $this->_sections['tree']['loop']-1;
if ($this->_sections['tree']['show']) {
    $this->_sections['tree']['total'] = $this->_sections['tree']['loop'];
    if ($this->_sections['tree']['total'] == 0)
        $this->_sections['tree']['show'] = false;
} else
    $this->_sections['tree']['total'] = 0;
if ($this->_sections['tree']['show']):

            for ($this->_sections['tree']['index'] = $this->_sections['tree']['start'], $this->_sections['tree']['iteration'] = 1;
                 $this->_sections['tree']['iteration'] <= $this->_sections['tree']['total'];
                 $this->_sections['tree']['index'] += $this->_sections['tree']['step'], $this->_sections['tree']['iteration']++):
$this->_sections['tree']['rownum'] = $this->_sections['tree']['iteration'];
$this->_sections['tree']['index_prev'] = $this->_sections['tree']['index'] - $this->_sections['tree']['step'];
$this->_sections['tree']['index_next'] = $this->_sections['tree']['index'] + $this->_sections['tree']['step'];
$this->_sections['tree']['first']      = ($this->_sections['tree']['iteration'] == 1);
$this->_sections['tree']['last']       = ($this->_sections['tree']['iteration'] == $this->_sections['tree']['total']);
 echo $this->_tpl_vars['class_tree']['classes'][$this->_sections['tree']['index']];  echo $this->_tpl_vars['class_tree']['distance'][$this->_sections['tree']['index']];  endfor; endif; ?></pre>
<?php endif; ?>

<?php if ($this->_tpl_vars['conflicts']['conflict_type']): ?>
  <hr class="separator" />
  <div>
    <span class="warning">Conflicts with classes:</span>
    <br />
    <?php if (isset($this->_sections['me'])) unset($this->_sections['me']);
$this->_sections['me']['name'] = 'me';
$this->_sections['me']['loop'] = is_array($_loop=$this->_tpl_vars['conflicts']['conflicts']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
$this->_sections['me']['show'] = true;
$this->_sections['me']['max'] = $this->_sections['me']['loop'];
$this->_sections['me']['step'] = 1;
$this->_sections['me']['start'] = $this->_sections['me']['step'] > 0 ? 0 : $this->_sections['me']['loop']-1;
if ($this->_sections['me']['show']) {
    $this->_sections['me']['total'] = $this->_sections['me']['loop'];
    if ($this->_sections['me']['total'] == 0)
        $this->_sections['me']['show'] = false;
} else
    $this->_sections['me']['total'] = 0;
if ($this->_sections['me']['show']):

            for ($this->_sections['me']['index'] = $this->_sections['me']['start'], $this->_sections['me']['iteration'] = 1;
                 $this->_sections['me']['iteration'] <= $this->_sections['me']['total'];
                 $this->_sections['me']['index'] += $this->_sections['me']['step'], $this->_sections['me']['iteration']++):
$this->_sections['me']['rownum'] = $this->_sections['me']['iteration'];
$this->_sections['me']['index_prev'] = $this->_sections['me']['index'] - $this->_sections['me']['step'];
$this->_sections['me']['index_next'] = $this->_sections['me']['index'] + $this->_sections['me']['step'];
$this->_sections['me']['first']      = ($this->_sections['me']['iteration'] == 1);
$this->_sections['me']['last']       = ($this->_sections['me']['iteration'] == $this->_sections['me']['total']);
?>      <?php echo $this->_tpl_vars['conflicts']['conflicts'][$this->_sections['me']['index']]; ?>
<br />
    <?php endfor; endif; ?>  </div>
<?php endif; ?>

<?php if (count ( $this->_tpl_vars['tags'] ) > 0): ?>
  <strong>Author(s):</strong>
  <ul>
    <?php if (isset($this->_sections['tag'])) unset($this->_sections['tag']);
$this->_sections['tag']['name'] = 'tag';
$this->_sections['tag']['loop'] = is_array($_loop=$this->_tpl_vars['tags']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
$this->_sections['tag']['show'] = true;
$this->_sections['tag']['max'] = $this->_sections['tag']['loop'];
$this->_sections['tag']['step'] = 1;
$this->_sections['tag']['start'] = $this->_sections['tag']['step'] > 0 ? 0 : $this->_sections['tag']['loop']-1;
if ($this->_sections['tag']['show']) {
    $this->_sections['tag']['total'] = $this->_sections['tag']['loop'];
    if ($this->_sections['tag']['total'] == 0)
        $this->_sections['tag']['show'] = false;
} else
    $this->_sections['tag']['total'] = 0;
if ($this->_sections['tag']['show']):

            for ($this->_sections['tag']['index'] = $this->_sections['tag']['start'], $this->_sections['tag']['iteration'] = 1;
                 $this->_sections['tag']['iteration'] <= $this->_sections['tag']['total'];
                 $this->_sections['tag']['index'] += $this->_sections['tag']['step'], $this->_sections['tag']['iteration']++):
$this->_sections['tag']['rownum'] = $this->_sections['tag']['iteration'];
$this->_sections['tag']['index_prev'] = $this->_sections['tag']['index'] - $this->_sections['tag']['step'];
$this->_sections['tag']['index_next'] = $this->_sections['tag']['index'] + $this->_sections['tag']['step'];
$this->_sections['tag']['first']      = ($this->_sections['tag']['iteration'] == 1);
$this->_sections['tag']['last']       = ($this->_sections['tag']['iteration'] == $this->_sections['tag']['total']);
?>
      <?php if ($this->_tpl_vars['tags'][$this->_sections['tag']['index']]['keyword'] == 'author'): ?>
        <li><?php echo $this->_tpl_vars['tags'][$this->_sections['tag']['index']]['data']; ?>
</li>
      <?php endif; ?>
    <?php endfor; endif; ?>
  </ul>
<?php endif; ?>

<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "classtags.tpl", 'smarty_include_vars' => array('tags' => $this->_tpl_vars['tags'])));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>

<?php if ($this->_tpl_vars['children']): ?>
  <a name="sec-descendants"></a>
  <h2>Descendants</h2>
  <table class="detail">
    <thead>
      <tr>
        <th>Child Class</th>
        <th>Description</th>
      </tr>
    </thead>
    <tbody>
      <?php if (isset($this->_sections['kids'])) unset($this->_sections['kids']);
$this->_sections['kids']['name'] = 'kids';
$this->_sections['kids']['loop'] = is_array($_loop=$this->_tpl_vars['children']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
$this->_sections['kids']['show'] = true;
$this->_sections['kids']['max'] = $this->_sections['kids']['loop'];
$this->_sections['kids']['step'] = 1;
$this->_sections['kids']['start'] = $this->_sections['kids']['step'] > 0 ? 0 : $this->_sections['kids']['loop']-1;
if ($this->_sections['kids']['show']) {
    $this->_sections['kids']['total'] = $this->_sections['kids']['loop'];
    if ($this->_sections['kids']['total'] == 0)
        $this->_sections['kids']['show'] = false;
} else
    $this->_sections['kids']['total'] = 0;
if ($this->_sections['kids']['show']):

            for ($this->_sections['kids']['index'] = $this->_sections['kids']['start'], $this->_sections['kids']['iteration'] = 1;
                 $this->_sections['kids']['iteration'] <= $this->_sections['kids']['total'];
                 $this->_sections['kids']['index'] += $this->_sections['kids']['step'], $this->_sections['kids']['iteration']++):
$this->_sections['kids']['rownum'] = $this->_sections['kids']['iteration'];
$this->_sections['kids']['index_prev'] = $this->_sections['kids']['index'] - $this->_sections['kids']['step'];
$this->_sections['kids']['index_next'] = $this->_sections['kids']['index'] + $this->_sections['kids']['step'];
$this->_sections['kids']['first']      = ($this->_sections['kids']['iteration'] == 1);
$this->_sections['kids']['last']       = ($this->_sections['kids']['iteration'] == $this->_sections['kids']['total']);
?>
        <tr>
          <td><?php echo $this->_tpl_vars['children'][$this->_sections['kids']['index']]['link']; ?>
</td>          <td>
            <?php if ($this->_tpl_vars['children'][$this->_sections['kids']['index']]['sdesc']): ?>
              <?php echo $this->_tpl_vars['children'][$this->_sections['kids']['index']]['sdesc']; ?>

            <?php else: ?>
              <?php echo $this->_tpl_vars['children'][$this->_sections['kids']['index']]['desc']; ?>

            <?php endif; ?>
          </td>
        </tr>
      <?php endfor; endif; ?>
    </tbody>
  </table>
<?php endif; ?>

<?php if ($this->_tpl_vars['consts']): ?>
  <a name="sec-const-summary"></a>
  <h2>Constants</h2>
  <table class="summary">
    <?php if (isset($this->_sections['consts'])) unset($this->_sections['consts']);
$this->_sections['consts']['name'] = 'consts';
$this->_sections['consts']['loop'] = is_array($_loop=$this->_tpl_vars['consts']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
$this->_sections['consts']['show'] = true;
$this->_sections['consts']['max'] = $this->_sections['consts']['loop'];
$this->_sections['consts']['step'] = 1;
$this->_sections['consts']['start'] = $this->_sections['consts']['step'] > 0 ? 0 : $this->_sections['consts']['loop']-1;
if ($this->_sections['consts']['show']) {
    $this->_sections['consts']['total'] = $this->_sections['consts']['loop'];
    if ($this->_sections['consts']['total'] == 0)
        $this->_sections['consts']['show'] = false;
} else
    $this->_sections['consts']['total'] = 0;
if ($this->_sections['consts']['show']):

            for ($this->_sections['consts']['index'] = $this->_sections['consts']['start'], $this->_sections['consts']['iteration'] = 1;
                 $this->_sections['consts']['iteration'] <= $this->_sections['consts']['total'];
                 $this->_sections['consts']['index'] += $this->_sections['consts']['step'], $this->_sections['consts']['iteration']++):
$this->_sections['consts']['rownum'] = $this->_sections['consts']['iteration'];
$this->_sections['consts']['index_prev'] = $this->_sections['consts']['index'] - $this->_sections['consts']['step'];
$this->_sections['consts']['index_next'] = $this->_sections['consts']['index'] + $this->_sections['consts']['step'];
$this->_sections['consts']['first']      = ($this->_sections['consts']['iteration'] == 1);
$this->_sections['consts']['last']       = ($this->_sections['consts']['iteration'] == $this->_sections['consts']['total']);
?>
      <tr>
        <td class="right">
          <a name="const-<?php echo $this->_tpl_vars['consts'][$this->_sections['consts']['index']]['const_name']; ?>
" id="<?php echo $this->_tpl_vars['const'][$this->_sections['consts']['index']]['const_name']; ?>
"></a>
          <code>
            <a href="#const-<?php echo $this->_tpl_vars['consts'][$this->_sections['consts']['index']]['const_name']; ?>
" title="details" class="const-name-summary"><?php echo $this->_tpl_vars['consts'][$this->_sections['consts']['index']]['const_name']; ?>
</a>
             = <?php echo ((is_array($_tmp=$this->_tpl_vars['consts'][$this->_sections['consts']['index']]['const_value'])) ? $this->_run_mod_handler('replace', true, $_tmp, "\n", "<br />") : smarty_modifier_replace($_tmp, "\n", "<br />")); ?>

          </code>
        </td>
        <td>
          <?php if ($this->_tpl_vars['consts'][$this->_sections['consts']['index']]['sdesc']):  echo $this->_tpl_vars['consts'][$this->_sections['consts']['index']]['sdesc'];  endif; ?>
          <?php if ($this->_tpl_vars['consts'][$this->_sections['consts']['index']]['desc']):  echo $this->_tpl_vars['consts'][$this->_sections['consts']['index']]['desc'];  endif; ?>
        </td>
      </tr>
    <?php endfor; endif; ?>
  </table>
<?php endif; ?>

<?php if ($this->_tpl_vars['iconsts']): ?>
  <a name="sec-inherited-consts"></a>
  <h2>Inherited Constants</h2>
  <table class="summary">
    <?php if (isset($this->_sections['iconsts'])) unset($this->_sections['iconsts']);
$this->_sections['iconsts']['name'] = 'iconsts';
$this->_sections['iconsts']['loop'] = is_array($_loop=$this->_tpl_vars['iconsts']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
$this->_sections['iconsts']['show'] = true;
$this->_sections['iconsts']['max'] = $this->_sections['iconsts']['loop'];
$this->_sections['iconsts']['step'] = 1;
$this->_sections['iconsts']['start'] = $this->_sections['iconsts']['step'] > 0 ? 0 : $this->_sections['iconsts']['loop']-1;
if ($this->_sections['iconsts']['show']) {
    $this->_sections['iconsts']['total'] = $this->_sections['iconsts']['loop'];
    if ($this->_sections['iconsts']['total'] == 0)
        $this->_sections['iconsts']['show'] = false;
} else
    $this->_sections['iconsts']['total'] = 0;
if ($this->_sections['iconsts']['show']):

            for ($this->_sections['iconsts']['index'] = $this->_sections['iconsts']['start'], $this->_sections['iconsts']['iteration'] = 1;
                 $this->_sections['iconsts']['iteration'] <= $this->_sections['iconsts']['total'];
                 $this->_sections['iconsts']['index'] += $this->_sections['iconsts']['step'], $this->_sections['iconsts']['iteration']++):
$this->_sections['iconsts']['rownum'] = $this->_sections['iconsts']['iteration'];
$this->_sections['iconsts']['index_prev'] = $this->_sections['iconsts']['index'] - $this->_sections['iconsts']['step'];
$this->_sections['iconsts']['index_next'] = $this->_sections['iconsts']['index'] + $this->_sections['iconsts']['step'];
$this->_sections['iconsts']['first']      = ($this->_sections['iconsts']['iteration'] == 1);
$this->_sections['iconsts']['last']       = ($this->_sections['iconsts']['iteration'] == $this->_sections['iconsts']['total']);
?>
      <?php if ($this->_tpl_vars['iconsts'][$this->_sections['iconsts']['index']] && is_array ( $this->_tpl_vars['iconsts'][$this->_sections['iconsts']['index']] ) && count ( $this->_tpl_vars['iconsts'][$this->_sections['iconsts']['index']] )): ?>
        <thead>
          <tr>
            <th colspan="2">
              From <span class="classname"><?php echo $this->_tpl_vars['iconsts'][$this->_sections['iconsts']['index']]['parent_class']; ?>
</span>:
            </th>
          </tr>
        </thead>
        <tbody>
          <?php if (isset($this->_sections['iconsts2'])) unset($this->_sections['iconsts2']);
$this->_sections['iconsts2']['name'] = 'iconsts2';
$this->_sections['iconsts2']['loop'] = is_array($_loop=$this->_tpl_vars['iconsts'][$this->_sections['iconsts']['index']]['iconsts']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
$this->_sections['iconsts2']['show'] = true;
$this->_sections['iconsts2']['max'] = $this->_sections['iconsts2']['loop'];
$this->_sections['iconsts2']['step'] = 1;
$this->_sections['iconsts2']['start'] = $this->_sections['iconsts2']['step'] > 0 ? 0 : $this->_sections['iconsts2']['loop']-1;
if ($this->_sections['iconsts2']['show']) {
    $this->_sections['iconsts2']['total'] = $this->_sections['iconsts2']['loop'];
    if ($this->_sections['iconsts2']['total'] == 0)
        $this->_sections['iconsts2']['show'] = false;
} else
    $this->_sections['iconsts2']['total'] = 0;
if ($this->_sections['iconsts2']['show']):

            for ($this->_sections['iconsts2']['index'] = $this->_sections['iconsts2']['start'], $this->_sections['iconsts2']['iteration'] = 1;
                 $this->_sections['iconsts2']['iteration'] <= $this->_sections['iconsts2']['total'];
                 $this->_sections['iconsts2']['index'] += $this->_sections['iconsts2']['step'], $this->_sections['iconsts2']['iteration']++):
$this->_sections['iconsts2']['rownum'] = $this->_sections['iconsts2']['iteration'];
$this->_sections['iconsts2']['index_prev'] = $this->_sections['iconsts2']['index'] - $this->_sections['iconsts2']['step'];
$this->_sections['iconsts2']['index_next'] = $this->_sections['iconsts2']['index'] + $this->_sections['iconsts2']['step'];
$this->_sections['iconsts2']['first']      = ($this->_sections['iconsts2']['iteration'] == 1);
$this->_sections['iconsts2']['last']       = ($this->_sections['iconsts2']['iteration'] == $this->_sections['iconsts2']['total']);
?>
            <tr>
              <td class="right">
                <code><?php echo $this->_tpl_vars['iconsts'][$this->_sections['iconsts']['index']]['iconsts'][$this->_sections['iconsts2']['index']]['link']; ?>
</code>
              </td>
              <td>
                <?php if ($this->_tpl_vars['iconsts'][$this->_sections['iconsts']['index']]['iconsts'][$this->_sections['iconsts2']['index']]['sdesc']): ?>&nbsp;&nbsp;&nbsp;<?php echo $this->_tpl_vars['iconsts'][$this->_sections['iconsts']['index']]['iconsts'][$this->_sections['iconsts2']['index']]['sdesc'];  endif; ?>
              </td>
            </tr>
          <?php endfor; endif; ?>
        </tbody>
      <?php endif; ?>
    <?php endfor; endif; ?>
  </table>
<?php endif; ?>

<?php if ($this->_tpl_vars['prop_tags']): ?>
  <a name="sec-prop-summary"></a>
  <h2>Properties</h2>
  <table class="summary">
    <?php if (isset($this->_sections['props'])) unset($this->_sections['props']);
$this->_sections['props']['name'] = 'props';
$this->_sections['props']['loop'] = is_array($_loop=$this->_tpl_vars['prop_tags']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
$this->_sections['props']['show'] = true;
$this->_sections['props']['max'] = $this->_sections['props']['loop'];
$this->_sections['props']['step'] = 1;
$this->_sections['props']['start'] = $this->_sections['props']['step'] > 0 ? 0 : $this->_sections['props']['loop']-1;
if ($this->_sections['props']['show']) {
    $this->_sections['props']['total'] = $this->_sections['props']['loop'];
    if ($this->_sections['props']['total'] == 0)
        $this->_sections['props']['show'] = false;
} else
    $this->_sections['props']['total'] = 0;
if ($this->_sections['props']['show']):

            for ($this->_sections['props']['index'] = $this->_sections['props']['start'], $this->_sections['props']['iteration'] = 1;
                 $this->_sections['props']['iteration'] <= $this->_sections['props']['total'];
                 $this->_sections['props']['index'] += $this->_sections['props']['step'], $this->_sections['props']['iteration']++):
$this->_sections['props']['rownum'] = $this->_sections['props']['iteration'];
$this->_sections['props']['index_prev'] = $this->_sections['props']['index'] - $this->_sections['props']['step'];
$this->_sections['props']['index_next'] = $this->_sections['props']['index'] + $this->_sections['props']['step'];
$this->_sections['props']['first']      = ($this->_sections['props']['iteration'] == 1);
$this->_sections['props']['last']       = ($this->_sections['props']['iteration'] == $this->_sections['props']['total']);
?>
      <tr>
        <td class="right">
          <a name="prop<?php echo $this->_tpl_vars['prop_tags'][$this->_sections['props']['index']]['prop_name']; ?>
" id="<?php echo $this->_tpl_vars['prop_tags'][$this->_sections['props']['index']]['prop_name']; ?>
"></a>
          <a name="prop-<?php echo $this->_tpl_vars['prop_tags'][$this->_sections['props']['index']]['prop_name']; ?>
" id="<?php echo $this->_tpl_vars['prop_tags'][$this->_sections['props']['index']]['prop_name']; ?>
"></a>
          <?php if ($this->_tpl_vars['prop_tags'][$this->_sections['props']['index']]['prop_type']): ?><em><?php echo $this->_tpl_vars['prop_tags'][$this->_sections['props']['index']]['prop_type']; ?>
</em><?php endif; ?>
        </td>
        <td class="right">
          <em><?php echo $this->_tpl_vars['prop_tags'][$this->_sections['props']['index']]['access']; ?>
</em>
        </td>
        <td>
          <code>
            <a href="#prop-<?php echo $this->_tpl_vars['prop_tags'][$this->_sections['props']['index']]['prop_name']; ?>
" title="details" class="var-name-summary"><?php echo $this->_tpl_vars['prop_tags'][$this->_sections['props']['index']]['prop_name']; ?>
</a>
            <?php if ($this->_tpl_vars['prop_tags'][$this->_sections['props']['index']]['def_value']): ?>&nbsp;=&nbsp;<?php echo $this->_tpl_vars['prop_tags'][$this->_sections['props']['index']]['def_value'];  endif; ?>
          </code>
          <?php if ($this->_tpl_vars['prop_tags'][$this->_sections['props']['index']]['sdesc']): ?><br /><div style="margin-left: 20px"><?php echo $this->_tpl_vars['prop_tags'][$this->_sections['props']['index']]['sdesc']; ?>
</div><?php endif; ?>
        </td>
      </tr>
    <?php endfor; endif; ?>
  </table>
<?php endif; ?>

<?php if ($this->_tpl_vars['vars']): ?>
  <a name="sec-var-summary"></a>
  <h2>Member Variables</h2>
  <table class="summary">
    <?php if (isset($this->_sections['vars'])) unset($this->_sections['vars']);
$this->_sections['vars']['name'] = 'vars';
$this->_sections['vars']['loop'] = is_array($_loop=$this->_tpl_vars['vars']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
$this->_sections['vars']['show'] = true;
$this->_sections['vars']['max'] = $this->_sections['vars']['loop'];
$this->_sections['vars']['step'] = 1;
$this->_sections['vars']['start'] = $this->_sections['vars']['step'] > 0 ? 0 : $this->_sections['vars']['loop']-1;
if ($this->_sections['vars']['show']) {
    $this->_sections['vars']['total'] = $this->_sections['vars']['loop'];
    if ($this->_sections['vars']['total'] == 0)
        $this->_sections['vars']['show'] = false;
} else
    $this->_sections['vars']['total'] = 0;
if ($this->_sections['vars']['show']):

            for ($this->_sections['vars']['index'] = $this->_sections['vars']['start'], $this->_sections['vars']['iteration'] = 1;
                 $this->_sections['vars']['iteration'] <= $this->_sections['vars']['total'];
                 $this->_sections['vars']['index'] += $this->_sections['vars']['step'], $this->_sections['vars']['iteration']++):
$this->_sections['vars']['rownum'] = $this->_sections['vars']['iteration'];
$this->_sections['vars']['index_prev'] = $this->_sections['vars']['index'] - $this->_sections['vars']['step'];
$this->_sections['vars']['index_next'] = $this->_sections['vars']['index'] + $this->_sections['vars']['step'];
$this->_sections['vars']['first']      = ($this->_sections['vars']['iteration'] == 1);
$this->_sections['vars']['last']       = ($this->_sections['vars']['iteration'] == $this->_sections['vars']['total']);
?>
      <?php if ($this->_tpl_vars['vars'][$this->_sections['vars']['index']]['static']): ?>
        <tr>
          <td class="right">
            <?php if ($this->_tpl_vars['vars'][$this->_sections['vars']['index']]['access']): ?><em><?php echo $this->_tpl_vars['vars'][$this->_sections['vars']['index']]['access']; ?>
</em><?php endif; ?>
            static
            <?php if ($this->_tpl_vars['vars'][$this->_sections['vars']['index']]['var_type']): ?><em><?php echo $this->_tpl_vars['vars'][$this->_sections['vars']['index']]['var_type']; ?>
</em><?php endif; ?>
          </td>
          <td>
            <code>
              <?php echo $this->_tpl_vars['vars'][$this->_sections['vars']['index']]['var_name']; ?>

              <?php if ($this->_tpl_vars['vars'][$this->_sections['vars']['index']]['var_default']): ?> = <span class="var-default"><?php echo ((is_array($_tmp=$this->_tpl_vars['vars'][$this->_sections['vars']['index']]['var_default'])) ? $this->_run_mod_handler('replace', true, $_tmp, "\n", "<br />") : smarty_modifier_replace($_tmp, "\n", "<br />")); ?>
</span><?php endif; ?>
            </code>
            <?php if ($this->_tpl_vars['vars'][$this->_sections['vars']['index']]['sdesc']): ?><br /><div style="margin-left: 20px"><?php echo $this->_tpl_vars['vars'][$this->_sections['vars']['index']]['sdesc']; ?>
</div><?php endif; ?>
            <?php if ($this->_tpl_vars['vars'][$this->_sections['vars']['index']]['desc']): ?><br /><div style="margin-left: 20px"><?php echo $this->_tpl_vars['vars'][$this->_sections['vars']['index']]['desc']; ?>
</div><?php endif; ?>
          </td>
        </tr>
      <?php endif; ?>
    <?php endfor; endif; ?>
    <?php if (isset($this->_sections['vars'])) unset($this->_sections['vars']);
$this->_sections['vars']['name'] = 'vars';
$this->_sections['vars']['loop'] = is_array($_loop=$this->_tpl_vars['vars']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
$this->_sections['vars']['show'] = true;
$this->_sections['vars']['max'] = $this->_sections['vars']['loop'];
$this->_sections['vars']['step'] = 1;
$this->_sections['vars']['start'] = $this->_sections['vars']['step'] > 0 ? 0 : $this->_sections['vars']['loop']-1;
if ($this->_sections['vars']['show']) {
    $this->_sections['vars']['total'] = $this->_sections['vars']['loop'];
    if ($this->_sections['vars']['total'] == 0)
        $this->_sections['vars']['show'] = false;
} else
    $this->_sections['vars']['total'] = 0;
if ($this->_sections['vars']['show']):

            for ($this->_sections['vars']['index'] = $this->_sections['vars']['start'], $this->_sections['vars']['iteration'] = 1;
                 $this->_sections['vars']['iteration'] <= $this->_sections['vars']['total'];
                 $this->_sections['vars']['index'] += $this->_sections['vars']['step'], $this->_sections['vars']['iteration']++):
$this->_sections['vars']['rownum'] = $this->_sections['vars']['iteration'];
$this->_sections['vars']['index_prev'] = $this->_sections['vars']['index'] - $this->_sections['vars']['step'];
$this->_sections['vars']['index_next'] = $this->_sections['vars']['index'] + $this->_sections['vars']['step'];
$this->_sections['vars']['first']      = ($this->_sections['vars']['iteration'] == 1);
$this->_sections['vars']['last']       = ($this->_sections['vars']['iteration'] == $this->_sections['vars']['total']);
?>
      <?php if (! $this->_tpl_vars['vars'][$this->_sections['vars']['index']]['static']): ?>
        <tr>
          <td class="right">
            <?php if ($this->_tpl_vars['vars'][$this->_sections['vars']['index']]['access']): ?><em><?php echo $this->_tpl_vars['vars'][$this->_sections['vars']['index']]['access']; ?>
</em><?php endif; ?>
            <?php if ($this->_tpl_vars['vars'][$this->_sections['vars']['index']]['var_type']): ?><em><?php echo $this->_tpl_vars['vars'][$this->_sections['vars']['index']]['var_type']; ?>
</em><?php endif; ?>
          </td>
          <td>
            <code>
              <?php echo $this->_tpl_vars['vars'][$this->_sections['vars']['index']]['var_name']; ?>

              <?php if ($this->_tpl_vars['vars'][$this->_sections['vars']['index']]['var_default']): ?> = <span class="var-default"><?php echo ((is_array($_tmp=$this->_tpl_vars['vars'][$this->_sections['vars']['index']]['var_default'])) ? $this->_run_mod_handler('replace', true, $_tmp, "\n", "<br />") : smarty_modifier_replace($_tmp, "\n", "<br />")); ?>
</span><?php endif; ?>
            </code>
            <?php if ($this->_tpl_vars['vars'][$this->_sections['vars']['index']]['sdesc']): ?><br /><div style="margin-left: 20px"><?php echo $this->_tpl_vars['vars'][$this->_sections['vars']['index']]['sdesc']; ?>
</div><?php endif; ?>
            <?php if ($this->_tpl_vars['vars'][$this->_sections['vars']['index']]['desc']): ?><br /><div style="margin-left: 20px"><?php echo $this->_tpl_vars['vars'][$this->_sections['vars']['index']]['desc']; ?>
</div><?php endif; ?>
          </td>
        </tr>
      <?php endif; ?>
    <?php endfor; endif; ?>
  </table>
<?php endif; ?>

<?php if ($this->_tpl_vars['ivars']): ?>
  <h2>Inherited Member Variables</h2>
  <table class="summary">
    <?php if (isset($this->_sections['ivars'])) unset($this->_sections['ivars']);
$this->_sections['ivars']['name'] = 'ivars';
$this->_sections['ivars']['loop'] = is_array($_loop=$this->_tpl_vars['ivars']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
$this->_sections['ivars']['show'] = true;
$this->_sections['ivars']['max'] = $this->_sections['ivars']['loop'];
$this->_sections['ivars']['step'] = 1;
$this->_sections['ivars']['start'] = $this->_sections['ivars']['step'] > 0 ? 0 : $this->_sections['ivars']['loop']-1;
if ($this->_sections['ivars']['show']) {
    $this->_sections['ivars']['total'] = $this->_sections['ivars']['loop'];
    if ($this->_sections['ivars']['total'] == 0)
        $this->_sections['ivars']['show'] = false;
} else
    $this->_sections['ivars']['total'] = 0;
if ($this->_sections['ivars']['show']):

            for ($this->_sections['ivars']['index'] = $this->_sections['ivars']['start'], $this->_sections['ivars']['iteration'] = 1;
                 $this->_sections['ivars']['iteration'] <= $this->_sections['ivars']['total'];
                 $this->_sections['ivars']['index'] += $this->_sections['ivars']['step'], $this->_sections['ivars']['iteration']++):
$this->_sections['ivars']['rownum'] = $this->_sections['ivars']['iteration'];
$this->_sections['ivars']['index_prev'] = $this->_sections['ivars']['index'] - $this->_sections['ivars']['step'];
$this->_sections['ivars']['index_next'] = $this->_sections['ivars']['index'] + $this->_sections['ivars']['step'];
$this->_sections['ivars']['first']      = ($this->_sections['ivars']['iteration'] == 1);
$this->_sections['ivars']['last']       = ($this->_sections['ivars']['iteration'] == $this->_sections['ivars']['total']);
?>
      <thead>
        <tr>
          <th colspan="2">
	        From <span class="classname"><?php echo $this->_tpl_vars['ivars'][$this->_sections['ivars']['index']]['parent_class']; ?>
</span>         
          </th>
        </tr>
      </thead>
      <tbody>
        <?php if (isset($this->_sections['ivars2'])) unset($this->_sections['ivars2']);
$this->_sections['ivars2']['name'] = 'ivars2';
$this->_sections['ivars2']['loop'] = is_array($_loop=$this->_tpl_vars['ivars'][$this->_sections['ivars']['index']]['ivars']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
$this->_sections['ivars2']['show'] = true;
$this->_sections['ivars2']['max'] = $this->_sections['ivars2']['loop'];
$this->_sections['ivars2']['step'] = 1;
$this->_sections['ivars2']['start'] = $this->_sections['ivars2']['step'] > 0 ? 0 : $this->_sections['ivars2']['loop']-1;
if ($this->_sections['ivars2']['show']) {
    $this->_sections['ivars2']['total'] = $this->_sections['ivars2']['loop'];
    if ($this->_sections['ivars2']['total'] == 0)
        $this->_sections['ivars2']['show'] = false;
} else
    $this->_sections['ivars2']['total'] = 0;
if ($this->_sections['ivars2']['show']):

            for ($this->_sections['ivars2']['index'] = $this->_sections['ivars2']['start'], $this->_sections['ivars2']['iteration'] = 1;
                 $this->_sections['ivars2']['iteration'] <= $this->_sections['ivars2']['total'];
                 $this->_sections['ivars2']['index'] += $this->_sections['ivars2']['step'], $this->_sections['ivars2']['iteration']++):
$this->_sections['ivars2']['rownum'] = $this->_sections['ivars2']['iteration'];
$this->_sections['ivars2']['index_prev'] = $this->_sections['ivars2']['index'] - $this->_sections['ivars2']['step'];
$this->_sections['ivars2']['index_next'] = $this->_sections['ivars2']['index'] + $this->_sections['ivars2']['step'];
$this->_sections['ivars2']['first']      = ($this->_sections['ivars2']['iteration'] == 1);
$this->_sections['ivars2']['last']       = ($this->_sections['ivars2']['iteration'] == $this->_sections['ivars2']['total']);
?>
          <tr>
            <td class="right">
              <?php if ($this->_tpl_vars['ivars'][$this->_sections['ivars']['index']]['ivars'][$this->_sections['ivars2']['index']]['access']): ?><em><?php echo $this->_tpl_vars['ivars'][$this->_sections['ivars']['index']]['ivars'][$this->_sections['ivars2']['index']]['access']; ?>
</em><?php endif; ?>
              <?php if ($this->_tpl_vars['ivars'][$this->_sections['ivars']['index']]['ivars'][$this->_sections['ivars2']['index']]['var_type']): ?><em><?php echo $this->_tpl_vars['ivars'][$this->_sections['ivars']['index']]['ivars'][$this->_sections['ivars2']['index']]['var_type']; ?>
</em><?php endif; ?>
            </td>
            <td>
              <code><?php echo $this->_tpl_vars['ivars'][$this->_sections['ivars']['index']]['ivars'][$this->_sections['ivars2']['index']]['link']; ?>
</code>
              <?php echo $this->_tpl_vars['ivars'][$this->_sections['ivars']['index']]['ivars'][$this->_sections['ivars2']['index']]['ivars_sdesc']; ?>

            </td>
          </tr>
        <?php endfor; endif; ?>
      </tbody>
    <?php endfor; endif; ?>
  </table>
<?php endif; ?>

<?php if ($this->_tpl_vars['methods']): ?>
  <a name="sec-method-summary"></a>
  <h2>Method Summary</h2>
  <table class="summary">
    <?php if (isset($this->_sections['methods'])) unset($this->_sections['methods']);
$this->_sections['methods']['name'] = 'methods';
$this->_sections['methods']['loop'] = is_array($_loop=$this->_tpl_vars['methods']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
$this->_sections['methods']['show'] = true;
$this->_sections['methods']['max'] = $this->_sections['methods']['loop'];
$this->_sections['methods']['step'] = 1;
$this->_sections['methods']['start'] = $this->_sections['methods']['step'] > 0 ? 0 : $this->_sections['methods']['loop']-1;
if ($this->_sections['methods']['show']) {
    $this->_sections['methods']['total'] = $this->_sections['methods']['loop'];
    if ($this->_sections['methods']['total'] == 0)
        $this->_sections['methods']['show'] = false;
} else
    $this->_sections['methods']['total'] = 0;
if ($this->_sections['methods']['show']):

            for ($this->_sections['methods']['index'] = $this->_sections['methods']['start'], $this->_sections['methods']['iteration'] = 1;
                 $this->_sections['methods']['iteration'] <= $this->_sections['methods']['total'];
                 $this->_sections['methods']['index'] += $this->_sections['methods']['step'], $this->_sections['methods']['iteration']++):
$this->_sections['methods']['rownum'] = $this->_sections['methods']['iteration'];
$this->_sections['methods']['index_prev'] = $this->_sections['methods']['index'] - $this->_sections['methods']['step'];
$this->_sections['methods']['index_next'] = $this->_sections['methods']['index'] + $this->_sections['methods']['step'];
$this->_sections['methods']['first']      = ($this->_sections['methods']['iteration'] == 1);
$this->_sections['methods']['last']       = ($this->_sections['methods']['iteration'] == $this->_sections['methods']['total']);
?>
      <?php if ($this->_tpl_vars['methods'][$this->_sections['methods']['index']]['static']): ?>
        <tr>
          <td class="right">
            <?php if ($this->_tpl_vars['methods'][$this->_sections['methods']['index']]['access']): ?><em><?php echo $this->_tpl_vars['methods'][$this->_sections['methods']['index']]['access']; ?>
</em><?php endif; ?>
            static
            <?php if ($this->_tpl_vars['methods'][$this->_sections['methods']['index']]['abstract']): ?>abstract<?php endif; ?>
            <?php if ($this->_tpl_vars['methods'][$this->_sections['methods']['index']]['function_return']): ?><em><?php echo $this->_tpl_vars['methods'][$this->_sections['methods']['index']]['function_return']; ?>
</em><?php endif; ?>
          </td>
          <td>
            <code>
              <a href="#<?php echo $this->_tpl_vars['methods'][$this->_sections['methods']['index']]['function_name']; ?>
"><b><?php if ($this->_tpl_vars['methods'][$this->_sections['methods']['index']]['ifunction_call']['returnsref']): ?>&amp;<?php endif;  echo $this->_tpl_vars['methods'][$this->_sections['methods']['index']]['function_name']; ?>
</b></a>(
              <?php if (count ( $this->_tpl_vars['methods'][$this->_sections['methods']['index']]['ifunction_call']['params'] )): ?>
                <?php if (isset($this->_sections['params'])) unset($this->_sections['params']);
$this->_sections['params']['name'] = 'params';
$this->_sections['params']['loop'] = is_array($_loop=$this->_tpl_vars['methods'][$this->_sections['methods']['index']]['ifunction_call']['params']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
$this->_sections['params']['show'] = true;
$this->_sections['params']['max'] = $this->_sections['params']['loop'];
$this->_sections['params']['step'] = 1;
$this->_sections['params']['start'] = $this->_sections['params']['step'] > 0 ? 0 : $this->_sections['params']['loop']-1;
if ($this->_sections['params']['show']) {
    $this->_sections['params']['total'] = $this->_sections['params']['loop'];
    if ($this->_sections['params']['total'] == 0)
        $this->_sections['params']['show'] = false;
} else
    $this->_sections['params']['total'] = 0;
if ($this->_sections['params']['show']):

            for ($this->_sections['params']['index'] = $this->_sections['params']['start'], $this->_sections['params']['iteration'] = 1;
                 $this->_sections['params']['iteration'] <= $this->_sections['params']['total'];
                 $this->_sections['params']['index'] += $this->_sections['params']['step'], $this->_sections['params']['iteration']++):
$this->_sections['params']['rownum'] = $this->_sections['params']['iteration'];
$this->_sections['params']['index_prev'] = $this->_sections['params']['index'] - $this->_sections['params']['step'];
$this->_sections['params']['index_next'] = $this->_sections['params']['index'] + $this->_sections['params']['step'];
$this->_sections['params']['first']      = ($this->_sections['params']['iteration'] == 1);
$this->_sections['params']['last']       = ($this->_sections['params']['iteration'] == $this->_sections['params']['total']);
?>
                  <?php if ($this->_sections['params']['iteration'] != 1): ?>, <?php endif; ?>
                  <?php if ($this->_tpl_vars['methods'][$this->_sections['methods']['index']]['ifunction_call']['params'][$this->_sections['params']['index']]['default'] != ''): ?>[<?php endif; ?>
                  <?php echo $this->_tpl_vars['methods'][$this->_sections['methods']['index']]['ifunction_call']['params'][$this->_sections['params']['index']]['name']; ?>

                  <?php if ($this->_tpl_vars['methods'][$this->_sections['methods']['index']]['ifunction_call']['params'][$this->_sections['params']['index']]['default'] != ''): ?> = <?php echo $this->_tpl_vars['methods'][$this->_sections['methods']['index']]['ifunction_call']['params'][$this->_sections['params']['index']]['default']; ?>
]<?php endif; ?>
                <?php endfor; endif; ?>
              <?php endif; ?> )
            </code>
            <?php if ($this->_tpl_vars['methods'][$this->_sections['methods']['index']]['sdesc']): ?><br /><div style="margin-left: 20px"><?php echo $this->_tpl_vars['methods'][$this->_sections['methods']['index']]['sdesc']; ?>
</div><?php endif; ?>
          </td>
        </tr>
      <?php endif; ?>
    <?php endfor; endif; ?>
    <?php if (isset($this->_sections['methods'])) unset($this->_sections['methods']);
$this->_sections['methods']['name'] = 'methods';
$this->_sections['methods']['loop'] = is_array($_loop=$this->_tpl_vars['methods']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
$this->_sections['methods']['show'] = true;
$this->_sections['methods']['max'] = $this->_sections['methods']['loop'];
$this->_sections['methods']['step'] = 1;
$this->_sections['methods']['start'] = $this->_sections['methods']['step'] > 0 ? 0 : $this->_sections['methods']['loop']-1;
if ($this->_sections['methods']['show']) {
    $this->_sections['methods']['total'] = $this->_sections['methods']['loop'];
    if ($this->_sections['methods']['total'] == 0)
        $this->_sections['methods']['show'] = false;
} else
    $this->_sections['methods']['total'] = 0;
if ($this->_sections['methods']['show']):

            for ($this->_sections['methods']['index'] = $this->_sections['methods']['start'], $this->_sections['methods']['iteration'] = 1;
                 $this->_sections['methods']['iteration'] <= $this->_sections['methods']['total'];
                 $this->_sections['methods']['index'] += $this->_sections['methods']['step'], $this->_sections['methods']['iteration']++):
$this->_sections['methods']['rownum'] = $this->_sections['methods']['iteration'];
$this->_sections['methods']['index_prev'] = $this->_sections['methods']['index'] - $this->_sections['methods']['step'];
$this->_sections['methods']['index_next'] = $this->_sections['methods']['index'] + $this->_sections['methods']['step'];
$this->_sections['methods']['first']      = ($this->_sections['methods']['iteration'] == 1);
$this->_sections['methods']['last']       = ($this->_sections['methods']['iteration'] == $this->_sections['methods']['total']);
?>
      <?php if (! $this->_tpl_vars['methods'][$this->_sections['methods']['index']]['static']): ?>
        <tr>
          <td class="right">
            <?php if ($this->_tpl_vars['methods'][$this->_sections['methods']['index']]['access']): ?><em><?php echo $this->_tpl_vars['methods'][$this->_sections['methods']['index']]['access']; ?>
</em><?php endif; ?>
            <?php if ($this->_tpl_vars['methods'][$this->_sections['methods']['index']]['abstract']): ?>abstract<?php endif; ?>
            <?php if ($this->_tpl_vars['methods'][$this->_sections['methods']['index']]['function_return']): ?><em><?php echo $this->_tpl_vars['methods'][$this->_sections['methods']['index']]['function_return']; ?>
</em><?php endif; ?>
          </td>
          <td>
            <code>
              <a href="#<?php echo $this->_tpl_vars['methods'][$this->_sections['methods']['index']]['function_name']; ?>
"><b><?php if ($this->_tpl_vars['methods'][$this->_sections['methods']['index']]['ifunction_call']['returnsref']): ?>&amp;<?php endif;  echo $this->_tpl_vars['methods'][$this->_sections['methods']['index']]['function_name']; ?>
</b></a>(
              <?php if (count ( $this->_tpl_vars['methods'][$this->_sections['methods']['index']]['ifunction_call']['params'] )): ?>
                <?php if (isset($this->_sections['params'])) unset($this->_sections['params']);
$this->_sections['params']['name'] = 'params';
$this->_sections['params']['loop'] = is_array($_loop=$this->_tpl_vars['methods'][$this->_sections['methods']['index']]['ifunction_call']['params']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
$this->_sections['params']['show'] = true;
$this->_sections['params']['max'] = $this->_sections['params']['loop'];
$this->_sections['params']['step'] = 1;
$this->_sections['params']['start'] = $this->_sections['params']['step'] > 0 ? 0 : $this->_sections['params']['loop']-1;
if ($this->_sections['params']['show']) {
    $this->_sections['params']['total'] = $this->_sections['params']['loop'];
    if ($this->_sections['params']['total'] == 0)
        $this->_sections['params']['show'] = false;
} else
    $this->_sections['params']['total'] = 0;
if ($this->_sections['params']['show']):

            for ($this->_sections['params']['index'] = $this->_sections['params']['start'], $this->_sections['params']['iteration'] = 1;
                 $this->_sections['params']['iteration'] <= $this->_sections['params']['total'];
                 $this->_sections['params']['index'] += $this->_sections['params']['step'], $this->_sections['params']['iteration']++):
$this->_sections['params']['rownum'] = $this->_sections['params']['iteration'];
$this->_sections['params']['index_prev'] = $this->_sections['params']['index'] - $this->_sections['params']['step'];
$this->_sections['params']['index_next'] = $this->_sections['params']['index'] + $this->_sections['params']['step'];
$this->_sections['params']['first']      = ($this->_sections['params']['iteration'] == 1);
$this->_sections['params']['last']       = ($this->_sections['params']['iteration'] == $this->_sections['params']['total']);
?>
                  <?php if ($this->_sections['params']['iteration'] != 1): ?>, <?php endif; ?>
                  <?php if ($this->_tpl_vars['methods'][$this->_sections['methods']['index']]['ifunction_call']['params'][$this->_sections['params']['index']]['default'] != ''): ?>[<?php endif; ?>
                  <?php echo $this->_tpl_vars['methods'][$this->_sections['methods']['index']]['ifunction_call']['params'][$this->_sections['params']['index']]['name']; ?>

                  <?php if ($this->_tpl_vars['methods'][$this->_sections['methods']['index']]['ifunction_call']['params'][$this->_sections['params']['index']]['default'] != ''): ?> = <?php echo $this->_tpl_vars['methods'][$this->_sections['methods']['index']]['ifunction_call']['params'][$this->_sections['params']['index']]['default']; ?>
]<?php endif; ?>
                <?php endfor; endif; ?>
              <?php endif; ?> )
            </code>
            <?php if ($this->_tpl_vars['methods'][$this->_sections['methods']['index']]['sdesc']): ?><br /><div style="margin-left: 20px"><?php echo $this->_tpl_vars['methods'][$this->_sections['methods']['index']]['sdesc']; ?>
</div><?php endif; ?>
          </td>
        </tr>
      <?php endif; ?>
    <?php endfor; endif; ?>
  </table>
<?php endif; ?>

<?php if ($this->_tpl_vars['imethods']): ?>
  <h2>Inherited Methods</h2>
  <table class="summary">
    <?php if (isset($this->_sections['imethods'])) unset($this->_sections['imethods']);
$this->_sections['imethods']['name'] = 'imethods';
$this->_sections['imethods']['loop'] = is_array($_loop=$this->_tpl_vars['imethods']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
$this->_sections['imethods']['show'] = true;
$this->_sections['imethods']['max'] = $this->_sections['imethods']['loop'];
$this->_sections['imethods']['step'] = 1;
$this->_sections['imethods']['start'] = $this->_sections['imethods']['step'] > 0 ? 0 : $this->_sections['imethods']['loop']-1;
if ($this->_sections['imethods']['show']) {
    $this->_sections['imethods']['total'] = $this->_sections['imethods']['loop'];
    if ($this->_sections['imethods']['total'] == 0)
        $this->_sections['imethods']['show'] = false;
} else
    $this->_sections['imethods']['total'] = 0;
if ($this->_sections['imethods']['show']):

            for ($this->_sections['imethods']['index'] = $this->_sections['imethods']['start'], $this->_sections['imethods']['iteration'] = 1;
                 $this->_sections['imethods']['iteration'] <= $this->_sections['imethods']['total'];
                 $this->_sections['imethods']['index'] += $this->_sections['imethods']['step'], $this->_sections['imethods']['iteration']++):
$this->_sections['imethods']['rownum'] = $this->_sections['imethods']['iteration'];
$this->_sections['imethods']['index_prev'] = $this->_sections['imethods']['index'] - $this->_sections['imethods']['step'];
$this->_sections['imethods']['index_next'] = $this->_sections['imethods']['index'] + $this->_sections['imethods']['step'];
$this->_sections['imethods']['first']      = ($this->_sections['imethods']['iteration'] == 1);
$this->_sections['imethods']['last']       = ($this->_sections['imethods']['iteration'] == $this->_sections['imethods']['total']);
?>
      <thead>
        <tr>
          <th colspan="2">
            From <span class="classname"><?php echo $this->_tpl_vars['imethods'][$this->_sections['imethods']['index']]['parent_class']; ?>
</span>
          </th>
        </tr>
      </thead>
      <tbody>
        <?php if (isset($this->_sections['imethods2'])) unset($this->_sections['imethods2']);
$this->_sections['imethods2']['name'] = 'imethods2';
$this->_sections['imethods2']['loop'] = is_array($_loop=$this->_tpl_vars['imethods'][$this->_sections['imethods']['index']]['imethods']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
$this->_sections['imethods2']['show'] = true;
$this->_sections['imethods2']['max'] = $this->_sections['imethods2']['loop'];
$this->_sections['imethods2']['step'] = 1;
$this->_sections['imethods2']['start'] = $this->_sections['imethods2']['step'] > 0 ? 0 : $this->_sections['imethods2']['loop']-1;
if ($this->_sections['imethods2']['show']) {
    $this->_sections['imethods2']['total'] = $this->_sections['imethods2']['loop'];
    if ($this->_sections['imethods2']['total'] == 0)
        $this->_sections['imethods2']['show'] = false;
} else
    $this->_sections['imethods2']['total'] = 0;
if ($this->_sections['imethods2']['show']):

            for ($this->_sections['imethods2']['index'] = $this->_sections['imethods2']['start'], $this->_sections['imethods2']['iteration'] = 1;
                 $this->_sections['imethods2']['iteration'] <= $this->_sections['imethods2']['total'];
                 $this->_sections['imethods2']['index'] += $this->_sections['imethods2']['step'], $this->_sections['imethods2']['iteration']++):
$this->_sections['imethods2']['rownum'] = $this->_sections['imethods2']['iteration'];
$this->_sections['imethods2']['index_prev'] = $this->_sections['imethods2']['index'] - $this->_sections['imethods2']['step'];
$this->_sections['imethods2']['index_next'] = $this->_sections['imethods2']['index'] + $this->_sections['imethods2']['step'];
$this->_sections['imethods2']['first']      = ($this->_sections['imethods2']['iteration'] == 1);
$this->_sections['imethods2']['last']       = ($this->_sections['imethods2']['iteration'] == $this->_sections['imethods2']['total']);
?>
          <tr>
            <td class="right">
              <?php if ($this->_tpl_vars['imethods'][$this->_sections['imethods']['index']]['imethods'][$this->_sections['imethods2']['index']]['access']): ?><em><?php echo $this->_tpl_vars['imethods'][$this->_sections['imethods']['index']]['imethods'][$this->_sections['imethods2']['index']]['access']; ?>
</em><?php endif; ?>
              <?php if ($this->_tpl_vars['imethods'][$this->_sections['imethods']['index']]['imethods'][$this->_sections['imethods2']['index']]['abstract']): ?>abstract<?php endif; ?>
              <?php if ($this->_tpl_vars['imethods'][$this->_sections['imethods']['index']]['imethods'][$this->_sections['imethods2']['index']]['static']): ?>static<?php endif; ?>
              <?php if ($this->_tpl_vars['imethods'][$this->_sections['imethods']['index']]['imethods'][$this->_sections['imethods2']['index']]['function_return']): ?><em><?php echo $this->_tpl_vars['imethods'][$this->_sections['imethods']['index']]['imethods'][$this->_sections['imethods2']['index']]['function_return']; ?>
</em><?php endif; ?>
            </td>
            <td>
              <code><b><?php echo $this->_tpl_vars['imethods'][$this->_sections['imethods']['index']]['imethods'][$this->_sections['imethods2']['index']]['link']; ?>
</b></code>
              <?php if ($this->_tpl_vars['imethods'][$this->_sections['imethods']['index']]['imethods'][$this->_sections['imethods2']['index']]['sdesc']): ?><br /><div style="margin-left: 20px"><?php echo $this->_tpl_vars['imethods'][$this->_sections['imethods']['index']]['imethods'][$this->_sections['imethods2']['index']]['sdesc']; ?>
</div><?php endif; ?>
              <?php if ($this->_tpl_vars['imethods'][$this->_sections['imethods']['index']]['imethods'][$this->_sections['im2']['index']]['sdesc']): ?><br /><div style="margin-left: 20px"><?php echo $this->_tpl_vars['imethods'][$this->_sections['imethods']['index']]['imethods'][$this->_sections['im2']['index']]['sdesc']; ?>
</div><?php endif; ?>
            </td>
          </tr>
        <?php endfor; endif; ?>
      </tbody>
    <?php endfor; endif; ?>
  </table>
<?php endif; ?>

<?php if ($this->_tpl_vars['methods']): ?>
  <a name="sec-methods"></a>
  <h2>Methods</h2>
  <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "method.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
  endif; ?>

<p class="notes">
  Located in <a class="field" href="<?php echo $this->_tpl_vars['page_link']; ?>
"><?php echo $this->_tpl_vars['source_location']; ?>
</a> 
  [<span class="field">line <?php if ($this->_tpl_vars['class_slink']):  echo $this->_tpl_vars['class_slink'];  else:  echo $this->_tpl_vars['line_number'];  endif; ?></span>]
</p>

<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "footer.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>