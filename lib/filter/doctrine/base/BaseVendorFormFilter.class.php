<?php

/**
 * Vendor filter form base class.
 *
 * @package    sf_sandbox
 * @subpackage filter
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormFilterGeneratedTemplate.php 24171 2009-11-19 16:37:50Z Kris.Wallsmith $
 */
abstract class BaseVendorFormFilter extends BaseFormFilterDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'city'                       => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'language'                   => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'time_zone'                  => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'inernational_dial_code'     => new sfWidgetFormFilterInput(),
      'airport_code'               => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'country_code'               => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'geo_boundries'              => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'geo_encode_look_up_pattern' => new sfWidgetFormFilterInput(),
      'created_at'                 => new sfWidgetFormFilterDate(array('from_date' => new sfWidgetFormDate(), 'to_date' => new sfWidgetFormDate(), 'with_empty' => false)),
      'updated_at'                 => new sfWidgetFormFilterDate(array('from_date' => new sfWidgetFormDate(), 'to_date' => new sfWidgetFormDate(), 'with_empty' => false)),
    ));

    $this->setValidators(array(
      'city'                       => new sfValidatorPass(array('required' => false)),
      'language'                   => new sfValidatorPass(array('required' => false)),
      'time_zone'                  => new sfValidatorPass(array('required' => false)),
      'inernational_dial_code'     => new sfValidatorPass(array('required' => false)),
      'airport_code'               => new sfValidatorPass(array('required' => false)),
      'country_code'               => new sfValidatorPass(array('required' => false)),
      'geo_boundries'              => new sfValidatorPass(array('required' => false)),
      'geo_encode_look_up_pattern' => new sfValidatorPass(array('required' => false)),
      'created_at'                 => new sfValidatorDateRange(array('required' => false, 'from_date' => new sfValidatorDateTime(array('required' => false, 'datetime_output' => 'Y-m-d 00:00:00')), 'to_date' => new sfValidatorDateTime(array('required' => false, 'datetime_output' => 'Y-m-d 23:59:59')))),
      'updated_at'                 => new sfValidatorDateRange(array('required' => false, 'from_date' => new sfValidatorDateTime(array('required' => false, 'datetime_output' => 'Y-m-d 00:00:00')), 'to_date' => new sfValidatorDateTime(array('required' => false, 'datetime_output' => 'Y-m-d 23:59:59')))),
    ));

    $this->widgetSchema->setNameFormat('vendor_filters[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'Vendor';
  }

  public function getFields()
  {
    return array(
      'id'                         => 'Number',
      'city'                       => 'Text',
      'language'                   => 'Text',
      'time_zone'                  => 'Text',
      'inernational_dial_code'     => 'Text',
      'airport_code'               => 'Text',
      'country_code'               => 'Text',
      'geo_boundries'              => 'Text',
      'geo_encode_look_up_pattern' => 'Text',
      'created_at'                 => 'Date',
      'updated_at'                 => 'Date',
    );
  }
}
