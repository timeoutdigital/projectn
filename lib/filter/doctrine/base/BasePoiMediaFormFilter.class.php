<?php

/**
 * PoiMedia filter form base class.
 *
 * @package    sf_sandbox
 * @subpackage filter
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormFilterGeneratedInheritanceTemplate.php 24171 2009-11-19 16:37:50Z Kris.Wallsmith $
 */
abstract class BasePoiMediaFormFilter extends MediaFormFilter
{
  protected function setupInheritance()
  {
    parent::setupInheritance();

    $this->widgetSchema   ['poi_id'] = new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('Poi'), 'add_empty' => true));
    $this->validatorSchema['poi_id'] = new sfValidatorDoctrineChoice(array('required' => false, 'model' => $this->getRelatedModelName('Poi'), 'column' => 'id'));

    $this->widgetSchema->setNameFormat('poi_media_filters[%s]');
  }

  public function getModelName()
  {
    return 'PoiMedia';
  }

  public function getFields()
  {
    return array_merge(parent::getFields(), array(
      'poi_id' => 'ForeignKey',
    ));
  }
}
