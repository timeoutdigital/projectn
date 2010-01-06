<?php

/**
 * Movie form base class.
 *
 * @method Movie getObject() Returns the current form's model object
 *
 * @package    sf_sandbox
 * @subpackage form
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormGeneratedTemplate.php 24171 2009-11-19 16:37:50Z Kris.Wallsmith $
 */
abstract class BaseMovieForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'         => new sfWidgetFormInputHidden(),
      'vender_id'  => new sfWidgetFormInputText(),
      'name'       => new sfWidgetFormTextarea(),
      'genre'      => new sfWidgetFormTextarea(),
      'plot'       => new sfWidgetFormTextarea(),
      'review'     => new sfWidgetFormTextarea(),
      'url'        => new sfWidgetFormTextarea(),
      'rating'     => new sfWidgetFormInputText(),
      'age_rating' => new sfWidgetFormInputText(),
    ));

    $this->setValidators(array(
      'id'         => new sfValidatorDoctrineChoice(array('model' => $this->getModelName(), 'column' => 'id', 'required' => false)),
      'vender_id'  => new sfValidatorInteger(),
      'name'       => new sfValidatorString(array('max_length' => 256)),
      'genre'      => new sfValidatorString(array('max_length' => 256)),
      'plot'       => new sfValidatorString(array('max_length' => 65535, 'required' => false)),
      'review'     => new sfValidatorString(array('max_length' => 65535, 'required' => false)),
      'url'        => new sfValidatorString(array('max_length' => 1024, 'required' => false)),
      'rating'     => new sfValidatorNumber(array('required' => false)),
      'age_rating' => new sfValidatorString(array('max_length' => 32, 'required' => false)),
    ));

    $this->widgetSchema->setNameFormat('movie[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'Movie';
  }

}
