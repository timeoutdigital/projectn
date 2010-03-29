<?php

/**
 * LinkingEventCategory form base class.
 *
 * @method LinkingEventCategory getObject() Returns the current form's model object
 *
 * @package    sf_sandbox
 * @subpackage form
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormGeneratedTemplate.php 24171 2009-11-19 16:37:50Z Kris.Wallsmith $
 */
abstract class BaseLinkingEventCategoryForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'                => new sfWidgetFormInputHidden(),
      'event_category_id' => new sfWidgetFormInputText(),
<<<<<<< HEAD:lib/form/doctrine/base/BaseLinkingEventCategoryForm.class.php
      'event_id'          => new sfWidgetFormInputText(),
=======
      'event_id'          => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('Event'), 'add_empty' => false)),
>>>>>>> 9c6972df7572956a8f7268cff10964681ad511b6:lib/form/doctrine/base/BaseLinkingEventCategoryForm.class.php
    ));

    $this->setValidators(array(
      'id'                => new sfValidatorDoctrineChoice(array('model' => $this->getModelName(), 'column' => 'id', 'required' => false)),
      'event_category_id' => new sfValidatorInteger(),
<<<<<<< HEAD:lib/form/doctrine/base/BaseLinkingEventCategoryForm.class.php
      'event_id'          => new sfValidatorInteger(),
=======
      'event_id'          => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('Event'))),
>>>>>>> 9c6972df7572956a8f7268cff10964681ad511b6:lib/form/doctrine/base/BaseLinkingEventCategoryForm.class.php
    ));

    $this->widgetSchema->setNameFormat('linking_event_category[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'LinkingEventCategory';
  }

}
