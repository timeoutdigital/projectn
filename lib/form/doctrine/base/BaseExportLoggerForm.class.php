<?php

/**
 * ExportLogger form base class.
 *
 * @method ExportLogger getObject() Returns the current form's model object
 *
 * @package    sf_sandbox
 * @subpackage form
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormGeneratedInheritanceTemplate.php 24171 2009-11-19 16:37:50Z Kris.Wallsmith $
 */
abstract class BaseExportLoggerForm extends LoggerForm
{
  protected function setupInheritance()
  {
    parent::setupInheritance();

    $this->widgetSchema->setNameFormat('export_logger[%s]');
  }

  public function getModelName()
  {
    return 'ExportLogger';
  }

}
