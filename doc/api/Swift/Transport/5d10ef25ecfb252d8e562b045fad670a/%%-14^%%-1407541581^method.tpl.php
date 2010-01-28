<?php /* Smarty version 2.6.0, created on 2010-01-27 17:42:46
         compiled from method.tpl */ ?>
<a name='method_detail'></a>
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
    <a name="method<?php echo $this->_tpl_vars['methods'][$this->_sections['methods']['index']]['function_name']; ?>
" id="<?php echo $this->_tpl_vars['methods'][$this->_sections['methods']['index']]['function_name']; ?>
"><!-- --></a>

    <h3><?php echo $this->_tpl_vars['methods'][$this->_sections['methods']['index']]['function_name']; ?>
</h3>

    <div class="method-signature">
      static <?php echo $this->_tpl_vars['methods'][$this->_sections['methods']['index']]['function_return']; ?>

      <?php if ($this->_tpl_vars['methods'][$this->_sections['methods']['index']]['ifunction_call']['returnsref']): ?>&amp;<?php endif;  echo $this->_tpl_vars['methods'][$this->_sections['methods']['index']]['function_name']; ?>
(
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
          <?php if ($this->_tpl_vars['methods'][$this->_sections['methods']['index']]['ifunction_call']['params'][$this->_sections['params']['index']]['hasdefault']): ?>[<?php endif;  echo $this->_tpl_vars['methods'][$this->_sections['methods']['index']]['ifunction_call']['params'][$this->_sections['params']['index']]['type']; ?>

          <?php echo $this->_tpl_vars['methods'][$this->_sections['methods']['index']]['ifunction_call']['params'][$this->_sections['params']['index']]['name']; ?>

          <?php if ($this->_tpl_vars['methods'][$this->_sections['methods']['index']]['ifunction_call']['params'][$this->_sections['params']['index']]['hasdefault']): ?> = <?php echo $this->_tpl_vars['methods'][$this->_sections['methods']['index']]['ifunction_call']['params'][$this->_sections['params']['index']]['default']; ?>
]<?php endif; ?>
        <?php endfor; endif; ?>
      <?php endif; ?>)
    </div>

      <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "docblock.tpl", 'smarty_include_vars' => array('sdesc' => $this->_tpl_vars['methods'][$this->_sections['methods']['index']]['sdesc'],'desc' => $this->_tpl_vars['methods'][$this->_sections['methods']['index']]['desc'])));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
      
      <?php if ($this->_tpl_vars['methods'][$this->_sections['methods']['index']]['params']): ?>
        <h4>Parameters:</h4>
        <table class="detail">
          <thead>
            <tr>
              <th>Name</th>
              <th>Type</th>
              <th class="desc">Description</th>
            </tr>
          </thead>
          <tbody>
            <?php if (isset($this->_sections['params'])) unset($this->_sections['params']);
