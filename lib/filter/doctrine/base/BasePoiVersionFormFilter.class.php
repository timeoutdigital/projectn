<?php

/**
 * PoiVersion filter form base class.
 *
 * @package    sf_sandbox
 * @subpackage filter
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormFilterGeneratedTemplate.php 24171 2009-11-19 16:37:50Z Kris.Wallsmith $
 */
abstract class BasePoiVersionFormFilter extends BaseFormFilterDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'review_date'                => new sfWidgetFormFilterDate(array('from_date' => new sfWidgetFormDate(), 'to_date' => new sfWidgetFormDate())),
      'vendor_poi_id'              => new sfWidgetFormFilterInput(),
      'local_language'             => new sfWidgetFormFilterInput(),
      'poi_name'                   => new sfWidgetFormFilterInput(),
      'house_no'                   => new sfWidgetFormFilterInput(),
      'street'                     => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'city'                       => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'district'                   => new sfWidgetFormFilterInput(),
      'country'                    => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'additional_address_details' => new sfWidgetFormFilterInput(),
      'zips'                       => new sfWidgetFormFilterInput(),
      'country_code'               => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'extension'                  => new sfWidgetFormFilterInput(),
      'longitude'                  => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'latitude'                   => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'email'                      => new sfWidgetFormFilterInput(),
      'url'                        => new sfWidgetFormFilterInput(),
      'phone'                      => new sfWidgetFormFilterInput(),
      'phone2'                     => new sfWidgetFormFilterInput(),
      'fax'                        => new sfWidgetFormFilterInput(),
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
      'vendor_id'                  => new sfWidgetFormFilterInput(array('with_empty' => false)),
    ));

    $this->setValidators(array(
      'review_date'                => new sfValidatorDateRange(array('required' => false, 'from_date' => new sfValidatorDateTime(array('required' => false, 'datetime_output' => 'Y-m-d 00:00:00')), 'to_date' => new sfValidatorDateTime(array('required' => false, 'datetime_output' => 'Y-m-d 23:59:59')))),
      'vendor_poi_id'              => new sfValidatorPass(array('required' => false)),
      'local_language'             => new sfValidatorPass(array('required' => false)),
      'poi_name'                   => new sfValidatorPass(array('required' => false)),
      'house_no'                   => new sfValidatorPass(array('required' => false)),
      'street'                     => new sfValidatorPass(array('required' => false)),
      'city'                       => new sfValidatorPass(array('required' => false)),
      'district'                   => new sfValidatorPass(array('required' => false)),
      'country'                    => new sfValidatorPass(array('required' => false)),
      'additional_address_details' => new sfValidatorPass(array('required' => false)),
      'zips'                       => new sfValidatorPass(array('required' => false)),
      'country_code'               => new sfValidatorPass(array('required' => false)),
      'extension'                  => new sfValidatorPass(array('required' => false)),
      'longitude'                  => new sfValidatorSchemaFilter('text', new sfValidatorNumber(array('required' => false))),
      'latitude'                   => new sfValidatorSchemaFilter('text', new sfValidatorNumber(array('required' => false))),
      'email'                      => new sfValidatorPass(array('required' => false)),
      'url'                        => new sfValidatorPass(array('required' => false)),
      'phone'                      => new sfValidatorPass(array('required' => false)),
      'phone2'                     => new sfValidatorPass(array('required' => false)),
      'fax'                        => new sfValidatorPass(array('required' => false)),
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
      'vendor_id'                  => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
    ));

    $this->widgetSchema->setNameFormat('poi_version_filters[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'PoiVersion';
  }

  public function getFields()
  {
    return array(
      'id'                         => 'Number',
      'review_date'                => 'Date',
      'vendor_poi_id'              => 'Text',
      'local_language'             => 'Text',
      'poi_name'                   => 'Text',
      'house_no'                   => 'Text',
      'street'                     => 'Text',
      'city'                       => 'Text',
      'district'                   => 'Text',
      'country'                    => 'Text',
      'additional_address_details' => 'Text',
      'zips'                       => 'Text',
      'country_code'               => 'Text',
      'extension'                  => 'Text',
      'longitude'                  => 'Number',
      'latitude'                   => 'Number',
      'email'                      => 'Text',
      'url'                        => 'Text',
      'phone'                      => 'Text',
      'phone2'                     => 'Text',
      'fax'                        => 'Text',
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
      'vendor_id'                  => 'Number',
      'version'                    => 'Number',
    );
  }
}
