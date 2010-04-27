<?php

/**
 * SLLCategory filter form base class.
 *
 * @package    sf_sandbox
 * @subpackage filter
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormFilterGeneratedTemplate.php 24171 2009-11-19 16:37:50Z Kris.Wallsmith $
 */
abstract class BaseSLLCategoryFormFilter extends BaseFormFilterDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'parent_category_id' => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'name'               => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'status'             => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'name_url'           => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'tagline'            => new sfWidgetFormFilterInput(),
      'last_listing_date'  => new sfWidgetFormFilterDate(array('from_date' => new sfWidgetFormDate(), 'to_date' => new sfWidgetFormDate())),
      'lft'                => new sfWidgetFormFilterInput(),
      'rgt'                => new sfWidgetFormFilterInput(),
      'level'              => new sfWidgetFormFilterInput(),
    ));

    $this->setValidators(array(
      'parent_category_id' => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'name'               => new sfValidatorPass(array('required' => false)),
      'status'             => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'name_url'           => new sfValidatorPass(array('required' => false)),
      'tagline'            => new sfValidatorPass(array('required' => false)),
      'last_listing_date'  => new sfValidatorDateRange(array('required' => false, 'from_date' => new sfValidatorDate(array('required' => false)), 'to_date' => new sfValidatorDateTime(array('required' => false)))),
      'lft'                => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'rgt'                => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'level'              => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
    ));

    $this->widgetSchema->setNameFormat('sll_category_filters[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'SLLCategory';
  }

  public function getFields()
  {
    return array(
      'id'                 => 'Number',
      'parent_category_id' => 'Number',
      'name'               => 'Text',
      'status'             => 'Number',
      'name_url'           => 'Text',
      'tagline'            => 'Text',
      'last_listing_date'  => 'Date',
      'lft'                => 'Number',
      'rgt'                => 'Number',
      'level'              => 'Number',
    );
  }
}
