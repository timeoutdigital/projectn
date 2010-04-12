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
      'vendor_poi_id'              => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'review_date'                => new sfWidgetFormFilterDate(array('from_date' => new sfWidgetFormDate(), 'to_date' => new sfWidgetFormDate())),
      'local_language'             => new sfWidgetFormFilterInput(),
      'poi_name'                   => new sfWidgetFormFilterInput(),
      'house_no'                   => new sfWidgetFormFilterInput(),
      'street'                     => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'city'                       => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'district'                   => new sfWidgetFormFilterInput(),
      'country'                    => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'additional_address_details' => new sfWidgetFormFilterInput(),
      'zips'                       => new sfWidgetFormFilterInput(),
      'longitude'                  => new sfWidgetFormFilterInput(),
      'latitude'                   => new sfWidgetFormFilterInput(),
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
      'vendor_id'                  => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('Vendor'), 'add_empty' => true)),
      'created_at'                 => new sfWidgetFormFilterDate(array('from_date' => new sfWidgetFormDate(), 'to_date' => new sfWidgetFormDate(), 'with_empty' => false)),
      'updated_at'                 => new sfWidgetFormFilterDate(array('from_date' => new sfWidgetFormDate(), 'to_date' => new sfWidgetFormDate(), 'with_empty' => false)),
      'poi_category_list'          => new sfWidgetFormDoctrineChoice(array('multiple' => true, 'model' => 'PoiCategory')),
      'vendor_poi_category_list'   => new sfWidgetFormDoctrineChoice(array('multiple' => true, 'model' => 'VendorPoiCategory')),
      'import_logger_success_list' => new sfWidgetFormDoctrineChoice(array('multiple' => true, 'model' => 'ImportLoggerSuccess')),
    ));

    $this->setValidators(array(
      'vendor_poi_id'              => new sfValidatorPass(array('required' => false)),
      'review_date'                => new sfValidatorDateRange(array('required' => false, 'from_date' => new sfValidatorDateTime(array('required' => false, 'datetime_output' => 'Y-m-d 00:00:00')), 'to_date' => new sfValidatorDateTime(array('required' => false, 'datetime_output' => 'Y-m-d 23:59:59')))),
      'local_language'             => new sfValidatorPass(array('required' => false)),
      'poi_name'                   => new sfValidatorPass(array('required' => false)),
      'house_no'                   => new sfValidatorPass(array('required' => false)),
      'street'                     => new sfValidatorPass(array('required' => false)),
      'city'                       => new sfValidatorPass(array('required' => false)),
      'district'                   => new sfValidatorPass(array('required' => false)),
      'country'                    => new sfValidatorPass(array('required' => false)),
      'additional_address_details' => new sfValidatorPass(array('required' => false)),
      'zips'                       => new sfValidatorPass(array('required' => false)),
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
      'vendor_id'                  => new sfValidatorDoctrineChoice(array('required' => false, 'model' => $this->getRelatedModelName('Vendor'), 'column' => 'id')),
      'created_at'                 => new sfValidatorDateRange(array('required' => false, 'from_date' => new sfValidatorDateTime(array('required' => false, 'datetime_output' => 'Y-m-d 00:00:00')), 'to_date' => new sfValidatorDateTime(array('required' => false, 'datetime_output' => 'Y-m-d 23:59:59')))),
      'updated_at'                 => new sfValidatorDateRange(array('required' => false, 'from_date' => new sfValidatorDateTime(array('required' => false, 'datetime_output' => 'Y-m-d 00:00:00')), 'to_date' => new sfValidatorDateTime(array('required' => false, 'datetime_output' => 'Y-m-d 23:59:59')))),
      'poi_category_list'          => new sfValidatorDoctrineChoice(array('multiple' => true, 'model' => 'PoiCategory', 'required' => false)),
      'vendor_poi_category_list'   => new sfValidatorDoctrineChoice(array('multiple' => true, 'model' => 'VendorPoiCategory', 'required' => false)),
      'import_logger_success_list' => new sfValidatorDoctrineChoice(array('multiple' => true, 'model' => 'ImportLoggerSuccess', 'required' => false)),
    ));

    $this->widgetSchema->setNameFormat('poi_filters[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function addPoiCategoryListColumnQuery(Doctrine_Query $query, $field, $values)
  {
    if (!is_array($values))
    {
      $values = array($values);
    }

    if (!count($values))
    {
      return;
    }

    $query->leftJoin('r.LinkingPoiCategory LinkingPoiCategory')
          ->andWhereIn('LinkingPoiCategory.poi_category_id', $values);
  }

  public function addVendorPoiCategoryListColumnQuery(Doctrine_Query $query, $field, $values)
  {
    if (!is_array($values))
    {
      $values = array($values);
    }

    if (!count($values))
    {
      return;
    }

    $query->leftJoin('r.LinkingVendorPoiCategory LinkingVendorPoiCategory')
          ->andWhereIn('LinkingVendorPoiCategory.vendor_poi_category_id', $values);
  }

  public function addImportLoggerSuccessListColumnQuery(Doctrine_Query $query, $field, $values)
  {
    if (!is_array($values))
    {
      $values = array($values);
    }

    if (!count($values))
    {
      return;
    }

    $query->leftJoin('r.LinkingImportLoggerSuccessPoi LinkingImportLoggerSuccessPoi')
          ->andWhereIn('LinkingImportLoggerSuccessPoi.import_logger_success_id', $values);
  }

  public function getModelName()
  {
    return 'Poi';
  }

  public function getFields()
  {
    return array(
      'id'                         => 'Number',
      'vendor_poi_id'              => 'Text',
      'review_date'                => 'Date',
      'local_language'             => 'Text',
      'poi_name'                   => 'Text',
      'house_no'                   => 'Text',
      'street'                     => 'Text',
      'city'                       => 'Text',
      'district'                   => 'Text',
      'country'                    => 'Text',
      'additional_address_details' => 'Text',
      'zips'                       => 'Text',
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
      'vendor_id'                  => 'ForeignKey',
      'created_at'                 => 'Date',
      'updated_at'                 => 'Date',
      'poi_category_list'          => 'ManyKey',
      'vendor_poi_category_list'   => 'ManyKey',
      'import_logger_success_list' => 'ManyKey',
    );
  }
}