$this->_sections['params']['name'] = 'params';
$this->_sections['params']['loop'] = is_array($_loop=$this->_tpl_vars['methods'][$this->_sections['methods']['index']]['params']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
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
              <tr>
                <td><code><?php echo $this->_tpl_vars['methods'][$this->_sections['methods']['index']]['params'][$this->_sections['params']['index']]['var']; ?>
</code></td>
                <td><em><?php echo $this->_tpl_vars['methods'][$this->_sections['methods']['index']]['params'][$this->_sections['params']['index']]['datatype']; ?>
</em></td>
                <td>
                  <?php if ($this->_tpl_vars['methods'][$this->_sections['methods']['index']]['params'][$this->_sections['params']['index']]['data']): ?>
                    <?php echo $this->_tpl_vars['methods'][$this->_sections['methods']['index']]['params'][$this->_sections['params']['index']]['data']; ?>

                  <?php endif; ?>
                </td>
              </tr>
            <?php endfor; endif; ?>
          </tbody>
        </table>
      <?php endif; ?>

      <?php if ($this->_tpl_vars['methods'][$this->_sections['methods']['index']]['exceptions']): ?>
        <h4>Exceptions:</h4>
        <table class="detail">
          <thead>
            <tr>
              <th>Type</th>
              <th class="desc">Description</th>
            </tr>
          </thead>
          <tbody>
            <?php if (isset($this->_sections['exception'])) unset($this->_sections['exception']);
$this->_sections['exception']['name'] = 'exception';
$this->_sections['exception']['loop'] = is_array($_loop=$this->_tpl_vars['methods'][$this->_sections['methods']['index']]['exceptions']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
$this->_sections['exception']['show'] = true;
$this->_sections['exception']['max'] = $this->_sections['exception']['loop'];
$this->_sections['exception']['step'] = 1;
$this->_sections['exception']['start'] = $this->_sections['exception']['step'] > 0 ? 0 : $this->_sections['exception']['loop']-1;
if ($this->_sections['exception']['show']) {
    $this->_sections['exception']['total'] = $this->_sections['exception']['loop'];
    if ($this->_sections['exception']['total'] == 0)
        $this->_sections['exception']['show'] = false;
} else
    $this->_sections['exception']['total'] = 0;
if ($this->_sections['exception']['show']):

            for ($this->_sections['exception']['index'] = $this->_sections['exception']['start'], $this->_sections['exception']['iteration'] = 1;
                 $this->_sections['exception']['iteration'] <= $this->_sections['exception']['total'];
                 $this->_sections['exception']['index'] += $this->_sections['exception']['step'], $this->_sections['exception']['iteration']++):
$this->_sections['exception']['rownum'] = $this->_sections['exception']['iteration'];
$this->_sections['exception']['index_prev'] = $this->_sections['exception']['index'] - $this->_sections['exception']['step'];
$this->_sections['exception']['index_next'] = $this->_sections['exception']['index'] + $this->_sections['exception']['step'];
$this->_sections['exception']['first']      = ($this->_sections['exception']['iteration'] == 1);
$this->_sections['exception']['last']       = ($this->_sections['exception']['iteration'] == $this->_sections['exception']['total']);
?>
              <tr>
                <td><code><?php echo $this->_tpl_vars['methods'][$this->_sections['methods']['index']]['exceptions'][$this->_sections['exception']['index']]['type']; ?>
</code></td>
                <td>
                  <?php if ($this->_tpl_vars['methods'][$this->_sections['methods']['index']]['exceptions'][$this->_sections['exception']['index']]['data']): ?>
                    <?php echo $this->_tpl_vars['methods'][$this->_sections['methods']['index']]['exceptions'][$this->_sections['exception']['index']]['data']; ?>

                  <?php endif; ?>
                </td>
              </tr>
            <?php endfor; endif; ?>
          </tbody>
        </table>
      <?php endif; ?>

      <?php if ($this->_tpl_vars['methods'][$this->_sections['methods']['index']]['method_overrides']): ?>
        <h4>Redefinition of:</h4>
        <table class="detail">
          <thead>
            <tr>
              <th>Method</th>
              <th class="desc">Description</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td><code><?php echo $this->_tpl_vars['methods'][$this->_sections['methods']['index']]['method_overrides']['link']; ?>
</code></td>
              <td>
                <?php if ($this->_tpl_vars['methods'][$this->_sections['methods']['index']]['method_overrides']['sdesc']): ?>
                  <?php echo $this->_tpl_vars['methods'][$this->_sections['methods']['index']]['method_overrides']['sdesc']; ?>

                <?php endif; ?>
              </td>
            </tr>
          </tbody>
        </table>
      <?php endif; ?>

      <?php if ($this->_tpl_vars['methods'][$this->_sections['methods']['index']]['method_implements']): ?>
        <h4>Implementation of:</h4>
        <table class="detail">
          <thead>
            <tr>
              <th>Method</th>
              <th class="desc">Description</th>
            </tr>
          </thead>
          <tbody>
            <?php if (isset($this->_sections['imp'])) unset($this->_sections['imp']);
$this->_sections['imp']['name'] = 'imp';
$this->_sections['imp']['loop'] = is_array($_loop=$this->_tpl_vars['methods'][$this->_sections['methods']['index']]['method_implements']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
$this->_sections['imp']['show'] = true;
$this->_sections['imp']['max'] = $this->_sections['imp']['loop'];
$this->_sections['imp']['step'] = 1;
$this->_sections['imp']['start'] = $this->_sections['imp']['step'] > 0 ? 0 : $this->_sections['imp']['loop']-1;
if ($this->_sections['imp']['show']) {
    $this->_sections['imp']['total'] = $this->_sections['imp']['loop'];
    if ($this->_sections['imp']['total'] == 0)
        $this->_sections['imp']['show'] = false;
} else
    $this->_sections['imp']['total'] = 0;
if ($this->_sections['imp']['show']):

            for ($this->_sections['imp']['index'] = $this->_sections['imp']['start'], $this->_sections['imp']['iteration'] = 1;
                 $this->_sections['imp']['iteration'] <= $this->_sections['imp']['total'];
                 $this->_sections['imp']['index'] += $this->_sections['imp']['step'], $this->_sections['imp']['iteration']++):
$this->_sections['imp']['rownum'] = $this->_sections['imp']['iteration'];
$this->_sections['imp']['index_prev'] = $this->_sections['imp']['index'] - $this->_sections['imp']['step'];
$this->_sections['imp']['index_next'] = $this->_sections['imp']['index'] + $this->_sections['imp']['step'];
$this->_sections['imp']['first']      = ($this->_sections['imp']['iteration'] == 1);
$this->_sections['imp']['last']       = ($this->_sections['imp']['iteration'] == $this->_sections['imp']['total']);
?>
              <tr>
                <td><code><?php echo $this->_tpl_vars['methods'][$this->_sections['methods']['index']]['method_implements'][$this->_sections['imp']['index']]['link']; ?>
</code></td>
                <td>
                  <?php if ($this->_tpl_vars['methods'][$this->_sections['methods']['index']]['method_implements'][$this->_sections['imp']['index']]['sdesc']): ?>
                    <?php echo $this->_tpl_vars['methods'][$this->_sections['methods']['index']]['method_implements'][$this->_sections['imp']['index']]['sdesc']; ?>

                  <?php endif; ?>
                </td>
              </tr>
            <?php endfor; endif; ?>
          </tbody>
        </table>
      <?php endif; ?>

      <?php if ($this->_tpl_vars['methods'][$this->_sections['methods']['index']]['descmethod']): ?>
        <h4>Redefined in descendants as:</h4>
        <table class="detail">
          <thead>
            <tr>
              <th>Method</th>
              <th class="desc">Description</th>
            </tr>
          </thead>
          <tbody>
            <?php if (isset($this->_sections['dm'])) unset($this->_sections['dm']);
$this->_sections['dm']['name'] = 'dm';
$this->_sections['dm']['loop'] = is_array($_loop=$this->_tpl_vars['methods'][$this->_sections['methods']['index']]['descmethod']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
$this->_sections['dm']['show'] = true;
$this->_sections['dm']['max'] = $this->_sections['dm']['loop'];
$this->_sections['dm']['step'] = 1;
$this->_sections['dm']['start'] = $this->_sections['dm']['step'] > 0 ? 0 : $this->_sections['dm']['loop']-1;
if ($this->_sections['dm']['show']) {
    $this->_sections['dm']['total'] = $this->_sections['dm']['loop'];
    if ($this->_sections['dm']['total'] == 0)
        $this->_sections['dm']['show'] = false;
} else
    $this->_sections['dm']['total'] = 0;
if ($this->_sections['dm']['show']):

            for ($this->_sections['dm']['index'] = $this->_sections['dm']['start'], $this->_sections['dm']['iteration'] = 1;
                 $this->_sections['dm']['iteration'] <= $this->_sections['dm']['total'];
                 $this->_sections['dm']['index'] += $this->_sections['dm']['step'], $this->_sections['dm']['iteration']++):
$this->_sections['dm']['rownum'] = $this->_sections['dm']['iteration'];
$this->_sections['dm']['index_prev'] = $this->_sections['dm']['index'] - $this->_sections['dm']['step'];
$this->_sections['dm']['index_next'] = $this->_sections['dm']['index'] + $this->_sections['dm']['step'];
$this->_sections['dm']['first']      = ($this->_sections['dm']['iteration'] == 1);
$this->_sections['dm']['last']       = ($this->_sections['dm']['iteration'] == $this->_sections['dm']['total']);
?>
              <tr>
                <td><code><?php echo $this->_tpl_vars['methods'][$this->_sections['methods']['index']]['descmethod'][$this->_sections['dm']['index']]['link']; ?>
</code></td>
                <td>
                  <?php if ($this->_tpl_vars['methods'][$this->_sections['methods']['index']]['descmethod'][$this->_sections['dm']['index']]['sdesc']):  echo $this->_tpl_vars['methods'][$this->_sections['methods']['index']]['descmethod'][$this->_sections['dm']['index']]['sdesc'];  endif; ?>&nbsp;
                </td>
              </tr>
            <?php endfor; endif; ?>
          </tbody>
        </table>
      <?php endif; ?>

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
    <a name="method<?php echo $this->_tpl_vars['methods'][$this->_sections['methods']['index']]['function_name']; ?>
" id="<?php echo $this->_tpl_vars['methods'][$this->_sections['methods']['index']]['function_name']; ?>
"><!-- --></a>

    <h3><?php echo $this->_tpl_vars['methods'][$this->_sections['methods']['index']]['function_name']; ?>
</h3>

    <div class="method-signature">
      <?php echo $this->_tpl_vars['methods'][$this->_sections['methods']['index']]['function_return']; ?>

      <?php if ($this->_tpl_vars['methods'][$this->_sections['methods']['index']]['ifunction_call']['returnsref']): ?>&amp;<?php endif;  echo $this->_tpl_vars['methods'][$this->_sections['methods']['index']]['function_name']; ?>
(
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
          <?php if ($this->_tpl_vars['methods'][$this->_sections['methods']['index']]['ifunction_call']['params'][$this->_sections['params']['index']]['hasdefault']): ?>[<?php endif;  echo $this->_tpl_vars['methods'][$this->_sections['methods']['index']]['ifunction_call']['params'][$this->_sections['params']['index']]['type']; ?>

          <?php echo $this->_tpl_vars['methods'][$this->_sections['methods']['index']]['ifunction_call']['params'][$this->_sections['params']['index']]['name']; ?>

          <?php if ($this->_tpl_vars['methods'][$this->_sections['methods']['index']]['ifunction_call']['params'][$this->_sections['params']['index']]['hasdefault']): ?> = <?php echo $this->_tpl_vars['methods'][$this->_sections['methods']['index']]['ifunction_call']['params'][$this->_sections['params']['index']]['default']; ?>
]<?php endif; ?>
        <?php endfor; endif; ?>
      <?php endif; ?>)
    </div>

    <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "docblock.tpl", 'smarty_include_vars' => array('sdesc' => $this->_tpl_vars['methods'][$this->_sections['methods']['index']]['sdesc'],'desc' => $this->_tpl_vars['methods'][$this->_sections['methods']['index']]['desc'])));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>

    <?php if ($this->_tpl_vars['methods'][$this->_sections['methods']['index']]['params']): ?>
      <h4>Parameters:</h4>
      <table class="detail">
        <thead>
          <tr>
            <th>Name</th>
            <th>Type</th>
            <th class="desc">Description</th>
          </tr>
        </thead>
        <tbody>
          <?php if (isset($this->_sections['params'])) unset($this->_sections['params']);
$this->_sections['params']['name'] = 'params';
$this->_sections['params']['loop'] = is_array($_loop=$this->_tpl_vars['methods'][$this->_sections['methods']['index']]['params']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
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
            <tr>
              <td><code><?php echo $this->_tpl_vars['methods'][$this->_sections['methods']['index']]['params'][$this->_sections['params']['index']]['var']; ?>
</code></td>
              <td><em><?php echo $this->_tpl_vars['methods'][$this->_sections['methods']['index']]['params'][$this->_sections['params']['index']]['datatype']; ?>
</em></td>
              <td>
                <?php if ($this->_tpl_vars['methods'][$this->_sections['methods']['index']]['params'][$this->_sections['params']['index']]['data']): ?>
                  <?php echo $this->_tpl_vars['methods'][$this->_sections['methods']['index']]['params'][$this->_sections['params']['index']]['data']; ?>

                <?php endif; ?>
              </td>
            </tr>
          <?php endfor; endif; ?>
        </tbody>
      </table>
    <?php endif; ?>

    <?php if ($this->_tpl_vars['methods'][$this->_sections['methods']['index']]['exceptions']): ?>
      <h4>Exceptions:</h4>
      <table class="detail">
        <thead>
          <tr>
            <th>Type</th>
            <th class="desc">Description</th>
          </tr>
        </thead>
        <tbody>
          <?php if (isset($this->_sections['exception'])) unset($this->_sections['exception']);
$this->_sections['exception']['name'] = 'exception';
$this->_sections['exception']['loop'] = is_array($_loop=$this->_tpl_vars['methods'][$this->_sections['methods']['index']]['exceptions']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
$this->_sections['exception']['show'] = true;
$this->_sections['exception']['max'] = $this->_sections['exception']['loop'];
$this->_sections['exception']['step'] = 1;
$this->_sections['exception']['start'] = $this->_sections['exception']['step'] > 0 ? 0 : $this->_sections['exception']['loop']-1;
if ($this->_sections['exception']['show']) {
    $this->_sections['exception']['total'] = $this->_sections['exception']['loop'];
    if ($this->_sections['exception']['total'] == 0)
        $this->_sections['exception']['show'] = false;
} else
    $this->_sections['exception']['total'] = 0;
if ($this->_sections['exception']['show']):

            for ($this->_sections['exception']['index'] = $this->_sections['exception']['start'], $this->_sections['exception']['iteration'] = 1;
                 $this->_sections['exception']['iteration'] <= $this->_sections['exception']['total'];
                 $this->_sections['exception']['index'] += $this->_sections['exception']['step'], $this->_sections['exception']['iteration']++):
$this->_sections['exception']['rownum'] = $this->_sections['exception']['iteration'];
$this->_sections['exception']['index_prev'] = $this->_sections['exception']['index'] - $this->_sections['exception']['step'];
$this->_sections['exception']['index_next'] = $this->_sections['exception']['index'] + $this->_sections['exception']['step'];
$this->_sections['exception']['first']      = ($this->_sections['exception']['iteration'] == 1);
$this->_sections['exception']['last']       = ($this->_sections['exception']['iteration'] == $this->_sections['exception']['total']);
?>
            <tr>
              <td><code><?php echo $this->_tpl_vars['methods'][$this->_sections['methods']['index']]['exceptions'][$this->_sections['exception']['index']]['type']; ?>
</code></td>
              <td>
                <?php if ($this->_tpl_vars['methods'][$this->_sections['methods']['index']]['exceptions'][$this->_sections['exception']['index']]['data']): ?>
                  <?php echo $this->_tpl_vars['methods'][$this->_sections['methods']['index']]['exceptions'][$this->_sections['exception']['index']]['data']; ?>

                <?php endif; ?>
              </td>
            </tr>
          <?php endfor; endif; ?>
        </tbody>
      </table>
    <?php endif; ?>
    

    <?php if ($this->_tpl_vars['methods'][$this->_sections['methods']['index']]['method_overrides']): ?>
      <h4>Redefinition of:</h4>
      <table class="detail">
        <thead>
          <tr>
            <th>Method</th>
            <th class="desc">Description</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td><code><?php echo $this->_tpl_vars['methods'][$this->_sections['methods']['index']]['method_overrides']['link']; ?>
</code></td>
            <td>
              <?php if ($this->_tpl_vars['methods'][$this->_sections['methods']['index']]['method_overrides']['sdesc']): ?>
                <?php echo $this->_tpl_vars['methods'][$this->_sections['methods']['index']]['method_overrides']['sdesc']; ?>

              <?php endif; ?>
            </td>
          </tr>
        </tbody>
      </table>
    <?php endif; ?>

    <?php if ($this->_tpl_vars['methods'][$this->_sections['methods']['index']]['method_implements']): ?>
      <h4>Implementation of:</h4>
      <table class="detail">
        <thead>
          <tr>
            <th>Method</th>
            <th class="desc">Description</th>
          </tr>
        </thead>
        <tbody>
          <?php if (isset($this->_sections['imp'])) unset($this->_sections['imp']);
$this->_sections['imp']['name'] = 'imp';
$this->_sections['imp']['loop'] = is_array($_loop=$this->_tpl_vars['methods'][$this->_sections['methods']['index']]['method_implements']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
$this->_sections['imp']['show'] = true;
$this->_sections['imp']['max'] = $this->_sections['imp']['loop'];
$this->_sections['imp']['step'] = 1;
$this->_sections['imp']['start'] = $this->_sections['imp']['step'] > 0 ? 0 : $this->_sections['imp']['loop']-1;
if ($this->_sections['imp']['show']) {
    $this->_sections['imp']['total'] = $this->_sections['imp']['loop'];
    if ($this->_sections['imp']['total'] == 0)
        $this->_sections['imp']['show'] = false;
} else
    $this->_sections['imp']['total'] = 0;
if ($this->_sections['imp']['show']):

            for ($this->_sections['imp']['index'] = $this->_sections['imp']['start'], $this->_sections['imp']['iteration'] = 1;
                 $this->_sections['imp']['iteration'] <= $this->_sections['imp']['total'];
                 $this->_sections['imp']['index'] += $this->_sections['imp']['step'], $this->_sections['imp']['iteration']++):
$this->_sections['imp']['rownum'] = $this->_sections['imp']['iteration'];
$this->_sections['imp']['index_prev'] = $this->_sections['imp']['index'] - $this->_sections['imp']['step'];
$this->_sections['imp']['index_next'] = $this->_sections['imp']['index'] + $this->_sections['imp']['step'];
$this->_sections['imp']['first']      = ($this->_sections['imp']['iteration'] == 1);
$this->_sections['imp']['last']       = ($this->_sections['imp']['iteration'] == $this->_sections['imp']['total']);
?>
            <tr>
              <td><code><?php echo $this->_tpl_vars['methods'][$this->_sections['methods']['index']]['method_implements'][$this->_sections['imp']['index']]['link']; ?>
</code></td>
              <td>
                <?php if ($this->_tpl_vars['methods'][$this->_sections['methods']['index']]['method_implements'][$this->_sections['imp']['index']]['sdesc']): ?>
                  <?php echo $this->_tpl_vars['methods'][$this->_sections['methods']['index']]['method_implements'][$this->_sections['imp']['index']]['sdesc']; ?>

                <?php endif; ?>
              </td>
            </tr>
          <?php endfor; endif; ?>
        </tbody>
      </table>
    <?php endif; ?>

    <?php if ($this->_tpl_vars['methods'][$this->_sections['methods']['index']]['descmethod']): ?>
      <h4>Redefined in descendants as:</h4>
      <table class="detail">
        <thead>
          <tr>
            <th>Method</th>
            <th class="desc">Description</th>
          </tr>
        </thead>
        <tbody>
          <?php if (isset($this->_sections['dm'])) unset($this->_sections['dm']);
$this->_sections['dm']['name'] = 'dm';
$this->_sections['dm']['loop'] = is_array($_loop=$this->_tpl_vars['methods'][$this->_sections['methods']['index']]['descmethod']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
$this->_sections['dm']['show'] = true;
$this->_sections['dm']['max'] = $this->_sections['dm']['loop'];
$this->_sections['dm']['step'] = 1;
$this->_sections['dm']['start'] = $this->_sections['dm']['step'] > 0 ? 0 : $this->_sections['dm']['loop']-1;
if ($this->_sections['dm']['show']) {
    $this->_sections['dm']['total'] = $this->_sections['dm']['loop'];
    if ($this->_sections['dm']['total'] == 0)
        $this->_sections['dm']['show'] = false;
} else
    $this->_sections['dm']['total'] = 0;
if ($this->_sections['dm']['show']):

            for ($this->_sections['dm']['index'] = $this->_sections['dm']['start'], $this->_sections['dm']['iteration'] = 1;
                 $this->_sections['dm']['iteration'] <= $this->_sections['dm']['total'];
                 $this->_sections['dm']['index'] += $this->_sections['dm']['step'], $this->_sections['dm']['iteration']++):
$this->_sections['dm']['rownum'] = $this->_sections['dm']['iteration'];
$this->_sections['dm']['index_prev'] = $this->_sections['dm']['index'] - $this->_sections['dm']['step'];
$this->_sections['dm']['index_next'] = $this->_sections['dm']['index'] + $this->_sections['dm']['step'];
$this->_sections['dm']['first']      = ($this->_sections['dm']['iteration'] == 1);
$this->_sections['dm']['last']       = ($this->_sections['dm']['iteration'] == $this->_sections['dm']['total']);
?>
            <tr>
              <td><code><?php echo $this->_tpl_vars['methods'][$this->_sections['methods']['index']]['descmethod'][$this->_sections['dm']['index']]['link']; ?>
</code></td>
              <td>
                <?php if ($this->_tpl_vars['methods'][$this->_sections['methods']['index']]['descmethod'][$this->_sections['dm']['index']]['sdesc']):  echo $this->_tpl_vars['methods'][$this->_sections['methods']['index']]['descmethod'][$this->_sections['dm']['index']]['sdesc'];  endif; ?>&nbsp;
              </td>
            </tr>
          <?php endfor; endif; ?>
        </tbody>
      </table>
    <?php endif; ?>

  <?php endif; ?>
<?php endfor; endif; ?>