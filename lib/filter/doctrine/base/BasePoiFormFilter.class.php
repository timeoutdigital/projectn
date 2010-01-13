<?php

/**
 * Poi filter form base class.
 *
 * @package    sf_sandbox
 * @subpackage filter
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormFilterGeneratedTemplate.php 24171 2009-11-19 16:37:50Z Kris.Wallsmith $
 */
abstract class BasePoiFormFilter extends BaseFormFilterDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'vendor_poi_id'              => new sfWidgetFormFilterInput(),
      'local_language'             => new sfWidgetFormFilterInput(),
      'poi_name'                   => new sfWidgetFormFilterInput(),
      'additional_address_details' => new sfWidgetFormFilterInput(),
      'country_code'               => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'longitude'                  => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'latitude'                   => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'email'                      => new sfWidgetFormFilterInput(),
      'url'                        => new sfWidgetFormFilterInput(),
      'phone'                      => new sfWidgetFormFilterInput(),
      'phone2'                     => new sfWidgetFormFilterInput(),
      'fax'                        => new sfWidgetFormFilterInput(),
      'language'                   => new sfWidgetFormFilterInput(),
      'vendor_category'            => new sfWidgetFormFilterInput(),
      'keywords'                   => new sfWidgetFormFilterInput(),
      'short_description'          => new sfWidgetFormFilterInput(),
      'description'                => new sfWidgetFormFilterInput(),
      'public_transport_links'     => new sfWidgetFormFilterInput(),
      'price_information'          => new sfWidgetFormFilterInput(),
      'openingtimes'               => new sfWidgetFormFilterInput(),
      'star_rating'                => new sfWidgetFormFilterInput(),
      'rating'                     => new sfWidgetFormFilterInput(),
      'provider'                   => new sfWidgetFormFilterInput(),
      'poi_category_id'            => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('PoiCategory'), 'add_empty' => true)),
      'poi_parent_category_id'     => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('PoiParentCategory'), 'add_empty' => true)),
      'vendor_id'                  => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('Vendor'), 'add_empty' => true)),
      'created_at'                 => new sfWidgetFormFilterDate(array('from_date' => new sfWidgetFormDate(), 'to_date' => new sfWidgetFormDate(), 'with_empty' => false)),
      'updated_at'                 => new sfWidgetFormFilterDate(array('from_date' => new sfWidgetFormDate(), 'to_date' => new sfWidgetFormDate(), 'with_empty' => false)),
    ));

    $this->setValidators(array(
      'vendor_poi_id'              => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'local_language'             => new sfValidatorPass(array('required' => false)),
      'poi_name'                   => new sfValidatorPass(array('required' => false)),
      'additional_address_details' => new sfValidatorPass(array('required' => false)),
      'country_code'               => new sfValidatorPass(array('required' => false)),
      'longitude'                  => new sfValidatorSchemaFilter('text', new sfValidatorNumber(array('required' => false))),
      'latitude'                   => new sfValidatorSchemaFilter('text', new sfValidatorNumber(array('required' => false))),
      'email'                      => new sfValidatorPass(array('required' => false)),
      'url'                        => new sfValidatorPass(array('required' => false)),
      'phone'                      => new sfValidatorPass(array('required' => false)),
      'phone2'                     => new sfValidatorPass(array('required' => false)),
      'fax'                        => new sfValidatorPass(array('required' => false)),
      'language'                   => new sfValidatorPass(array('required' => false)),
      'vendor_category'            => new sfValidatorPass(array('required' => false)),
      'keywords'                   => new sfValidatorPass(array('required' => false)),
      'short_description'          => new sfValidatorPass(array('required' => false)),
      'description'                => new sfValidatorPass(array('required' => false)),
      'public_transport_links'     => new sfValidatorPass(array('required' => false)),
      'price_information'          => new sfValidatorPass(array('required' => false)),
      'openingtimes'               => new sfValidatorPass(array('required' => false)),
      'star_rating'                => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'rating'                     => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'provider'                   => new sfValidatorPass(array('required' => false)),
      'poi_category_id'            => new sfValidatorDoctrineChoice(array('required' => false, 'model' => $this->getRelatedModelName('PoiCategory'), 'column' => 'id')),
      'poi_parent_category_id'     => new sfValidatorDoctrineChoice(array('required' => false, 'model' => $this->getRelatedModelName('PoiParentCategory'), 'column' => 'id')),
      'vendor_id'                  => new sfValidatorDoctrineChoice(array('required' => false, 'model' => $this->getRelatedModelName('Vendor'), 'column' => 'id')),
      'created_at'                 => new sfValidatorDateRange(array('required' => false, 'from_date' => new sfValidatorDateTime(array('required' => false, 'datetime_output' => 'Y-m-d 00:00:00')), 'to_date' => new sfValidatorDateTime(array('required' => false, 'datetime_output' => 'Y-m-d 23:59:59')))),
      'updated_at'                 => new sfValidatorDateRange(array('required' => false, 'from_date' => new sfValidatorDateTime(array('required' => false, 'datetime_output' => 'Y-m-d 00:00:00')), 'to_date' => new sfValidatorDateTime(array('required' => false, 'datetime_output' => 'Y-m-d 23:59:59')))),
    ));

    $this->widgetSchema->setNameFormat('poi_filters[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'Poi';
  }

  public function getFields()
  {
    return array(
      'id'                         => 'Number',
      'vendor_poi_id'              => 'Number',
      'local_language'             => 'Text',
      'poi_name'                   => 'Text',
      'additional_address_details' => 'Text',
      'country_code'               => 'Text',
      'longitude'                  => 'Number',
      'latitude'                   => 'Number',
      'email'                      => 'Text',
      'url'                        => 'Text',
      'phone'                      => 'Text',
      'phone2'                     => 'Text',
      'fax'                        => 'Text',
      'language'                   => 'Text',
      'vendor_category'            => 'Text',
      'keywords'                   => 'Text',
      'short_description'          => 'Text',
      'description'                => 'Text',
      'public_transport_links'     => 'Text',
      'price_information'          => 'Text',
      'openingtimes'               => 'Text',
      'star_rating'                => 'Number',
      'rating'                     => 'Number',
      'provider'                   => 'Text',
      'poi_category_id'            => 'ForeignKey',
      'poi_parent_category_id'     => 'ForeignKey',
      'vendor_id'                  => 'ForeignKey',
      'created_at'                 => 'Date',
      'updated_at'                 => 'Date',
    );
  }
}
