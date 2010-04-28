<?php

/**
 * RecordFieldOverrideMovie form base class.
 *
 * @method RecordFieldOverrideMovie getObject() Returns the current form's model object
 *
 * @package    sf_sandbox
 * @subpackage form
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormGeneratedInheritanceTemplate.php 24171 2009-11-19 16:37:50Z Kris.Wallsmith $
 */
abstract class BaseRecordFieldOverrideMovieForm extends RecordFieldOverrideForm
{
  protected function setupInheritance()
  {
    parent::setupInheritance();

    $this->widgetSchema->setNameFormat('record_field_override_movie[%s]');
  }

  public function getModelName()
  {
    return 'RecordFieldOverrideMovie';
  }

}
