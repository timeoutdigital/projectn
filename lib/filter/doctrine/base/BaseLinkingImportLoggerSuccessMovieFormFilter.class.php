<?php

/**
 * LinkingImportLoggerSuccessMovie filter form base class.
 *
 * @package    sf_sandbox
 * @subpackage filter
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormFilterGeneratedTemplate.php 24171 2009-11-19 16:37:50Z Kris.Wallsmith $
 */
abstract class BaseLinkingImportLoggerSuccessMovieFormFilter extends BaseFormFilterDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'import_logger_success_id' => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('ImportLoggerSuccess'), 'add_empty' => true)),
      'movie_id'                 => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('Movie'), 'add_empty' => true)),
    ));

    $this->setValidators(array(
      'import_logger_success_id' => new sfValidatorDoctrineChoice(array('required' => false, 'model' => $this->getRelatedModelName('ImportLoggerSuccess'), 'column' => 'id')),
      'movie_id'                 => new sfValidatorDoctrineChoice(array('required' => false, 'model' => $this->getRelatedModelName('Movie'), 'column' => 'id')),
    ));

    $this->widgetSchema->setNameFormat('linking_import_logger_success_movie_filters[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'LinkingImportLoggerSuccessMovie';
  }

  public function getFields()
  {
    return array(
      'id'                       => 'Number',
      'import_logger_success_id' => 'ForeignKey',
      'movie_id'                 => 'ForeignKey',
    );
  }
}
