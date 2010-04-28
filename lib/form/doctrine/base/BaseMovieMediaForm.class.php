<?php

/**
 * MovieMedia form base class.
 *
 * @method MovieMedia getObject() Returns the current form's model object
 *
 * @package    sf_sandbox
 * @subpackage form
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormGeneratedInheritanceTemplate.php 24171 2009-11-19 16:37:50Z Kris.Wallsmith $
 */
abstract class BaseMovieMediaForm extends MediaForm
{
  protected function setupInheritance()
  {
    parent::setupInheritance();

    $this->widgetSchema   ['movie_id'] = new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('Movie'), 'add_empty' => false));
    $this->validatorSchema['movie_id'] = new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('Movie')));

    $this->widgetSchema->setNameFormat('movie_media[%s]');
  }

  public function getModelName()
  {
    return 'MovieMedia';
  }

}
