<fieldset id="sf_fieldset_<?php echo preg_replace('/[^a-z0-9_]/', '_', strtolower($fieldset)) ?>">
  <?php if ('NONE' != $fieldset): ?>
    <h2><?php echo __($fieldset, array(), 'messages') ?></h2>
  <?php endif; ?>
  <?php foreach ($fields as $name => $field): ?>
    <?php if ((isset($form[$name]) && $form[$name]->isHidden()) || (!isset($form[$name]) && $field->isReal())) continue ?>


    
    <?php include_partial('poi/form_field', array(
      'name'       => $name,
      'attributes' => $field->getConfig('attributes', array()),
      'label'      => $field->getConfig('label'),
      'help'       => $field->getConfig('help'),
      'form'       => $form,
      'field'      => $field,
      'class'      => 'sf_admin_form_row sf_admin_'.strtolower($field->getType()).' sf_admin_form_field_'.$name,
    )) ?>


    <?php if($name == "latitude"):?>
    <div class="sf_admin_form_row sf_admin_text">
       <label for="poi_vendor_geo_link">Geo Link</label>
       <div class="content">

           <?php $address = urlencode($poi['street'].', '. $poi['city']); ?>

           
           
           <?php
             $geoCodeObj = new googleGeocoder();

             $unescapedPoi = $sf_data->getRaw('poi');

            $geoCodeObj->setAddress($address);
            $geoCodeObj->setBounds($unescapedPoi['Vendor']->getGoogleApiGeoBounds());
            $geoCodeObj->setRegion($unescapedPoi['Vendor']['country_code']);
           ?>

           <a href="<?php echo $geoCodeObj->getLookupUrl(); ?>">Link</a>
       </div>
    </div>


    <?php endif; ?>
  <?php endforeach; ?>
</fieldset>
