<?php

/**
 * MovieOccurence form base class.
 *
 * @method MovieOccurence getObject() Returns the current form's model object
 *
 * @package    sf_sandbox
 * @subpackage form
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormGeneratedTemplate.php 24171 2009-11-19 16:37:50Z Kris.Wallsmith $
 */
abstract class BaseMovieOccurenceForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'         => new sfWidgetFormInputHidden(),
      'vender_id'  => new sfWidgetFormInputText(),
      'start_date' => new sfWidgetFormDate(),
      'end_date'   => new sfWidgetFormDate(),
      'start_time' => new sfWidgetFormTime(),
      'end_time'   => new sfWidgetFormTime(),
      'utf_offset' => new sfWidgetFormTime(),
      'movie_id'   => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('Movie'), 'add_empty' => false)),
      'poi_id'     => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('Poi'), 'add_empty' => false)),
    ));

    $this->setValidators(array(
      'id'         => new sfValidatorDoctrineChoice(array('model' => $this->getModelName(), 'column' => 'id', 'required' => false)),
      'vender_id'  => new sfValidatorInteger(),
      'start_date' => new sfValidatorDate(),
      'end_date'   => new sfValidatorDate(array('required' => false)),
      'start_time' => new sfValidatorTime(array('required' => false)),
      'end_time'   => new sfValidatorTime(array('required' => false)),
      'utf_offset' => new sfValidatorTime(),
      'movie_id'   => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('Movie'))),
      'poi_id'     => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('Poi'))),
    ));

    $this->widgetSchema->setNameFormat('movie_occurence[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'MovieOccurence';
  }

}
