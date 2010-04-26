<?php

/**
 * SLLVenue form base class.
 *
 * @method SLLVenue getObject() Returns the current form's model object
 *
 * @package    sf_sandbox
 * @subpackage form
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormGeneratedTemplate.php 24171 2009-11-19 16:37:50Z Kris.Wallsmith $
 */
abstract class BaseSLLVenueForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'               => new sfWidgetFormInputHidden(),
      'neighbourhood_id' => new sfWidgetFormInputText(),
      'name'             => new sfWidgetFormInputText(),
      'address'          => new sfWidgetFormTextarea(),
      'postcode'         => new sfWidgetFormInputText(),
      'latitude'         => new sfWidgetFormInputText(),
      'longitude'        => new sfWidgetFormInputText(),
      'status'           => new sfWidgetFormInputText(),
      'source_id'        => new sfWidgetFormInputText(),
      'event_count'      => new sfWidgetFormInputText(),
      'alt_name'         => new sfWidgetFormInputText(),
      'building_name'    => new sfWidgetFormInputText(),
      'travel'           => new sfWidgetFormTextarea(),
      'opening_times'    => new sfWidgetFormInputText(),
      'url'              => new sfWidgetFormInputText(),
      'phone'            => new sfWidgetFormInputText(),
      'email'            => new sfWidgetFormInputText(),
      'image_id'         => new sfWidgetFormInputText(),
      'source'           => new sfWidgetFormInputText(),
      'annotation'       => new sfWidgetFormTextarea(),
    ));

    $this->setValidators(array(
      'id'               => new sfValidatorDoctrineChoice(array('model' => $this->getModelName(), 'column' => 'id', 'required' => false)),
      'neighbourhood_id' => new sfValidatorInteger(),
      'name'             => new sfValidatorString(array('max_length' => 255, 'required' => false)),
      'address'          => new sfValidatorString(array('max_length' => 2147483647, 'required' => false)),
      'postcode'         => new sfValidatorString(array('max_length' => 20, 'required' => false)),
      'latitude'         => new sfValidatorNumber(),
      'longitude'        => new sfValidatorNumber(),
      'status'           => new sfValidatorInteger(),
      'source_id'        => new sfValidatorInteger(array('required' => false)),
      'event_count'      => new sfValidatorInteger(array('required' => false)),
      'alt_name'         => new sfValidatorString(array('max_length' => 255, 'required' => false)),
      'building_name'    => new sfValidatorString(array('max_length' => 255, 'required' => false)),
      'travel'           => new sfValidatorString(array('max_length' => 2147483647, 'required' => false)),
      'opening_times'    => new sfValidatorString(array('max_length' => 255, 'required' => false)),
      'url'              => new sfValidatorString(array('max_length' => 255, 'required' => false)),
      'phone'            => new sfValidatorString(array('max_length' => 255, 'required' => false)),
      'email'            => new sfValidatorString(array('max_length' => 255, 'required' => false)),
      'image_id'         => new sfValidatorInteger(array('required' => false)),
      'source'           => new sfValidatorString(array('max_length' => 15, 'required' => false)),
      'annotation'       => new sfValidatorString(array('max_length' => 2147483647, 'required' => false)),
    ));

    $this->widgetSchema->setNameFormat('sll_venue[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'SLLVenue';
  }

}
