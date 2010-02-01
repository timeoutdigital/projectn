<?php

/**
 * SLLVenueCategoryInformation filter form base class.
 *
 * @package    sf_sandbox
 * @subpackage filter
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormFilterGeneratedTemplate.php 24171 2009-11-19 16:37:50Z Kris.Wallsmith $
 */
abstract class BaseSLLVenueCategoryInformationFormFilter extends BaseFormFilterDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'annotation'       => new sfWidgetFormFilterInput(),
      'price_export'     => new sfWidgetFormFilterInput(),
      'telephone_export' => new sfWidgetFormFilterInput(),
      'times_export'     => new sfWidgetFormFilterInput(),
      'url_export'       => new sfWidgetFormFilterInput(),
      'food_served'      => new sfWidgetFormFilterInput(),
      'free_venue'       => new sfWidgetFormFilterInput(),
      'late_night'       => new sfWidgetFormFilterInput(),
    ));

    $this->setValidators(array(
      'annotation'       => new sfValidatorPass(array('required' => false)),
      'price_export'     => new sfValidatorPass(array('required' => false)),
      'telephone_export' => new sfValidatorPass(array('required' => false)),
      'times_export'     => new sfValidatorPass(array('required' => false)),
      'url_export'       => new sfValidatorPass(array('required' => false)),
      'food_served'      => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'free_venue'       => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'late_night'       => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
    ));

    $this->widgetSchema->setNameFormat('sll_venue_category_information_filters[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'SLLVenueCategoryInformation';
  }

  public function getFields()
  {
    return array(
      'category_id'      => 'Number',
      'venue_id'         => 'Number',
      'annotation'       => 'Text',
      'price_export'     => 'Text',
      'telephone_export' => 'Text',
      'times_export'     => 'Text',
      'url_export'       => 'Text',
      'food_served'      => 'Number',
      'free_venue'       => 'Number',
      'late_night'       => 'Number',
    );
  }
}
