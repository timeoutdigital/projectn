<?php use_helper('JavascriptBase'); ?>
<?php
    $filter = $sf_user->getAttribute('geocode_ui.filters', array(), 'admin_module');

    $list = ( isset( $filter['list'] ) ) ? $filter['list'] : '';

    $tabs = array(
        'non-geocoded' => 'non - Geocoded',
        'geocoded' => 'Geocoded',
        'manual' => 'Manually Geocoded',

        );

?>


<?php include_partial( 'google_map', array( 'noFilter' => true ) ); ?>
<?php include_partial( 'venue_details', array( 'noFilter' => true ) ); ?>

<div class="clearfix backendTabs"><ul>
<?php foreach ($tabs as $status => $text): ?>
   <li<?php echo ($list == $status) ? ' class="selected"' : ''?>><?php echo link_to($text, 'geocode_ui/chooseList?list=' . $status); ?></li>
<?php endforeach ?>
</ul></div>

<?php
    $filters = $sf_user->getAttribute( 'geocode_ui.filters', array(), 'admin_module' );
?>