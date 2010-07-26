<?php use_helper('I18N', 'Date') ?>
<?php include_partial('venue/assets') ?>
<div id="sf_admin_container">
  <h1><?php echo __('Venues', array(), 'messages') ?></h1>

  <?php include_partial('venue/flashes') ?>

  <div id="sf_admin_header">
    <?php include_partial('venue/list_header', array('pager' => $pager)) ?>
     <?php include_partial('venue/filters', array('form' => $filters, 'configuration' => $configuration)) ?>
  </div>

  <div id="sf_admin_content">
    <form action="<?php echo url_for('venue_collection', array('action' => 'batch')) ?>" method="post">
    <?php include_partial('venue/list', array('pager' => $pager, 'sort' => $sort, 'helper' => $helper)) ?>
    <ul class="sf_admin_actions">
      <?php include_partial('venue/list_batch_actions', array('helper' => $helper)) ?>
      <?php include_partial('venue/list_actions', array('helper' => $helper)) ?>
    </ul>
    </form>
  </div>

  <div id="sf_admin_footer">
    <?php include_partial('venue/list_footer', array('pager' => $pager)) ?>
  </div>
</div>
