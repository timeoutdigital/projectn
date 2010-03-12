<?php

/**
 * VendorEventCategory filter form base class.
 *
 * @package    sf_sandbox
 * @subpackage filter
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormFilterGeneratedTemplate.php 24171 2009-11-19 16:37:50Z Kris.Wallsmith $
 */
abstract class BaseVendorEventCategoryFormFilter extends BaseFormFilterDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'name'                => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'vendor_id'           => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('Vendor'), 'add_empty' => true)),
      'event_list'          => new sfWidgetFormDoctrineChoice(array('multiple' => true, 'model' => 'Event')),
      'event_category_list' => new sfWidgetFormDoctrineChoice(array('multiple' => true, 'model' => 'EventCategory')),
    ));

    $this->setValidators(array(
      'name'                => new sfValidatorPass(array('required' => false)),
      'vendor_id'           => new sfValidatorDoctrineChoice(array('required' => false, 'model' => $this->getRelatedModelName('Vendor'), 'column' => 'id')),
      'event_list'          => new sfValidatorDoctrineChoice(array('multiple' => true, 'model' => 'Event', 'required' => false)),
      'event_category_list' => new sfValidatorDoctrineChoice(array('multiple' => true, 'model' => 'EventCategory', 'required' => false)),
    ));

    $this->widgetSchema->setNameFormat('vendor_event_category_filters[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function addEventListColumnQuery(Doctrine_Query $query, $field, $values)
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
          ->andWhereIn('LinkingVendorEventCategory.event_id', $values);
  }

  public function addEventCategoryListColumnQuery(Doctrine_Query $query, $field, $values)
  {
    if (!is_array($values))
    {
      $values = array($values);
    }

    if (!count($values))
    {
      return;
    }

    $query->leftJoin('r.LinkingEventCategoryMapping LinkingEventCategoryMapping')
          ->andWhereIn('LinkingEventCategoryMapping.event_category_id', $values);
  }

  public function getModelName()
  {
    return 'VendorEventCategory';
  }

  public function getFields()
  {
    return array(
      'id'                  => 'Number',
      'name'                => 'Text',
      'vendor_id'           => 'ForeignKey',
      'event_list'          => 'ManyKey',
      'event_category_list' => 'ManyKey',
    );
  }
}
