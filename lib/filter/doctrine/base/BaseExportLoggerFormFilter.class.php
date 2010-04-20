<?php

/**
 * ExportLogger filter form base class.
 *
 * @package    sf_sandbox
 * @subpackage filter
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormFilterGeneratedInheritanceTemplate.php 24171 2009-11-19 16:37:50Z Kris.Wallsmith $
 */
abstract class BaseExportLoggerFormFilter extends LoggerFormFilter
{
  protected function setupInheritance()
  {
    parent::setupInheritance();

    $this->widgetSchema->setNameFormat('export_logger_filters[%s]');
  }

  public function getModelName()
  {
    return 'ExportLogger';
  }
}
