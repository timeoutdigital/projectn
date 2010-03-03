<<<<<<< .merge_file_J4guu0
<?php

/**
 * LinkingPoiCategory form base class.
 *
 * @method LinkingPoiCategory getObject() Returns the current form's model object
 *
 * @package    sf_sandbox
 * @subpackage form
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormGeneratedTemplate.php 24171 2009-11-19 16:37:50Z Kris.Wallsmith $
 */
abstract class BaseLinkingPoiCategoryForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'              => new sfWidgetFormInputHidden(),
      'poi_category_id' => new sfWidgetFormInputText(),
      'poi_id'          => new sfWidgetFormInputText(),
    ));

    $this->setValidators(array(
      'id'              => new sfValidatorDoctrineChoice(array('model' => $this->getModelName(), 'column' => 'id', 'required' => false)),
      'poi_category_id' => new sfValidatorInteger(),
      'poi_id'          => new sfValidatorInteger(),
    ));

    $this->widgetSchema->setNameFormat('linking_poi_category[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'LinkingPoiCategory';
  }

}
=======
<?php

/**
 * LinkingPoiCategory form base class.
 *
 * @method LinkingPoiCategory getObject() Returns the current form's model object
 *
 * @package    sf_sandbox
 * @subpackage form
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormGeneratedTemplate.php 24171 2009-11-19 16:37:50Z Kris.Wallsmith $
 */
abstract class BaseLinkingPoiCategoryForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'              => new sfWidgetFormInputHidden(),
      'poi_category_id' => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('PoiCategory'), 'add_empty' => false)),
      'poi_id'          => new sfWidgetFormInputText(),
    ));

    $this->setValidators(array(
      'id'              => new sfValidatorDoctrineChoice(array('model' => $this->getModelName(), 'column' => 'id', 'required' => false)),
      'poi_category_id' => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('PoiCategory'))),
      'poi_id'          => new sfValidatorInteger(),
    ));

    $this->widgetSchema->setNameFormat('linking_poi_category[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'LinkingPoiCategory';
  }

}
>>>>>>> .merge_file_ZfSZiX
