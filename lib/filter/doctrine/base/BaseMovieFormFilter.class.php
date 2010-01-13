<?php

/**
 * Movie filter form base class.
 *
 * @package    sf_sandbox
 * @subpackage filter
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormFilterGeneratedTemplate.php 24171 2009-11-19 16:37:50Z Kris.Wallsmith $
 */
abstract class BaseMovieFormFilter extends BaseFormFilterDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'vendor_id'  => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('Vendor'), 'add_empty' => true)),
      'name'       => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'genre'      => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'plot'       => new sfWidgetFormFilterInput(),
      'review'     => new sfWidgetFormFilterInput(),
      'url'        => new sfWidgetFormFilterInput(),
      'rating'     => new sfWidgetFormFilterInput(),
      'age_rating' => new sfWidgetFormFilterInput(),
    ));

    $this->setValidators(array(
      'vendor_id'  => new sfValidatorDoctrineChoice(array('required' => false, 'model' => $this->getRelatedModelName('Vendor'), 'column' => 'id')),
      'name'       => new sfValidatorPass(array('required' => false)),
      'genre'      => new sfValidatorPass(array('required' => false)),
      'plot'       => new sfValidatorPass(array('required' => false)),
      'review'     => new sfValidatorPass(array('required' => false)),
      'url'        => new sfValidatorPass(array('required' => false)),
      'rating'     => new sfValidatorSchemaFilter('text', new sfValidatorNumber(array('required' => false))),
      'age_rating' => new sfValidatorPass(array('required' => false)),
    ));

    $this->widgetSchema->setNameFormat('movie_filters[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'Movie';
  }

  public function getFields()
  {
    return array(
      'id'         => 'Number',
      'vendor_id'  => 'ForeignKey',
      'name'       => 'Text',
      'genre'      => 'Text',
      'plot'       => 'Text',
      'review'     => 'Text',
      'url'        => 'Text',
      'rating'     => 'Number',
      'age_rating' => 'Text',
    );
  }
}
