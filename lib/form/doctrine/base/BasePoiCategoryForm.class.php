<?php

/**
 * PoiCategory form base class.
 *
 * @method PoiCategory getObject() Returns the current form's model object
 *
 * @package    sf_sandbox
 * @subpackage form
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormGeneratedTemplate.php 24171 2009-11-19 16:37:50Z Kris.Wallsmith $
 */
abstract class BasePoiCategoryForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'                       => new sfWidgetFormInputHidden(),
      'name'                     => new sfWidgetFormInputText(),
      'created_at'               => new sfWidgetFormDateTime(),
      'updated_at'               => new sfWidgetFormDateTime(),
      'poi_list'                 => new sfWidgetFormDoctrineChoice(array('multiple' => true, 'model' => 'Poi')),
      'parent_list'              => new sfWidgetFormDoctrineChoice(array('multiple' => true, 'model' => 'PoiCategory')),
      'vendor_poi_category_list' => new sfWidgetFormDoctrineChoice(array('multiple' => true, 'model' => 'VendorPoiCategory')),
      'children_list'            => new sfWidgetFormDoctrineChoice(array('multiple' => true, 'model' => 'PoiCategory')),
    ));

    $this->setValidators(array(
      'id'                       => new sfValidatorDoctrineChoice(array('model' => $this->getModelName(), 'column' => 'id', 'required' => false)),
      'name'                     => new sfValidatorString(array('max_length' => 50)),
      'created_at'               => new sfValidatorDateTime(),
      'updated_at'               => new sfValidatorDateTime(),
      'poi_list'                 => new sfValidatorDoctrineChoice(array('multiple' => true, 'model' => 'Poi', 'required' => false)),
      'parent_list'              => new sfValidatorDoctrineChoice(array('multiple' => true, 'model' => 'PoiCategory', 'required' => false)),
      'vendor_poi_category_list' => new sfValidatorDoctrineChoice(array('multiple' => true, 'model' => 'VendorPoiCategory', 'required' => false)),
      'children_list'            => new sfValidatorDoctrineChoice(array('multiple' => true, 'model' => 'PoiCategory', 'required' => false)),
    ));

    $this->widgetSchema->setNameFormat('poi_category[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'PoiCategory';
  }

  public function updateDefaultsFromObject()
  {
    parent::updateDefaultsFromObject();

    if (isset($this->widgetSchema['poi_list']))
    {
      $this->setDefault('poi_list', $this->object->Poi->getPrimaryKeys());
    }

    if (isset($this->widgetSchema['parent_list']))
    {
      $this->setDefault('parent_list', $this->object->Parent->getPrimaryKeys());
    }

    if (isset($this->widgetSchema['vendor_poi_category_list']))
    {
      $this->setDefault('vendor_poi_category_list', $this->object->VendorPoiCategory->getPrimaryKeys());
    }

    if (isset($this->widgetSchema['children_list']))
    {
      $this->setDefault('children_list', $this->object->Children->getPrimaryKeys());
    }

  }

  protected function doSave($con = null)
  {
    $this->savePoiList($con);
    $this->saveParentList($con);
    $this->saveVendorPoiCategoryList($con);
    $this->saveChildrenList($con);

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

  public function saveParentList($con = null)
  {
    if (!$this->isValid())
    {
      throw $this->getErrorSchema();
    }

    if (!isset($this->widgetSchema['parent_list']))
    {
      // somebody has unset this widget
      return;
    }

    if (null === $con)
    {
      $con = $this->getConnection();
    }

    $existing = $this->object->Parent->getPrimaryKeys();
    $values = $this->getValue('parent_list');
    if (!is_array($values))
    {
      $values = array();
    }

    $unlink = array_diff($existing, $values);
    if (count($unlink))
    {
      $this->object->unlink('Parent', array_values($unlink));
    }

    $link = array_diff($values, $existing);
    if (count($link))
    {
      $this->object->link('Parent', array_values($link));
    }
  }

  public function saveVendorPoiCategoryList($con = null)
  {
    if (!$this->isValid())
    {
      throw $this->getErrorSchema();
    }

    if (!isset($this->widgetSchema['vendor_poi_category_list']))
    {
      // somebody has unset this widget
      return;
    }

    if (null === $con)
    {
      $con = $this->getConnection();
    }

    $existing = $this->object->VendorPoiCategory->getPrimaryKeys();
    $values = $this->getValue('vendor_poi_category_list');
    if (!is_array($values))
    {
      $values = array();
    }

    $unlink = array_diff($existing, $values);
    if (count($unlink))
    {
      $this->object->unlink('VendorPoiCategory', array_values($unlink));
    }

    $link = array_diff($values, $existing);
    if (count($link))
    {
      $this->object->link('VendorPoiCategory', array_values($link));
    }
  }

  public function saveChildrenList($con = null)
  {
    if (!$this->isValid())
    {
      throw $this->getErrorSchema();
    }

    if (!isset($this->widgetSchema['children_list']))
    {
      // somebody has unset this widget
      return;
    }

    if (null === $con)
    {
      $con = $this->getConnection();
    }

    $existing = $this->object->Children->getPrimaryKeys();
    $values = $this->getValue('children_list');
    if (!is_array($values))
    {
      $values = array();
    }

    $unlink = array_diff($existing, $values);
    if (count($unlink))
    {
      $this->object->unlink('Children', array_values($unlink));
    }

    $link = array_diff($values, $existing);
    if (count($link))
    {
      $this->object->link('Children', array_values($link));
    }
  }

}
