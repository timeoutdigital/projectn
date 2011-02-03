<?php

class LogImportErrorHelper
{
    const MSG_INVALID_VENDOR        = 'Serialized Object Missing Vendor ID or Vendor Reference';
    const MSG_DESERIALIZE_ERR       = 'Unable to Deserialize Object';
    const MSG_INVALID_IMPORT_ERROR  = 'Import Error Not Found';
    const MSG_INVALID_REQUEST       = 'Import Error ID Not Numeric';
    const MSG_NO_MATCHING_DB_RECORD = 'No Matching Database Record Found';

    /**
     * Returns an array with the merged record as well as the values before 
     * the merge
     *
     * @param sfActions $action
     * @param sfRequest $request
     * @return array
     */
    public static function getMergedObject( &$action, &$request )
    {
        //@todo this if is not very nice, improvable??
        if( is_numeric( $importErrorId = $request->getGetParameter( 'import_error_id' ) ) ||
            is_numeric( $importErrorId = $request->getPostParameter( 'poi[import_error_id]' ) ) ||
            is_numeric( $importErrorId = $request->getPostParameter( 'event[import_error_id]' ) ) ||
            is_numeric( $importErrorId = $request->getPostParameter( 'movie[import_error_id]' ) ) )
        {
            $logImportErrorRecord = self::getLogImportErrorRecordByErrorId( $importErrorId );

            if( is_object( $logImportErrorRecord ) && $logImportErrorRecord instanceof LogImportError && isset( $logImportErrorRecord[ 'serialized_object' ] ) )
            {
                $serializedObj = unserialize( $logImportErrorRecord[ 'serialized_object' ] );

                if( is_object( $serializedObj ) && $serializedObj instanceof Doctrine_Record )
                {
                    $vendorReferenceColumn = 'vendor_' . strtolower( get_class( $serializedObj ) ) . '_id';

                    if( isset( $serializedObj[ $vendorReferenceColumn ] ) && !empty( $serializedObj[ $vendorReferenceColumn ] ) &&
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

                            $valuesBeforeMerge = $record->toArray();

                            $mergedRecord = clone $record;
                            $mergedRecord->merge( $serializedObj );

                            $mergedRecord->mapValue( 'import_error_id', $importErrorId);

                            $mergedRecord['id'] = $databaseRecordId;
                            $mergedRecord[ $vendorReferenceColumn ] = $databaseVendorReference;
                        }

                        else {
                            $action->getUser()->setFlash( 'notice', self::MSG_NO_MATCHING_DB_RECORD );
                            $mergedRecord = $serializedObj;
                        }
                    }

                    else $action->getUser()->setFlash( 'error', self::MSG_INVALID_VENDOR );
                }

                else $action->getUser()->setFlash( 'error', self::MSG_DESERIALIZE_ERR );
            }
            else $action->getUser()->setFlash( 'error', self::MSG_INVALID_IMPORT_ERROR );
        }
        else $action->getUser()->setFlash( 'error', self::MSG_INVALID_REQUEST );

        return array( 'record' => isset( $mergedRecord ) ? $mergedRecord : null, 'previousValues' => isset( $valuesBeforeMerge ) ? $valuesBeforeMerge : array() );
    }

    /**
     * Returns the unserialized Error Object By Log Import Error ID
     * 
     * @param integer $importErrorId
     * @return Doctrine_Record 
     */
    public static function getErrorObjectByImportErrorId( $importErrorId )
    {
        $logImportErrorRecord = self::getLogImportErrorRecordByErrorId( $importErrorId );
        
        if ( $logImportErrorRecord !== false )
        {
            return $logImportErrorRecord[ 'errorObject' ];
        }
        return false;
    }

    /**
     * Returns the Log Import Error Record by ID
     * 
     * @param integer $importErrorId
     * @return Doctrine_Record 
     */
    public static function getLogImportErrorRecordByErrorId( $importErrorId )
    {
        if ( is_numeric( $importErrorId ) )
        {
            return Doctrine::getTable( 'LogImportError' )->findOneById( $importErrorId );
        }
        return false;
    }
}