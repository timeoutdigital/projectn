<?php

/**
 * SLLOccurrence form base class.
 *
 * @method SLLOccurrence getObject() Returns the current form's model object
 *
 * @package    sf_sandbox
 * @subpackage form
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormGeneratedTemplate.php 24171 2009-11-19 16:37:50Z Kris.Wallsmith $
 */
abstract class BaseSLLOccurrenceForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'                   => new sfWidgetFormInputHidden(),
      'event_id'             => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('SLLEvent'), 'add_empty' => false)),
      'venue_id'             => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('SLLVenue'), 'add_empty' => false)),
      'date_start'           => new sfWidgetFormDate(),
      'time_start'           => new sfWidgetFormTime(),
      'date_end'             => new sfWidgetFormDate(),
      'time_end'             => new sfWidgetFormTime(),
      'annotation_behaviour' => new sfWidgetFormInputText(),
      'new'                  => new sfWidgetFormInputText(),
      'last_chance'          => new sfWidgetFormInputText(),
      'recommended'          => new sfWidgetFormInputText(),
      'source'               => new sfWidgetFormInputText(),
      'source_id'            => new sfWidgetFormInputText(),
      'search_grouping_id'   => new sfWidgetFormInputText(),
      'seo_synopsis'         => new sfWidgetFormInputText(),
      'title'                => new sfWidgetFormTextarea(),
      'annotation'           => new sfWidgetFormTextarea(),
      'price'                => new sfWidgetFormTextarea(),
      'notable_title'        => new sfWidgetFormInputText(),
      'image_id'             => new sfWidgetFormInputText(),
      'page_views'           => new sfWidgetFormInputText(),
      'flickr_tag'           => new sfWidgetFormInputText(),
      'advanced_text'        => new sfWidgetFormTextarea(),
      'date_created'         => new sfWidgetFormDate(),
      'date_modified'        => new sfWidgetFormDate(),
    ));

    $this->setValidators(array(
      'id'                   => new sfValidatorDoctrineChoice(array('model' => $this->getModelName(), 'column' => 'id', 'required' => false)),
      'event_id'             => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('SLLEvent'))),
      'venue_id'             => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('SLLVenue'))),
      'date_start'           => new sfValidatorDate(),
      'time_start'           => new sfValidatorTime(),
      'date_end'             => new sfValidatorDate(),
      'time_end'             => new sfValidatorTime(),
      'annotation_behaviour' => new sfValidatorInteger(array('required' => false)),
      'new'                  => new sfValidatorInteger(),
      'last_chance'          => new sfValidatorInteger(),
      'recommended'          => new sfValidatorInteger(),
      'source'               => new sfValidatorString(array('max_length' => 15)),
      'source_id'            => new sfValidatorInteger(),
      'search_grouping_id'   => new sfValidatorInteger(array('required' => false)),
      'seo_synopsis'         => new sfValidatorString(array('max_length' => 255, 'required' => false)),
      'title'                => new sfValidatorString(array('max_length' => 2147483647, 'required' => false)),
      'annotation'           => new sfValidatorString(array('max_length' => 2147483647, 'required' => false)),
      'price'                => new sfValidatorString(array('max_length' => 2147483647, 'required' => false)),
      'notable_title'        => new sfValidatorString(array('max_length' => 30, 'required' => false)),
      'image_id'             => new sfValidatorInteger(array('required' => false)),
      'page_views'           => new sfValidatorPass(array('required' => false)),
      'flickr_tag'           => new sfValidatorString(array('max_length' => 255, 'required' => false)),
      'advanced_text'        => new sfValidatorString(array('max_length' => 2147483647, 'required' => false)),
      'date_created'         => new sfValidatorDate(array('required' => false)),
      'date_modified'        => new sfValidatorDate(array('required' => false)),
    ));

    $this->widgetSchema->setNameFormat('sll_occurrence[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'SLLOccurrence';
  }

}
