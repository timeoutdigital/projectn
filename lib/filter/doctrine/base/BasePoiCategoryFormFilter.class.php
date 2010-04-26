<?php

/**
 * PoiCategory filter form base class.
 *
 * @package    sf_sandbox
 * @subpackage filter
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormFilterGeneratedTemplate.php 24171 2009-11-19 16:37:50Z Kris.Wallsmith $
 */
abstract class BasePoiCategoryFormFilter extends BaseFormFilterDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'name'                     => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'created_at'               => new sfWidgetFormFilterDate(array('from_date' => new sfWidgetFormDate(), 'to_date' => new sfWidgetFormDate(), 'with_empty' => false)),
      'updated_at'               => new sfWidgetFormFilterDate(array('from_date' => new sfWidgetFormDate(), 'to_date' => new sfWidgetFormDate(), 'with_empty' => false)),
      'poi_list'                 => new sfWidgetFormDoctrineChoice(array('multiple' => true, 'model' => 'Poi')),
      'parent_list'              => new sfWidgetFormDoctrineChoice(array('multiple' => true, 'model' => 'PoiCategory')),
      'vendor_poi_category_list' => new sfWidgetFormDoctrineChoice(array('multiple' => true, 'model' => 'VendorPoiCategory')),
      'children_list'            => new sfWidgetFormDoctrineChoice(array('multiple' => true, 'model' => 'PoiCategory')),
    ));

    $this->setValidators(array(
      'name'                     => new sfValidatorPass(array('required' => false)),
      'created_at'               => new sfValidatorDateRange(array('required' => false, 'from_date' => new sfValidatorDateTime(array('required' => false, 'datetime_output' => 'Y-m-d 00:00:00')), 'to_date' => new sfValidatorDateTime(array('required' => false, 'datetime_output' => 'Y-m-d 23:59:59')))),
      'updated_at'               => new sfValidatorDateRange(array('required' => false, 'from_date' => new sfValidatorDateTime(array('required' => false, 'datetime_output' => 'Y-m-d 00:00:00')), 'to_date' => new sfValidatorDateTime(array('required' => false, 'datetime_output' => 'Y-m-d 23:59:59')))),
      'poi_list'                 => new sfValidatorDoctrineChoice(array('multiple' => true, 'model' => 'Poi', 'required' => false)),
      'parent_list'              => new sfValidatorDoctrineChoice(array('multiple' => true, 'model' => 'PoiCategory', 'required' => false)),
      'vendor_poi_category_list' => new sfValidatorDoctrineChoice(array('multiple' => true, 'model' => 'VendorPoiCategory', 'required' => false)),
      'children_list'            => new sfValidatorDoctrineChoice(array('multiple' => true, 'model' => 'PoiCategory', 'required' => false)),
    ));

    $this->widgetSchema->setNameFormat('poi_category_filters[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function addPoiListColumnQuery(Doctrine_Query $query, $field, $values)
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
          ->andWhereIn('LinkingPoiCategory.poi_id', $values);
  }

  public function addParentListColumnQuery(Doctrine_Query $query, $field, $values)
  {
    if (!is_array($values))
    {
      $values = array($values);
    }

    if (!count($values))
    {
      return;
    }

    $query->leftJoin('r.PoiCategoryReference PoiCategoryReference')
          ->andWhereIn('PoiCategoryReference.parent_id', $values);
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

    $query->leftJoin('r.LinkingPoiCategoryMapping LinkingPoiCategoryMapping')
          ->andWhereIn('LinkingPoiCategoryMapping.vendor_poi_category_id', $values);
  }

  public function addChildrenListColumnQuery(Doctrine_Query $query, $field, $values)
  {
    if (!is_array($values))
    {
      $values = array($values);
    }

    if (!count($values))
    {
      return;
    }

    $query->leftJoin('r.PoiCategoryReference PoiCategoryReference')
          ->andWhereIn('PoiCategoryReference.child_id', $values);
  }

  public function getModelName()
  {
    return 'PoiCategory';
  }

  public function getFields()
  {
    return array(
      'id'                       => 'Number',
      'name'                     => 'Text',
      'created_at'               => 'Date',
      'updated_at'               => 'Date',
      'poi_list'                 => 'ManyKey',
      'parent_list'              => 'ManyKey',
      'vendor_poi_category_list' => 'ManyKey',
      'children_list'            => 'ManyKey',
    );
  }
}
