<?php

class LogImportErrorHelper
{
    public static function loadAndUnSerialize( &$action, sfWebRequest &$request )
    {
        if( is_numeric( $importErrorId = $request->getGetParameter( 'import_error_id' ) ) )
        {
            $logImportRecord = Doctrine::getTable( 'LogImportError' )->findOneById( $importErrorId );

            if( is_object( $logImportRecord ) && $logImportRecord instanceof LogImportError && isset( $logImportRecord[ 'serialized_object' ] ) )
            {
                $serializedObj = unserialize( $logImportRecord[ 'serialized_object' ] );

                if( isset( $serializedObj['id'] ) && is_numeric( $serializedObj['id'] ) )
                {
                    $record = Doctrine::getTable( get_class( $serializedObj ) )->findOneById( $serializedObj['id'] );
                    $record->merge( $serializedObj );
                }

                else $record = $serializedObj;
            }
        }
        else $action->getUser()->setFlash('error', 'Import Error ID Not Numeric' );

        return $record;
    }
}