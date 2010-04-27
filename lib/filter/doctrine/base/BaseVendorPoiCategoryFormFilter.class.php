<?php

/**
 * VendorPoiCategory filter form base class.
 *
 * @package    sf_sandbox
 * @subpackage filter
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormFilterGeneratedTemplate.php 24171 2009-11-19 16:37:50Z Kris.Wallsmith $
 */
abstract class BaseVendorPoiCategoryFormFilter extends BaseFormFilterDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'name'              => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'vendor_id'         => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('Vendor'), 'add_empty' => true)),
      'poi_list'          => new sfWidgetFormDoctrineChoice(array('multiple' => true, 'model' => 'Poi')),
      'poi_category_list' => new sfWidgetFormDoctrineChoice(array('multiple' => true, 'model' => 'PoiCategory')),
    ));

    $this->setValidators(array(
      'name'              => new sfValidatorPass(array('required' => false)),
      'vendor_id'         => new sfValidatorDoctrineChoice(array('required' => false, 'model' => $this->getRelatedModelName('Vendor'), 'column' => 'id')),
      'poi_list'          => new sfValidatorDoctrineChoice(array('multiple' => true, 'model' => 'Poi', 'required' => false)),
      'poi_category_list' => new sfValidatorDoctrineChoice(array('multiple' => true, 'model' => 'PoiCategory', 'required' => false)),
    ));

    $this->widgetSchema->setNameFormat('vendor_poi_category_filters[%s]');

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

    $query->leftJoin('r.LinkingVendorPoiCategory LinkingVendorPoiCategory')
          ->andWhereIn('LinkingVendorPoiCategory.poi_id', $values);
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

    $query->leftJoin('r.LinkingPoiCategoryMapping LinkingPoiCategoryMapping')
          ->andWhereIn('LinkingPoiCategoryMapping.poi_category_id', $values);
  }

  public function getModelName()
  {
    return 'VendorPoiCategory';
  }

  public function getFields()
  {
    return array(
      'id'                => 'Number',
      'name'              => 'Text',
      'vendor_id'         => 'ForeignKey',
      'poi_list'          => 'ManyKey',
      'poi_category_list' => 'ManyKey',
    );
  }
}
