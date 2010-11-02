<?php

class LogImportErrorHelper
{
    const MSG_INVALID_VENDOR        = 'Serialized Object Missing Vendor ID or Vendor Reference';
    const MSG_DESERIALIZE_ERR       = 'Unable to Deserialize Object';
    const MSG_INVALID_IMPORT_ERROR  = 'Import Error Not Found';
    const MSG_INVALID_REQUEST       = 'Import Error ID Not Numeric';
    const MSG_NO_MATCHING_DB_RECORD = 'No Matching Database Record Found';

    public static function loadAndUnSerialize( &$action, &$request )
    {
        if( is_numeric( $importErrorId = $request->getGetParameter( 'import_error_id' ) ) )
        {
            $logImportRecord = Doctrine::getTable( 'LogImportError' )->findOneById( $importErrorId );

            if( is_object( $logImportRecord ) && $logImportRecord instanceof LogImportError && isset( $logImportRecord[ 'serialized_object' ] ) )
            {
                $serializedObj = unserialize( $logImportRecord[ 'serialized_object' ] );

                if( is_object( $serializedObj ) && $serializedObj instanceof Doctrine_Record )
                {
                    $vendorReferenceColumn = 'vendor_' . strtolower( get_class( $serializedObj ) ) . '_id';

                    if( isset( $serializedObj[ $vendorReferenceColumn ] ) && is_numeric( $serializedObj[ $vendorReferenceColumn ] ) &&
                        isset( $serializedObj[ 'vendor_id' ] ) && is_numeric( $serializedObj[ 'vendor_id' ] ) )
                    {
                        $record = Doctrine::getTable( get_class( $serializedObj ) )
                                ->createQuery()
                                ->where( 'vendor_id = ?', $serializedObj[ 'vendor_id' ] )
                                ->addWhere( $vendorReferenceColumn . ' = ?', $serializedObj[ $vendorReferenceColumn ] )
                                ->limit( 1 )
                                ->fetchOne();

                        if( is_object( $record ) && $record instanceof Doctrine_Record )
                        {
                            $databaseRecordId = $record['id'];

                            $vendorReferenceColumn = 'vendor_' . strtolower( get_class( $record ) ) . '_id';
                            $databaseVendorReference = $record[ $vendorReferenceColumn ];

                            $record->merge( $serializedObj );
                            $record['id'] = $databaseRecordId;
                            $record[ $vendorReferenceColumn ] = $databaseVendorReference;
                        }

                        else {
                            $action->getUser()->setFlash( 'notice', self::MSG_NO_MATCHING_DB_RECORD );
                            $record = $serializedObj;
                        }
                    }

                    else $action->getUser()->setFlash( 'error', self::MSG_INVALID_VENDOR );
                }

                else $action->getUser()->setFlash( 'error', self::MSG_DESERIALIZE_ERR );
            }
            else $action->getUser()->setFlash( 'error', self::MSG_INVALID_IMPORT_ERROR );
        }
        else $action->getUser()->setFlash( 'error', self::MSG_INVALID_REQUEST );

        return isset( $record ) ? $record : null;
    }
}