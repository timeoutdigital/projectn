<?php if ($sf_user->hasFlash('notice')): ?>
  <div class="notice"><?php echo html_entity_decode(  __($sf_user->getFlash('notice'), array(), 'sf_admin') )  ?></div>
<?php endif; ?>

<?php if ($sf_user->hasFlash('error')): ?>
  <div class="error"><?php echo html_entity_decode( __($sf_user->getFlash('error'), array(), 'sf_admin') ) ?></div>
<?php endif; ?>

<?php if ($sf_user->hasFlash('error_poi_category_delete')):?>

<?php $errorData =json_decode( html_entity_decode( $sf_user->getFlash('error_poi_category_delete' , array(), 'sf_admin')) ,true );  ?>
  <div class="error">
  <p>
    Vendor Poi Category "<?php echo $errorData[ 'vendorPoiCategoryName' ] ; ?>" cannot be deleted because there are <?php echo count( $errorData[ 'poiList'] ); ?> venue(s) attached to this category
  </p>
  <p>
    You can change the name of the category or remove the unassociate the category from the venues below before deleting the category:
  </p>
      <?php $i = 0 ;?>
      <?php foreach ($errorData[ 'poiList'] as $id => $poiName) : ?>
        <a href="poi/<?php echo $id;?>/edit" target="_blank" ><?php echo $poiName ; ?> </a>
        <?php if( ++$i != count( $errorData[ 'poiList'] )) :?>, <?php endif;?>
      <?php endforeach;?>
  </p>
   </div>
<?php endif; ?>

