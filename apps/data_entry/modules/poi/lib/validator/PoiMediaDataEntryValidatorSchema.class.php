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
class PoiMediaDataEntryValidatorSchema extends sfValidatorSchema
{

  protected function configure($options = array(), $messages = array())
  {
  }

  protected function doClean($values)
  {
    if (!$values['url'] && !$values['url_delete'])
    {
      foreach($values as $key => $value)
      {
          unset($values[$key]);
      }
    }

    return $values;
  }
}