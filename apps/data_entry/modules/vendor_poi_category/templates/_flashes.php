<?php if ($sf_user->hasFlash('notice')): ?>
  <div class="notice"><?php echo html_entity_decode(  __($sf_user->getFlash('notice'), array(), 'sf_admin') )  ?></div>
<?php endif; ?>

<?php if ($sf_user->hasFlash('error')): ?>
  <div class="error"><?php echo html_entity_decode( __($sf_user->getFlash('error'), array(), 'sf_admin') ) ?></div>
<?php endif; ?>

<?php if ($sf_user->hasFlash('error_poi_category_delete')):?>

<?php $poiList =json_decode( html_entity_decode( $sf_user->getFlash('error_poi_category_delete' , array(), 'sf_admin')) ,true );  ?>
  <div class="error">Vendor Poi Category cannot be deleted because there are <?php echo count( $poiList ); ?> Poi(s) attached to this category:<br />
  <?php $i = 0 ;?>
  <?php foreach ($poiList as $id => $poiName) : ?>
    <a href="poi/<?php echo $id;?>/edit" target="_blank" ><?php echo $poiName; ?> </a>
    <?php if( ++$i != count( $poiList )) :?>, <?php endif;?>
  <?php endforeach;?>
   </div>
<?php endif; ?>

