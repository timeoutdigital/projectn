<?php

/**
 * EventOccurrence form.
 *
 * @package    sf_sandbox
 * @subpackage form
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class EventOccurrenceForm extends BaseEventOccurrenceForm
{
  public function configure()
  {
      //@todo try to move this into app..
      $this->useFields( array( 'start_date', 'start_time', 'end_date', 'end_time', 'poi_id', ) );
      $this->mergePostValidator(new EventOccurrenceDataEntryValidatorSchema());
  }
}
