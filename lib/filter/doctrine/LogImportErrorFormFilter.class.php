<?php

/**
 * LogImportError filter form.
 *
 * @package    sf_sandbox
 * @subpackage filter
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormFilterTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class LogImportErrorFormFilter extends BaseLogImportErrorFormFilter
{
  public function configure()
  {
    if (isset($this->widgetSchema['log_import_id']))
    {
        $this->widgetSchema['log_import_id']->setOption( 'order_by', array('id', 'desc')  );
    }
  }
}
