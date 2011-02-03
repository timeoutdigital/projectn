<ul class="sf_admin_td_actions">
    <li class="sf_admin_action_info">
        <?php echo link_to( 'Info', 'log_import_error/ListErrorInfo?id='.$log_import_error[ 'id' ], array()) ?>
    </li>
<?php if ( $log_import_error['isFixable'] ) : ?>
    <li class="sf_admin_action_resolve">
        <?php echo link_to( 'Resolve', strtolower( $log_import_error[ 'model' ] ) . '/resolve', array( 'popup' => true, 'query_string' => 'import_error_id=' . $log_import_error[ 'id' ] )) ?>
    </li>
<?php endif; ?>
</ul>