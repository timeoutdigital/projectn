<?php

/**
 * LinkingVendorEventCategory form base class.
 *
 * @method LinkingVendorEventCategory getObject() Returns the current form's model object
 *
 * @package    sf_sandbox
 * @subpackage form
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormGeneratedTemplate.php 24171 2009-11-19 16:37:50Z Kris.Wallsmith $
 */
abstract class BaseLinkingVendorEventCategoryForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'                       => new sfWidgetFormInputHidden(),
      'vendor_event_category_id' => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('VendorEventCategory'), 'add_empty' => false)),
      'event_id'                 => new sfWidgetFormInputText(),
    ));

    $this->setValidators(array(
      'id'                       => new sfValidatorDoctrineChoice(array('model' => $this->getModelName(), 'column' => 'id', 'required' => false)),
      'vendor_event_category_id' => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('VendorEventCategory'))),
      'event_id'                 => new sfValidatorInteger(),
    ));

    $this->widgetSchema->setNameFormat('linking_vendor_event_category[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'LinkingVendorEventCategory';
  }

}
