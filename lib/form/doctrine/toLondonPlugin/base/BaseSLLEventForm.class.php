<?php

/**
 * SLLEvent form base class.
 *
 * @method SLLEvent getObject() Returns the current form's model object
 *
 * @package    sf_sandbox
 * @subpackage form
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormGeneratedTemplate.php 24171 2009-11-19 16:37:50Z Kris.Wallsmith $
 */
abstract class BaseSLLEventForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'                   => new sfWidgetFormInputHidden(),
      'master_category_id'   => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('SLLCategory'), 'add_empty' => false)),
      'default_venue_id'     => new sfWidgetFormInputText(),
      'title'                => new sfWidgetFormTextarea(),
      'title_sort'           => new sfWidgetFormTextarea(),
      'free'                 => new sfWidgetFormInputText(),
      'image_id'             => new sfWidgetFormInputText(),
      'status'               => new sfWidgetFormInputText(),
      'recommended'          => new sfWidgetFormInputText(),
      'distinct_occurrences' => new sfWidgetFormInputText(),
      'travel'               => new sfWidgetFormTextarea(),
      'venue_prefix'         => new sfWidgetFormTextarea(),
      'search_priority'      => new sfWidgetFormInputText(),
      'source'               => new sfWidgetFormInputText(),
      'source_id'            => new sfWidgetFormInputText(),
      'source_event_id'      => new sfWidgetFormInputText(),
      'date_start'           => new sfWidgetFormDate(),
      'type'                 => new sfWidgetFormInputText(),
      'title_url'            => new sfWidgetFormInputText(),
      'suitable_for_kids'    => new sfWidgetFormInputText(),
      'seo_synopsis'         => new sfWidgetFormTextarea(),
      'annotation'           => new sfWidgetFormTextarea(),
      'phone'                => new sfWidgetFormTextarea(),
      'url'                  => new sfWidgetFormTextarea(),
      'price'                => new sfWidgetFormTextarea(),
      'price_cheapest'       => new sfWidgetFormInputText(),
      'discount'             => new sfWidgetFormInputText(),
      'keywords'             => new sfWidgetFormTextarea(),
      'tags'                 => new sfWidgetFormTextarea(),
      'date_end'             => new sfWidgetFormDate(),
      'opening_times'        => new sfWidgetFormTextarea(),
      'booking_ahead'        => new sfWidgetFormInputText(),
      'rescheduled'          => new sfWidgetFormInputText(),
      'extra'                => new sfWidgetFormInputText(),
      'cancelled'            => new sfWidgetFormInputText(),
      'flickr_tag'           => new sfWidgetFormInputText(),
      'advanced_text'        => new sfWidgetFormTextarea(),
      'date_created'         => new sfWidgetFormDate(),
      'date_modified'        => new sfWidgetFormDate(),
      'source_field'         => new sfWidgetFormInputText(),
    ));

    $this->setValidators(array(
      'id'                   => new sfValidatorDoctrineChoice(array('model' => $this->getModelName(), 'column' => 'id', 'required' => false)),
      'master_category_id'   => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('SLLCategory'))),
      'default_venue_id'     => new sfValidatorInteger(array('required' => false)),
      'title'                => new sfValidatorString(array('max_length' => 2147483647)),
      'title_sort'           => new sfValidatorString(array('max_length' => 2147483647)),
      'free'                 => new sfValidatorInteger(array('required' => false)),
      'image_id'             => new sfValidatorInteger(array('required' => false)),
      'status'               => new sfValidatorInteger(array('required' => false)),
      'recommended'          => new sfValidatorInteger(array('required' => false)),
      'distinct_occurrences' => new sfValidatorInteger(array('required' => false)),
      'travel'               => new sfValidatorString(array('max_length' => 2147483647)),
      'venue_prefix'         => new sfValidatorString(array('max_length' => 2147483647)),
      'search_priority'      => new sfValidatorInteger(array('required' => false)),
      'source'               => new sfValidatorString(array('max_length' => 15, 'required' => false)),
      'source_id'            => new sfValidatorInteger(),
      'source_event_id'      => new sfValidatorInteger(),
      'date_start'           => new sfValidatorDate(),
      'type'                 => new sfValidatorInteger(array('required' => false)),
      'title_url'            => new sfValidatorString(array('max_length' => 255, 'required' => false)),
      'suitable_for_kids'    => new sfValidatorInteger(array('required' => false)),
      'seo_synopsis'         => new sfValidatorString(array('max_length' => 2147483647, 'required' => false)),
      'annotation'           => new sfValidatorString(array('max_length' => 2147483647, 'required' => false)),
      'phone'                => new sfValidatorString(array('max_length' => 2147483647, 'required' => false)),
      'url'                  => new sfValidatorString(array('max_length' => 2147483647, 'required' => false)),
      'price'                => new sfValidatorString(array('max_length' => 2147483647, 'required' => false)),
      'price_cheapest'       => new sfValidatorNumber(array('required' => false)),
      'discount'             => new sfValidatorInteger(array('required' => false)),
      'keywords'             => new sfValidatorString(array('max_length' => 2147483647, 'required' => false)),
      'tags'                 => new sfValidatorString(array('max_length' => 2147483647, 'required' => false)),
      'date_end'             => new sfValidatorDate(array('required' => false)),
      'opening_times'        => new sfValidatorString(array('max_length' => 2147483647, 'required' => false)),
      'booking_ahead'        => new sfValidatorInteger(array('required' => false)),
      'rescheduled'          => new sfValidatorInteger(array('required' => false)),
      'extra'                => new sfValidatorInteger(array('required' => false)),
      'cancelled'            => new sfValidatorInteger(array('required' => false)),
      'flickr_tag'           => new sfValidatorString(array('max_length' => 255, 'required' => false)),
      'advanced_text'        => new sfValidatorString(array('max_length' => 2147483647, 'required' => false)),
      'date_created'         => new sfValidatorDate(array('required' => false)),
      'date_modified'        => new sfValidatorDate(array('required' => false)),
      'source_field'         => new sfValidatorString(array('max_length' => 25, 'required' => false)),
    ));

    $this->widgetSchema->setNameFormat('sll_event[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'SLLEvent';
  }

}
