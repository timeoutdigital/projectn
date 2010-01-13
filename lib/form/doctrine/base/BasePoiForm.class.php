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
      'local_language'             => new sfWidgetFormInputText(),
      'poi_name'                   => new sfWidgetFormInputText(),
      'additional_address_details' => new sfWidgetFormInputText(),
      'country_code'               => new sfWidgetFormInputText(),
      'longitude'                  => new sfWidgetFormInputText(),
      'latitude'                   => new sfWidgetFormInputText(),
      'email'                      => new sfWidgetFormInputText(),
      'url'                        => new sfWidgetFormTextarea(),
      'phone'                      => new sfWidgetFormInputText(),
      'phone2'                     => new sfWidgetFormInputText(),
      'fax'                        => new sfWidgetFormInputText(),
      'language'                   => new sfWidgetFormTextarea(),
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
      'poi_category_id'            => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('PoiCategory'), 'add_empty' => false)),
      'vendor_id'                  => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('Vendor'), 'add_empty' => false)),
      'created_at'                 => new sfWidgetFormDateTime(),
      'updated_at'                 => new sfWidgetFormDateTime(),
    ));

    $this->setValidators(array(
      'id'                         => new sfValidatorDoctrineChoice(array('model' => $this->getModelName(), 'column' => 'id', 'required' => false)),
      'vendor_poi_id'              => new sfValidatorInteger(array('required' => false)),
      'local_language'             => new sfValidatorString(array('max_length' => 10, 'required' => false)),
      'poi_name'                   => new sfValidatorString(array('max_length' => 80, 'required' => false)),
      'additional_address_details' => new sfValidatorString(array('max_length' => 128, 'required' => false)),
      'country_code'               => new sfValidatorString(array('max_length' => 2)),
      'longitude'                  => new sfValidatorNumber(),
      'latitude'                   => new sfValidatorNumber(),
      'email'                      => new sfValidatorString(array('max_length' => 12, 'required' => false)),
      'url'                        => new sfValidatorString(array('max_length' => 1024, 'required' => false)),
      'phone'                      => new sfValidatorString(array('max_length' => 32, 'required' => false)),
      'phone2'                     => new sfValidatorString(array('max_length' => 32, 'required' => false)),
      'fax'                        => new sfValidatorString(array('max_length' => 32, 'required' => false)),
      'language'                   => new sfValidatorString(array('required' => false)),
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
      'poi_category_id'            => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('PoiCategory'))),
      'vendor_id'                  => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('Vendor'))),
      'created_at'                 => new sfValidatorDateTime(),
      'updated_at'                 => new sfValidatorDateTime(),
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

}
