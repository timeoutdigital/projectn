<?php

require_once dirname(__FILE__).'/../lib/log_import_errorGeneratorConfiguration.class.php';
require_once dirname(__FILE__).'/../lib/log_import_errorGeneratorHelper.class.php';

/**
 * log_import_error actions.
 *
 * @package    sf_sandbox
 * @subpackage log_import_error
 * @author     Your name here
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class log_import_errorActions extends autoLog_import_errorActions
{
    public function executeListErrorInfo(sfWebRequest $request)
    {
        $logImportErrorId = $request->getParameterHolder()->get( 'id' );

        if ( $logImportErrorId == NULL )
        {
            $this->getUser()->setFlash( 'error', 'Error not found' );
            $this->redirect( 'log_import_error' );
        }
        else
        {
            $importError  = LogImportErrorHelper::getLogImportErrorRecordByErrorId( $logImportErrorId );
            $this->importError = $importError;
        }
    }
}
