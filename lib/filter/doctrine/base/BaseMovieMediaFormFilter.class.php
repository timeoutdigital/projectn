<?php

/**
 * MovieMedia filter form base class.
 *
 * @package    sf_sandbox
 * @subpackage filter
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormFilterGeneratedInheritanceTemplate.php 24171 2009-11-19 16:37:50Z Kris.Wallsmith $
 */
abstract class BaseMovieMediaFormFilter extends MediaFormFilter
{
  protected function setupInheritance()
  {
    parent::setupInheritance();

    $this->widgetSchema   ['movie_id'] = new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('Movie'), 'add_empty' => true));
    $this->validatorSchema['movie_id'] = new sfValidatorDoctrineChoice(array('required' => false, 'model' => $this->getRelatedModelName('Movie'), 'column' => 'id'));

    $this->widgetSchema->setNameFormat('movie_media_filters[%s]');
  }

  public function getModelName()
  {
    return 'MovieMedia';
  }

  public function getFields()
  {
    return array_merge(parent::getFields(), array(
      'movie_id' => 'ForeignKey',
    ));
  }
}
