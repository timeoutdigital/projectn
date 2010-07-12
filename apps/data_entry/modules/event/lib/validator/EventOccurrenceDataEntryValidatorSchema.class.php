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
class EventOccurrenceDataEntryValidatorSchema extends sfValidatorSchema
{

  protected function configure($options = array(), $messages = array())
  {
    $this->addMessage('poi_id', 'The poi_id is required.');
    $this->addMessage('start_date', 'The start_date is required.');
  }

  protected function doClean($values)
  {
    $errorSchema = new sfValidatorErrorSchema($this);

    // start_date is filled but no poi_id
    if ($values['start_date'] && !$values['poi_id'])
    {
      $errorSchemaLocal->addError(new sfValidatorError($this, 'required'), 'poi_id');
    }

    // no poi_id and no start_date, remove the empty values
    if (!$values['start_date'])
    {
      foreach($values as $key => $value)
      {
        unset($values[$key]);
      }
    }

    if (count($errorSchema))
    {
      throw new sfValidatorErrorSchema($this, $errorSchema);
    }

    return $values;
  }
}