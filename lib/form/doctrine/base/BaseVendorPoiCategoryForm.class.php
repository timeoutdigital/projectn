<?php

/**
 * VendorPoiCategory form base class.
 *
 * @method VendorPoiCategory getObject() Returns the current form's model object
 *
 * @package    sf_sandbox
 * @subpackage form
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormGeneratedTemplate.php 24171 2009-11-19 16:37:50Z Kris.Wallsmith $
 */
abstract class BaseVendorPoiCategoryForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'                => new sfWidgetFormInputHidden(),
      'name'              => new sfWidgetFormInputText(),
      'vendor_id'         => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('Vendor'), 'add_empty' => false)),
      'poi_list'          => new sfWidgetFormDoctrineChoice(array('multiple' => true, 'model' => 'Poi')),
      'poi_category_list' => new sfWidgetFormDoctrineChoice(array('multiple' => true, 'model' => 'PoiCategory')),
    ));

    $this->setValidators(array(
      'id'                => new sfValidatorDoctrineChoice(array('model' => $this->getModelName(), 'column' => 'id', 'required' => false)),
      'name'              => new sfValidatorString(array('max_length' => 255)),
      'vendor_id'         => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('Vendor'))),
      'poi_list'          => new sfValidatorDoctrineChoice(array('multiple' => true, 'model' => 'Poi', 'required' => false)),
      'poi_category_list' => new sfValidatorDoctrineChoice(array('multiple' => true, 'model' => 'PoiCategory', 'required' => false)),
    ));

    $this->widgetSchema->setNameFormat('vendor_poi_category[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'VendorPoiCategory';
  }

  public function updateDefaultsFromObject()
  {
    parent::updateDefaultsFromObject();

    if (isset($this->widgetSchema['poi_list']))
    {
      $this->setDefault('poi_list', $this->object->Poi->getPrimaryKeys());
    }

    if (isset($this->widgetSchema['poi_category_list']))
    {
      $this->setDefault('poi_category_list', $this->object->PoiCategory->getPrimaryKeys());
    }

  }

  protected function doSave($con = null)
  {
    $this->savePoiList($con);
    $this->savePoiCategoryList($con);

    parent::doSave($con);
  }

  public function savePoiList($con = null)
  {
    if (!$this->isValid())
    {
      throw $this->getErrorSchema();
    }

    if (!isset($this->widgetSchema['poi_list']))
    {
      // somebody has unset this widget
      return;
    }

    if (null === $con)
    {
      $con = $this->getConnection();
    }

    $existing = $this->object->Poi->getPrimaryKeys();
    $values = $this->getValue('poi_list');
    if (!is_array($values))
    {
      $values = array();
    }

    $unlink = array_diff($existing, $values);
    if (count($unlink))
    {
      $this->object->unlink('Poi', array_values($unlink));
    }

    $link = array_diff($values, $existing);
    if (count($link))
    {
      $this->object->link('Poi', array_values($link));
    }
  }

  public function savePoiCategoryList($con = null)
  {
    if (!$this->isValid())
    {
      throw $this->getErrorSchema();
    }

    if (!isset($this->widgetSchema['poi_category_list']))
    {
      // somebody has unset this widget
      return;
    }

    if (null === $con)
    {
      $con = $this->getConnection();
    }

    $existing = $this->object->PoiCategory->getPrimaryKeys();
    $values = $this->getValue('poi_category_list');
    if (!is_array($values))
    {
      $values = array();
    }

    $unlink = array_diff($existing, $values);
    if (count($unlink))
    {
      $this->object->unlink('PoiCategory', array_values($unlink));
    }

    $link = array_diff($values, $existing);
    if (count($link))
    {
      $this->object->link('PoiCategory', array_values($link));
    }
  }

}
