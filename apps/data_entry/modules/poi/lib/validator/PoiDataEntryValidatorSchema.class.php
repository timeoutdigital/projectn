<?php
/**
 * widgetFormVendorPoiCategory
 *
 * @package symfony
 * @subpackage validator.lib.modules.data_entry.apps
 *
 * @author Ralph Schwaninger <ralphschwaninger@timout.com>
 * @copyright Timeout Communications Ltd
 *
 * @version 1.0.0
 *
 *
 */
class PoiDataEntryValidatorSchema extends sfValidatorSchema
{

  protected function configure($options = array(), $messages = array())
  {
  }

  protected function doClean($values)
  {
    //compose geocode_look_up string
    $values[ 'geocode_look_up' ] = stringTransform::concatNonBlankStrings( ', ', array( $values['house_no'], $values['street'], $values['city'], $values['zips'] ) );
   
    return $values;
  }
}