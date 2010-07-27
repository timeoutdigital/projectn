<?php use_helper('JavascriptBase'); ?>
<?php
    $filter = $sf_user->getAttribute('geocode_ui.filters', array(), 'admin_module');

    $list = ( isset( $filter['list'] ) ) ? $filter['list'] : '';

    $tabs = array(
        'non-geocoded' => 'non - geocoded',
        'geocoded' => 'geocoded',

        );

?>


<?php include_partial( 'google_map', array( 'noFilter' => true ) ); ?>
<?php include_partial( 'venue_details', array( 'noFilter' => true ) ); ?>

<div class="clearfix backendTabs"><ul>
<?php foreach ($tabs as $status => $text): ?>
   <li<?php echo ($list == $status) ? ' class="selected"' : ''?>><?php echo link_to($text, 'geocode_ui/chooseList?list=' . $status); ?></li>
<?php endforeach ?>
</ul></div>
<ul class="sf_admin_actions">
<?php if ( !isset( $noFilter ) || !$noFilter ) : ?>
<li class="sf_admin_action_new"><?php echo link_to_function( 'Show filters', 'showFilter(true);', array( 'id' => 'show_filter' ) ); ?></li>
<li class="sf_admin_action_delete"><?php echo link_to_function( 'Hide filters', 'showFilter(false);', array( 'id' => 'hide_filter', 'style' => 'display: none' ) ); ?></li>
<?php endif; ?>
</ul>
<?php
    $filters = $sf_user->getAttribute( 'geocode_ui.filters', array(), 'admin_module' );
    //var_dump($filters);
?>