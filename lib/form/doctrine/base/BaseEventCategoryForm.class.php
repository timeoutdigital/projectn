<?php

/**
 * EventCategory form base class.
 *
 * @method EventCategory getObject() Returns the current form's model object
 *
 * @package    sf_sandbox
 * @subpackage form
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormGeneratedTemplate.php 24171 2009-11-19 16:37:50Z Kris.Wallsmith $
 */
abstract class BaseEventCategoryForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'                         => new sfWidgetFormInputHidden(),
      'name'                       => new sfWidgetFormInputText(),
      'vendor_event_category_list' => new sfWidgetFormDoctrineChoice(array('multiple' => true, 'model' => 'VendorEventCategory')),
    ));

    $this->setValidators(array(
      'id'                         => new sfValidatorDoctrineChoice(array('model' => $this->getModelName(), 'column' => 'id', 'required' => false)),
      'name'                       => new sfValidatorString(array('max_length' => 50)),
      'vendor_event_category_list' => new sfValidatorDoctrineChoice(array('multiple' => true, 'model' => 'VendorEventCategory', 'required' => false)),
    ));

    $this->widgetSchema->setNameFormat('event_category[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'EventCategory';
  }

  public function updateDefaultsFromObject()
  {
    parent::updateDefaultsFromObject();

    if (isset($this->widgetSchema['vendor_event_category_list']))
    {
      $this->setDefault('vendor_event_category_list', $this->object->VendorEventCategory->getPrimaryKeys());
    }

  }

  protected function doSave($con = null)
  {
    $this->saveVendorEventCategoryList($con);

    parent::doSave($con);
  }

  public function saveVendorEventCategoryList($con = null)
  {
    if (!$this->isValid())
    {
      throw $this->getErrorSchema();
    }

    if (!isset($this->widgetSchema['vendor_event_category_list']))
    {
      // somebody has unset this widget
      return;
    }

    if (null === $con)
    {
      $con = $this->getConnection();
    }

    $existing = $this->object->VendorEventCategory->getPrimaryKeys();
    $values = $this->getValue('vendor_event_category_list');
    if (!is_array($values))
    {
      $values = array();
    }

    $unlink = array_diff($existing, $values);
    if (count($unlink))
    {
      $this->object->unlink('VendorEventCategory', array_values($unlink));
    }

    $link = array_diff($values, $existing);
    if (count($link))
    {
      $this->object->link('VendorEventCategory', array_values($link));
    }
  }

}
