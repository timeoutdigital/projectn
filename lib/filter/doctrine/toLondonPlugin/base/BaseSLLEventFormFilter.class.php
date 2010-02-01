<?php

/**
 * SLLEvent filter form base class.
 *
 * @package    sf_sandbox
 * @subpackage filter
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormFilterGeneratedTemplate.php 24171 2009-11-19 16:37:50Z Kris.Wallsmith $
 */
abstract class BaseSLLEventFormFilter extends BaseFormFilterDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'master_category_id'   => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('SLLCategory'), 'add_empty' => true)),
      'default_venue_id'     => new sfWidgetFormFilterInput(),
      'title'                => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'title_sort'           => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'free'                 => new sfWidgetFormFilterInput(),
      'image_id'             => new sfWidgetFormFilterInput(),
      'status'               => new sfWidgetFormFilterInput(),
      'recommended'          => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'distinct_occurrences' => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'travel'               => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'venue_prefix'         => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'search_priority'      => new sfWidgetFormFilterInput(),
      'source'               => new sfWidgetFormFilterInput(),
      'source_id'            => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'source_event_id'      => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'date_start'           => new sfWidgetFormFilterDate(array('from_date' => new sfWidgetFormDate(), 'to_date' => new sfWidgetFormDate(), 'with_empty' => false)),
      'type'                 => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'title_url'            => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'suitable_for_kids'    => new sfWidgetFormFilterInput(),
      'seo_synopsis'         => new sfWidgetFormFilterInput(),
      'annotation'           => new sfWidgetFormFilterInput(),
      'phone'                => new sfWidgetFormFilterInput(),
      'url'                  => new sfWidgetFormFilterInput(),
      'price'                => new sfWidgetFormFilterInput(),
      'price_cheapest'       => new sfWidgetFormFilterInput(),
      'discount'             => new sfWidgetFormFilterInput(),
      'keywords'             => new sfWidgetFormFilterInput(),
      'tags'                 => new sfWidgetFormFilterInput(),
      'date_end'             => new sfWidgetFormFilterDate(array('from_date' => new sfWidgetFormDate(), 'to_date' => new sfWidgetFormDate())),
      'opening_times'        => new sfWidgetFormFilterInput(),
      'booking_ahead'        => new sfWidgetFormFilterInput(),
      'rescheduled'          => new sfWidgetFormFilterInput(),
      'extra'                => new sfWidgetFormFilterInput(),
      'cancelled'            => new sfWidgetFormFilterInput(),
      'flickr_tag'           => new sfWidgetFormFilterInput(),
      'advanced_text'        => new sfWidgetFormFilterInput(),
      'date_created'         => new sfWidgetFormFilterDate(array('from_date' => new sfWidgetFormDate(), 'to_date' => new sfWidgetFormDate())),
      'date_modified'        => new sfWidgetFormFilterDate(array('from_date' => new sfWidgetFormDate(), 'to_date' => new sfWidgetFormDate())),
      'source_field'         => new sfWidgetFormFilterInput(),
    ));

    $this->setValidators(array(
      'master_category_id'   => new sfValidatorDoctrineChoice(array('required' => false, 'model' => $this->getRelatedModelName('SLLCategory'), 'column' => 'id')),
      'default_venue_id'     => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'title'                => new sfValidatorPass(array('required' => false)),
      'title_sort'           => new sfValidatorPass(array('required' => false)),
      'free'                 => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'image_id'             => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'status'               => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'recommended'          => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'distinct_occurrences' => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'travel'               => new sfValidatorPass(array('required' => false)),
      'venue_prefix'         => new sfValidatorPass(array('required' => false)),
      'search_priority'      => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'source'               => new sfValidatorPass(array('required' => false)),
      'source_id'            => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'source_event_id'      => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'date_start'           => new sfValidatorDateRange(array('required' => false, 'from_date' => new sfValidatorDate(array('required' => false)), 'to_date' => new sfValidatorDateTime(array('required' => false)))),
      'type'                 => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'title_url'            => new sfValidatorPass(array('required' => false)),
      'suitable_for_kids'    => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'seo_synopsis'         => new sfValidatorPass(array('required' => false)),
      'annotation'           => new sfValidatorPass(array('required' => false)),
      'phone'                => new sfValidatorPass(array('required' => false)),
      'url'                  => new sfValidatorPass(array('required' => false)),
      'price'                => new sfValidatorPass(array('required' => false)),
      'price_cheapest'       => new sfValidatorSchemaFilter('text', new sfValidatorNumber(array('required' => false))),
      'discount'             => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'keywords'             => new sfValidatorPass(array('required' => false)),
      'tags'                 => new sfValidatorPass(array('required' => false)),
      'date_end'             => new sfValidatorDateRange(array('required' => false, 'from_date' => new sfValidatorDate(array('required' => false)), 'to_date' => new sfValidatorDateTime(array('required' => false)))),
      'opening_times'        => new sfValidatorPass(array('required' => false)),
      'booking_ahead'        => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'rescheduled'          => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'extra'                => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'cancelled'            => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'flickr_tag'           => new sfValidatorPass(array('required' => false)),
      'advanced_text'        => new sfValidatorPass(array('required' => false)),
      'date_created'         => new sfValidatorDateRange(array('required' => false, 'from_date' => new sfValidatorDate(array('required' => false)), 'to_date' => new sfValidatorDateTime(array('required' => false)))),
      'date_modified'        => new sfValidatorDateRange(array('required' => false, 'from_date' => new sfValidatorDate(array('required' => false)), 'to_date' => new sfValidatorDateTime(array('required' => false)))),
      'source_field'         => new sfValidatorPass(array('required' => false)),
    ));

    $this->widgetSchema->setNameFormat('sll_event_filters[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'SLLEvent';
  }

  public function getFields()
  {
    return array(
      'id'                   => 'Number',
      'master_category_id'   => 'ForeignKey',
      'default_venue_id'     => 'Number',
      'title'                => 'Text',
      'title_sort'           => 'Text',
      'free'                 => 'Number',
      'image_id'             => 'Number',
      'status'               => 'Number',
      'recommended'          => 'Number',
      'distinct_occurrences' => 'Number',
      'travel'               => 'Text',
      'venue_prefix'         => 'Text',
      'search_priority'      => 'Number',
      'source'               => 'Text',
      'source_id'            => 'Number',
      'source_event_id'      => 'Number',
      'date_start'           => 'Date',
      'type'                 => 'Number',
      'title_url'            => 'Text',
      'suitable_for_kids'    => 'Number',
      'seo_synopsis'         => 'Text',
      'annotation'           => 'Text',
      'phone'                => 'Text',
      'url'                  => 'Text',
      'price'                => 'Text',
      'price_cheapest'       => 'Number',
      'discount'             => 'Number',
      'keywords'             => 'Text',
      'tags'                 => 'Text',
      'date_end'             => 'Date',
      'opening_times'        => 'Text',
      'booking_ahead'        => 'Number',
      'rescheduled'          => 'Number',
      'extra'                => 'Number',
      'cancelled'            => 'Number',
      'flickr_tag'           => 'Text',
      'advanced_text'        => 'Text',
      'date_created'         => 'Date',
      'date_modified'        => 'Date',
      'source_field'         => 'Text',
    );
  }
}
