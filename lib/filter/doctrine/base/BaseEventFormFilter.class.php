<?php

/**
 * Event filter form base class.
 *
 * @package    sf_sandbox
 * @subpackage filter
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormFilterGeneratedTemplate.php 24171 2009-11-19 16:37:50Z Kris.Wallsmith $
 */
abstract class BaseEventFormFilter extends BaseFormFilterDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'vendor_event_id'              => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'name'                         => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'short_description'            => new sfWidgetFormFilterInput(),
      'description'                  => new sfWidgetFormFilterInput(),
      'booking_url'                  => new sfWidgetFormFilterInput(),
      'url'                          => new sfWidgetFormFilterInput(),
      'price'                        => new sfWidgetFormFilterInput(),
      'rating'                       => new sfWidgetFormFilterInput(),
      'vendor_id'                    => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('Vendor'), 'add_empty' => true)),
      'created_at'                   => new sfWidgetFormFilterDate(array('from_date' => new sfWidgetFormDate(), 'to_date' => new sfWidgetFormDate(), 'with_empty' => false)),
      'updated_at'                   => new sfWidgetFormFilterDate(array('from_date' => new sfWidgetFormDate(), 'to_date' => new sfWidgetFormDate(), 'with_empty' => false)),
      'event_categories_list'        => new sfWidgetFormDoctrineChoice(array('multiple' => true, 'model' => 'EventCategory')),
      'vendor_event_categories_list' => new sfWidgetFormDoctrineChoice(array('multiple' => true, 'model' => 'VendorEventCategory')),
    ));

    $this->setValidators(array(
      'vendor_event_id'              => new sfValidatorPass(array('required' => false)),
      'name'                         => new sfValidatorPass(array('required' => false)),
      'short_description'            => new sfValidatorPass(array('required' => false)),
      'description'                  => new sfValidatorPass(array('required' => false)),
      'booking_url'                  => new sfValidatorPass(array('required' => false)),
      'url'                          => new sfValidatorPass(array('required' => false)),
      'price'                        => new sfValidatorPass(array('required' => false)),
      'rating'                       => new sfValidatorSchemaFilter('text', new sfValidatorNumber(array('required' => false))),
      'vendor_id'                    => new sfValidatorDoctrineChoice(array('required' => false, 'model' => $this->getRelatedModelName('Vendor'), 'column' => 'id')),
      'created_at'                   => new sfValidatorDateRange(array('required' => false, 'from_date' => new sfValidatorDateTime(array('required' => false, 'datetime_output' => 'Y-m-d 00:00:00')), 'to_date' => new sfValidatorDateTime(array('required' => false, 'datetime_output' => 'Y-m-d 23:59:59')))),
      'updated_at'                   => new sfValidatorDateRange(array('required' => false, 'from_date' => new sfValidatorDateTime(array('required' => false, 'datetime_output' => 'Y-m-d 00:00:00')), 'to_date' => new sfValidatorDateTime(array('required' => false, 'datetime_output' => 'Y-m-d 23:59:59')))),
      'event_categories_list'        => new sfValidatorDoctrineChoice(array('multiple' => true, 'model' => 'EventCategory', 'required' => false)),
      'vendor_event_categories_list' => new sfValidatorDoctrineChoice(array('multiple' => true, 'model' => 'VendorEventCategory', 'required' => false)),
    ));

    $this->widgetSchema->setNameFormat('event_filters[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function addEventCategoriesListColumnQuery(Doctrine_Query $query, $field, $values)
  {
    if (!is_array($values))
    {
      $values = array($values);
    }

    if (!count($values))
    {
      return;
    }

    $query->leftJoin('r.LinkingEventCategory LinkingEventCategory')
          ->andWhereIn('LinkingEventCategory.event_category_id', $values);
  }

  public function addVendorEventCategoriesListColumnQuery(Doctrine_Query $query, $field, $values)
  {
    if (!is_array($values))
    {
      $values = array($values);
    }

    if (!count($values))
    {
      return;
    }

    $query->leftJoin('r.LinkingVendorEventCategory LinkingVendorEventCategory')
          ->andWhereIn('LinkingVendorEventCategory.vendor_event_category_id', $values);
  }

  public function getModelName()
  {
    return 'Event';
  }

  public function getFields()
  {
    return array(
      'id'                           => 'Number',
      'vendor_event_id'              => 'Text',
      'name'                         => 'Text',
      'short_description'            => 'Text',
      'description'                  => 'Text',
      'booking_url'                  => 'Text',
      'url'                          => 'Text',
      'price'                        => 'Text',
      'rating'                       => 'Number',
      'vendor_id'                    => 'ForeignKey',
      'created_at'                   => 'Date',
      'updated_at'                   => 'Date',
      'event_categories_list'        => 'ManyKey',
      'vendor_event_categories_list' => 'ManyKey',
    );
  }
}
