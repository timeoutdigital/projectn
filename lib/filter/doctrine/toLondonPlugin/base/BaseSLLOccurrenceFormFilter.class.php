<?php

/**
 * SLLOccurrence filter form base class.
 *
 * @package    sf_sandbox
 * @subpackage filter
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormFilterGeneratedTemplate.php 24171 2009-11-19 16:37:50Z Kris.Wallsmith $
 */
abstract class BaseSLLOccurrenceFormFilter extends BaseFormFilterDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'event_id'             => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('SLLEvent'), 'add_empty' => true)),
      'venue_id'             => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('SLLVenue'), 'add_empty' => true)),
      'date_start'           => new sfWidgetFormFilterDate(array('from_date' => new sfWidgetFormDate(), 'to_date' => new sfWidgetFormDate(), 'with_empty' => false)),
      'time_start'           => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'date_end'             => new sfWidgetFormFilterDate(array('from_date' => new sfWidgetFormDate(), 'to_date' => new sfWidgetFormDate(), 'with_empty' => false)),
      'time_end'             => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'annotation_behaviour' => new sfWidgetFormFilterInput(),
      'new'                  => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'last_chance'          => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'recommended'          => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'source'               => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'source_id'            => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'search_grouping_id'   => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'seo_synopsis'         => new sfWidgetFormFilterInput(),
      'title'                => new sfWidgetFormFilterInput(),
      'annotation'           => new sfWidgetFormFilterInput(),
      'price'                => new sfWidgetFormFilterInput(),
      'notable_title'        => new sfWidgetFormFilterInput(),
      'image_id'             => new sfWidgetFormFilterInput(),
      'page_views'           => new sfWidgetFormFilterInput(),
      'flickr_tag'           => new sfWidgetFormFilterInput(),
      'advanced_text'        => new sfWidgetFormFilterInput(),
      'date_created'         => new sfWidgetFormFilterDate(array('from_date' => new sfWidgetFormDate(), 'to_date' => new sfWidgetFormDate())),
      'date_modified'        => new sfWidgetFormFilterDate(array('from_date' => new sfWidgetFormDate(), 'to_date' => new sfWidgetFormDate())),
    ));

    $this->setValidators(array(
      'event_id'             => new sfValidatorDoctrineChoice(array('required' => false, 'model' => $this->getRelatedModelName('SLLEvent'), 'column' => 'id')),
      'venue_id'             => new sfValidatorDoctrineChoice(array('required' => false, 'model' => $this->getRelatedModelName('SLLVenue'), 'column' => 'id')),
      'date_start'           => new sfValidatorDateRange(array('required' => false, 'from_date' => new sfValidatorDate(array('required' => false)), 'to_date' => new sfValidatorDateTime(array('required' => false)))),
      'time_start'           => new sfValidatorPass(array('required' => false)),
      'date_end'             => new sfValidatorDateRange(array('required' => false, 'from_date' => new sfValidatorDate(array('required' => false)), 'to_date' => new sfValidatorDateTime(array('required' => false)))),
      'time_end'             => new sfValidatorPass(array('required' => false)),
      'annotation_behaviour' => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'new'                  => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'last_chance'          => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'recommended'          => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'source'               => new sfValidatorPass(array('required' => false)),
      'source_id'            => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'search_grouping_id'   => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'seo_synopsis'         => new sfValidatorPass(array('required' => false)),
      'title'                => new sfValidatorPass(array('required' => false)),
      'annotation'           => new sfValidatorPass(array('required' => false)),
      'price'                => new sfValidatorPass(array('required' => false)),
      'notable_title'        => new sfValidatorPass(array('required' => false)),
      'image_id'             => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'page_views'           => new sfValidatorPass(array('required' => false)),
      'flickr_tag'           => new sfValidatorPass(array('required' => false)),
      'advanced_text'        => new sfValidatorPass(array('required' => false)),
      'date_created'         => new sfValidatorDateRange(array('required' => false, 'from_date' => new sfValidatorDate(array('required' => false)), 'to_date' => new sfValidatorDateTime(array('required' => false)))),
      'date_modified'        => new sfValidatorDateRange(array('required' => false, 'from_date' => new sfValidatorDate(array('required' => false)), 'to_date' => new sfValidatorDateTime(array('required' => false)))),
    ));

    $this->widgetSchema->setNameFormat('sll_occurrence_filters[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'SLLOccurrence';
  }

  public function getFields()
  {
    return array(
      'id'                   => 'Number',
      'event_id'             => 'ForeignKey',
      'venue_id'             => 'ForeignKey',
      'date_start'           => 'Date',
      'time_start'           => 'Text',
      'date_end'             => 'Date',
      'time_end'             => 'Text',
      'annotation_behaviour' => 'Number',
      'new'                  => 'Number',
      'last_chance'          => 'Number',
      'recommended'          => 'Number',
      'source'               => 'Text',
      'source_id'            => 'Number',
      'search_grouping_id'   => 'Number',
      'seo_synopsis'         => 'Text',
      'title'                => 'Text',
      'annotation'           => 'Text',
      'price'                => 'Text',
      'notable_title'        => 'Text',
      'image_id'             => 'Number',
      'page_views'           => 'Text',
      'flickr_tag'           => 'Text',
      'advanced_text'        => 'Text',
      'date_created'         => 'Date',
      'date_modified'        => 'Date',
    );
  }
}
