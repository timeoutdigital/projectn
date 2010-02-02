<?php

/**
 * Poi form base class.
 *
 * @method Poi getObject() Returns the current form's model object
 *
 * @package    sf_sandbox
 * @subpackage form
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormGeneratedTemplate.php 24171 2009-11-19 16:37:50Z Kris.Wallsmith $
 */
abstract class BasePoiForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'                         => new sfWidgetFormInputHidden(),
      'vendor_poi_id'              => new sfWidgetFormInputText(),
      'review_date'                => new sfWidgetFormInputText(),
      'local_language'             => new sfWidgetFormInputText(),
      'poi_name'                   => new sfWidgetFormInputText(),
      'house_no'                   => new sfWidgetFormInputText(),
      'street'                     => new sfWidgetFormInputText(),
      'city'                       => new sfWidgetFormInputText(),
      'district'                   => new sfWidgetFormInputText(),
      'country'                    => new sfWidgetFormInputText(),
      'additional_address_details' => new sfWidgetFormInputText(),
      'zips'                       => new sfWidgetFormInputText(),
      'longitude'                  => new sfWidgetFormInputText(),
      'latitude'                   => new sfWidgetFormInputText(),
      'email'                      => new sfWidgetFormInputText(),
      'url'                        => new sfWidgetFormTextarea(),
      'phone'                      => new sfWidgetFormInputText(),
      'phone2'                     => new sfWidgetFormInputText(),
      'fax'                        => new sfWidgetFormInputText(),
      'vendor_category'            => new sfWidgetFormInputText(),
      'keywords'                   => new sfWidgetFormTextarea(),
      'short_description'          => new sfWidgetFormTextarea(),
      'description'                => new sfWidgetFormTextarea(),
      'public_transport_links'     => new sfWidgetFormTextarea(),
      'price_information'          => new sfWidgetFormTextarea(),
      'openingtimes'               => new sfWidgetFormTextarea(),
      'star_rating'                => new sfWidgetFormInputText(),
      'rating'                     => new sfWidgetFormInputText(),
      'provider'                   => new sfWidgetFormTextarea(),
      'vendor_id'                  => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('Vendor'), 'add_empty' => false)),
      'created_at'                 => new sfWidgetFormDateTime(),
      'updated_at'                 => new sfWidgetFormDateTime(),
      'poi_categories_list'        => new sfWidgetFormDoctrineChoice(array('multiple' => true, 'model' => 'PoiCategory')),
      'vendor_poi_categories_list' => new sfWidgetFormDoctrineChoice(array('multiple' => true, 'model' => 'VendorPoiCategory')),
    ));

    $this->setValidators(array(
      'id'                         => new sfValidatorDoctrineChoice(array('model' => $this->getModelName(), 'column' => 'id', 'required' => false)),
      'vendor_poi_id'              => new sfValidatorString(array('max_length' => 10)),
      'review_date'                => new sfValidatorPass(array('required' => false)),
      'local_language'             => new sfValidatorString(array('max_length' => 10, 'required' => false)),
      'poi_name'                   => new sfValidatorString(array('max_length' => 80, 'required' => false)),
      'house_no'                   => new sfValidatorString(array('max_length' => 16, 'required' => false)),
      'street'                     => new sfValidatorString(array('max_length' => 128)),
      'city'                       => new sfValidatorString(array('max_length' => 32)),
      'district'                   => new sfValidatorString(array('max_length' => 128, 'required' => false)),
      'country'                    => new sfValidatorRegex(array('max_length' => 3, 'pattern' => '/^[a-zA-Z]{3}$/')),
      'additional_address_details' => new sfValidatorString(array('max_length' => 128, 'required' => false)),
      'zips'                       => new sfValidatorString(array('max_length' => 16, 'required' => false)),
      'longitude'                  => new sfValidatorNumber(),
      'latitude'                   => new sfValidatorNumber(),
      'email'                      => new sfValidatorString(array('max_length' => 12, 'required' => false)),
      'url'                        => new sfValidatorString(array('max_length' => 1024, 'required' => false)),
      'phone'                      => new sfValidatorString(array('max_length' => 32, 'required' => false)),
      'phone2'                     => new sfValidatorString(array('max_length' => 32, 'required' => false)),
      'fax'                        => new sfValidatorString(array('max_length' => 32, 'required' => false)),
      'vendor_category'            => new sfValidatorString(array('max_length' => 128, 'required' => false)),
      'keywords'                   => new sfValidatorString(array('max_length' => 512, 'required' => false)),
      'short_description'          => new sfValidatorString(array('max_length' => 2048, 'required' => false)),
      'description'                => new sfValidatorString(array('max_length' => 65535, 'required' => false)),
      'public_transport_links'     => new sfValidatorString(array('max_length' => 1024, 'required' => false)),
      'price_information'          => new sfValidatorString(array('max_length' => 512, 'required' => false)),
      'openingtimes'               => new sfValidatorString(array('max_length' => 512, 'required' => false)),
      'star_rating'                => new sfValidatorInteger(array('required' => false)),
      'rating'                     => new sfValidatorInteger(array('required' => false)),
      'provider'                   => new sfValidatorString(array('max_length' => 512, 'required' => false)),
      'vendor_id'                  => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('Vendor'))),
      'created_at'                 => new sfValidatorDateTime(),
      'updated_at'                 => new sfValidatorDateTime(),
      'poi_categories_list'        => new sfValidatorDoctrineChoice(array('multiple' => true, 'model' => 'PoiCategory', 'required' => false)),
      'vendor_poi_categories_list' => new sfValidatorDoctrineChoice(array('multiple' => true, 'model' => 'VendorPoiCategory', 'required' => false)),
    ));

    $this->widgetSchema->setNameFormat('poi[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'Poi';
  }

  public function updateDefaultsFromObject()
  {
    parent::updateDefaultsFromObject();

    if (isset($this->widgetSchema['poi_categories_list']))
    {
      $this->setDefault('poi_categories_list', $this->object->PoiCategories->getPrimaryKeys());
    }

    if (isset($this->widgetSchema['vendor_poi_categories_list']))
    {
      $this->setDefault('vendor_poi_categories_list', $this->object->VendorPoiCategories->getPrimaryKeys());
    }

  }

  protected function doSave($con = null)
  {
    $this->savePoiCategoriesList($con);
    $this->saveVendorPoiCategoriesList($con);

    parent::doSave($con);
  }

  public function savePoiCategoriesList($con = null)
  {
    if (!$this->isValid())
    {
      throw $this->getErrorSchema();
    }

    if (!isset($this->widgetSchema['poi_categories_list']))
    {
      // somebody has unset this widget
      return;
    }

    if (null === $con)
    {
      $con = $this->getConnection();
    }

    $existing = $this->object->PoiCategories->getPrimaryKeys();
    $values = $this->getValue('poi_categories_list');
    if (!is_array($values))
    {
      $values = array();
    }

    $unlink = array_diff($existing, $values);
    if (count($unlink))
    {
      $this->object->unlink('PoiCategories', array_values($unlink));
    }

    $link = array_diff($values, $existing);
    if (count($link))
    {
      $this->object->link('PoiCategories', array_values($link));
    }
  }

  public function saveVendorPoiCategoriesList($con = null)
  {
    if (!$this->isValid())
    {
      throw $this->getErrorSchema();
    }

    if (!isset($this->widgetSchema['vendor_poi_categories_list']))
    {
      // somebody has unset this widget
      return;
    }

    if (null === $con)
    {
      $con = $this->getConnection();
    }

    $existing = $this->object->VendorPoiCategories->getPrimaryKeys();
    $values = $this->getValue('vendor_poi_categories_list');
    if (!is_array($values))
    {
      $values = array();
    }

    $unlink = array_diff($existing, $values);
    if (count($unlink))
    {
      $this->object->unlink('VendorPoiCategories', array_values($unlink));
    }

    $link = array_diff($values, $existing);
    if (count($link))
    {
      $this->object->link('VendorPoiCategories', array_values($link));
    }
  }

}
