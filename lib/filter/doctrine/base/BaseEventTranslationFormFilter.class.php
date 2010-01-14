<?php

/**
 * EventTranslation filter form base class.
 *
 * @package    sf_sandbox
 * @subpackage filter
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormFilterGeneratedTemplate.php 24171 2009-11-19 16:37:50Z Kris.Wallsmith $
 */
abstract class BaseEventTranslationFormFilter extends BaseFormFilterDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'name'              => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'vendor_category'   => new sfWidgetFormFilterInput(),
      'short_description' => new sfWidgetFormFilterInput(),
      'description'       => new sfWidgetFormFilterInput(),
      'booking_url'       => new sfWidgetFormFilterInput(),
    ));

    $this->setValidators(array(
      'name'              => new sfValidatorPass(array('required' => false)),
      'vendor_category'   => new sfValidatorPass(array('required' => false)),
      'short_description' => new sfValidatorPass(array('required' => false)),
      'description'       => new sfValidatorPass(array('required' => false)),
      'booking_url'       => new sfValidatorPass(array('required' => false)),
    ));

    $this->widgetSchema->setNameFormat('event_translation_filters[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'EventTranslation';
  }

  public function getFields()
  {
    return array(
      'id'                => 'Number',
      'name'              => 'Text',
      'vendor_category'   => 'Text',
      'short_description' => 'Text',
      'description'       => 'Text',
      'booking_url'       => 'Text',
      'lang'              => 'Text',
    );
  }
}
