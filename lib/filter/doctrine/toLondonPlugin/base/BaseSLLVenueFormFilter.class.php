<?php

/**
 * SLLVenue filter form base class.
 *
 * @package    sf_sandbox
 * @subpackage filter
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormFilterGeneratedTemplate.php 24171 2009-11-19 16:37:50Z Kris.Wallsmith $
 */
abstract class BaseSLLVenueFormFilter extends BaseFormFilterDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'neighbourhood_id' => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'name'             => new sfWidgetFormFilterInput(),
      'address'          => new sfWidgetFormFilterInput(),
      'postcode'         => new sfWidgetFormFilterInput(),
      'latitude'         => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'longitude'        => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'status'           => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'source_id'        => new sfWidgetFormFilterInput(),
      'event_count'      => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'alt_name'         => new sfWidgetFormFilterInput(),
      'building_name'    => new sfWidgetFormFilterInput(),
      'travel'           => new sfWidgetFormFilterInput(),
      'opening_times'    => new sfWidgetFormFilterInput(),
      'url'              => new sfWidgetFormFilterInput(),
      'phone'            => new sfWidgetFormFilterInput(),
      'email'            => new sfWidgetFormFilterInput(),
      'image_id'         => new sfWidgetFormFilterInput(),
      'source'           => new sfWidgetFormFilterInput(),
      'annotation'       => new sfWidgetFormFilterInput(),
    ));

    $this->setValidators(array(
      'neighbourhood_id' => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'name'             => new sfValidatorPass(array('required' => false)),
      'address'          => new sfValidatorPass(array('required' => false)),
      'postcode'         => new sfValidatorPass(array('required' => false)),
      'latitude'         => new sfValidatorSchemaFilter('text', new sfValidatorNumber(array('required' => false))),
      'longitude'        => new sfValidatorSchemaFilter('text', new sfValidatorNumber(array('required' => false))),
      'status'           => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'source_id'        => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'event_count'      => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'alt_name'         => new sfValidatorPass(array('required' => false)),
      'building_name'    => new sfValidatorPass(array('required' => false)),
      'travel'           => new sfValidatorPass(array('required' => false)),
      'opening_times'    => new sfValidatorPass(array('required' => false)),
      'url'              => new sfValidatorPass(array('required' => false)),
      'phone'            => new sfValidatorPass(array('required' => false)),
      'email'            => new sfValidatorPass(array('required' => false)),
      'image_id'         => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'source'           => new sfValidatorPass(array('required' => false)),
      'annotation'       => new sfValidatorPass(array('required' => false)),
    ));

    $this->widgetSchema->setNameFormat('sll_venue_filters[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'SLLVenue';
  }

  public function getFields()
  {
    return array(
      'id'               => 'Number',
      'neighbourhood_id' => 'Number',
      'name'             => 'Text',
      'address'          => 'Text',
      'postcode'         => 'Text',
      'latitude'         => 'Number',
      'longitude'        => 'Number',
      'status'           => 'Number',
      'source_id'        => 'Number',
      'event_count'      => 'Number',
      'alt_name'         => 'Text',
      'building_name'    => 'Text',
      'travel'           => 'Text',
      'opening_times'    => 'Text',
      'url'              => 'Text',
      'phone'            => 'Text',
      'email'            => 'Text',
      'image_id'         => 'Number',
      'source'           => 'Text',
      'annotation'       => 'Text',
    );
  }
}
