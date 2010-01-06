<?php

/**
 * PoiTranslation filter form base class.
 *
 * @package    sf_sandbox
 * @subpackage filter
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormFilterGeneratedTemplate.php 24171 2009-11-19 16:37:50Z Kris.Wallsmith $
 */
abstract class BasePoiTranslationFormFilter extends BaseFormFilterDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'house_no'  => new sfWidgetFormFilterInput(),
      'street'    => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'city'      => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'district'  => new sfWidgetFormFilterInput(),
      'country'   => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'zips'      => new sfWidgetFormFilterInput(),
      'extension' => new sfWidgetFormFilterInput(),
    ));

    $this->setValidators(array(
      'house_no'  => new sfValidatorPass(array('required' => false)),
      'street'    => new sfValidatorPass(array('required' => false)),
      'city'      => new sfValidatorPass(array('required' => false)),
      'district'  => new sfValidatorPass(array('required' => false)),
      'country'   => new sfValidatorPass(array('required' => false)),
      'zips'      => new sfValidatorPass(array('required' => false)),
      'extension' => new sfValidatorPass(array('required' => false)),
    ));

    $this->widgetSchema->setNameFormat('poi_translation_filters[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'PoiTranslation';
  }

  public function getFields()
  {
    return array(
      'id'        => 'Number',
      'house_no'  => 'Text',
      'street'    => 'Text',
      'city'      => 'Text',
      'district'  => 'Text',
      'country'   => 'Text',
      'zips'      => 'Text',
      'extension' => 'Text',
      'lang'      => 'Text',
    );
  }
}
