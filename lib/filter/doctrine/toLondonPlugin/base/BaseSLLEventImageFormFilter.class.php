<?php

/**
 * SLLEventImage filter form base class.
 *
 * @package    sf_sandbox
 * @subpackage filter
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormFilterGeneratedTemplate.php 24171 2009-11-19 16:37:50Z Kris.Wallsmith $
 */
abstract class BaseSLLEventImageFormFilter extends BaseFormFilterDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
    ));

    $this->setValidators(array(
    ));

    $this->widgetSchema->setNameFormat('sll_event_image_filters[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'SLLEventImage';
  }

  public function getFields()
  {
    return array(
      'item_id'    => 'Number',
      'item_type'  => 'Text',
      'image_id'   => 'Number',
      'sort_index' => 'Number',
    );
  }
}
