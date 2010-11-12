<?php

class LogImportTable extends LogTable
{

    /**
     * Get All LogImports via Vendor City Name
     *
     * @return Doctrine_Collection
     */
    public function getAllByCityName( $city_name )
    {
        $result = Doctrine_Query::create()
            ->from( 'LogImport li' )
            ->leftJoin( 'li.Vendor v' )
            ->leftJoin( 'li.LogImportCount lic' )
            ->where( 'v.city = ?', $city_name )
            ->orderBy( 'li.created_at ASC' )
            ->execute();

      // If City Not Found Return Empty Array;
      return ( $result !== false ) ? $result : array();
    }

    /**
     * Get Latest LogImport via Vendor City Name
     *
     * @return LogImport
     */
    public function getLatestOneByCityName( $city_name )
    {
        $result = Doctrine_Query::create()
            ->from( 'LogImport li' )
            ->leftJoin( 'li.Vendor v' )
            ->leftJoin( 'li.LogImportCount lic' )
            ->where( 'v.city = ?', $city_name )
            ->orderBy( 'li.id DESC' )
            ->limit( 1 )
            ->fetchOne();

      // If City Not Found Return Empty LogImport, so methods still return 0;
      return ( $result !== false ) ? $result : new LogImport();
    }

    /**
     * Get LogImport and LogImportCount from specified vendor + date from - date to
     * as mixed type (Doctrine_core::Hydrate_Type) result set
     * @param int $vendorID
     * @param string $dateFrom
     * @param string $dateTo
     * @param int $doctrineHydrateType
     * @return Mixed
     */
    public function getLogImportWithCountRecords( $vendorID, $dateFrom, $dateTo, $doctrineHydrateType = Doctrine_Core::HYDRATE_RECORD )
    {
        $q = Doctrine::getTable( 'LogImport' )->createQuery('l')
        ->leftJoin( 'l.LogImportCount lc ON l.id = lc.log_import_id' )
        ->where( 'l.vendor_id=?', $vendorID )
        ->addWhere( 'l.created_at BETWEEN ? AND ?', array( $dateFrom, $dateTo ) );
        
        return $q->execute( array(), $doctrineHydrateType );
    }

    /**
     * Get LogImport and LogImport Count for given date range
     * @param string $dateFrom
     * @param string $dateTo
     * @param int $doctrineHydrateType
     * @return mixed
     */
    public function getLogImportWithCountRecordsByDates( $dateFrom, $dateTo, $doctrineHydrateType = Doctrine_Core::HYDRATE_RECORD )
    {
        $q = Doctrine::getTable( 'LogImport' )->createQuery('l')
        ->leftJoin( 'l.LogImportCount lc ON l.id = lc.log_import_id' )
        ->where( 'l.created_at BETWEEN ? AND ?', array( $dateFrom, $dateTo ) );

        return $q->execute( array(), $doctrineHydrateType );
    }


    /**
     * Filter and get LogImport + LogImportCount by Model, Status and Date Ranges
     * @param string $modelName
     * @param mixed $status
     * @param string $dateFrom
     * @param string $dateTo
     * @param int $doctrineHydrateType
     * @return Mixed
     */
    public function getLogImportWithCountRecordsByModelAndStatus( $modelName, $status, $dateFrom, $dateTo, $doctrineHydrateType = Doctrine_Core::HYDRATE_RECORD )
    {
        $q = Doctrine::getTable( 'LogImport' )->createQuery('l')
        ->leftJoin( 'l.LogImportCount lc ON l.id = lc.log_import_id' )
        ->where( 'l.created_at BETWEEN ? AND ?', array( $dateFrom, $dateTo ) );

        // Add Model to where
        if( is_string( $modelName ) && trim($modelName) != '' )
        {
            $q->andWhere( 'lc.model = ?', $modelName );
        }

        // add Status to Where
        if( is_string( $status ) && trim( $status ) != '' )
        {
            // check for valid option
            $operationDefinition = Doctrine::getTable('LogImportCount')->getColumnDefinition('operation');
            $availableOperations = $operationDefinition['values'];

            if( in_array($status, $availableOperations ) )
                $q->andWhere( 'lc.operation = ?', $status );
        }

        // Execute and Retuen result
        return $q->execute( array(), $doctrineHydrateType );
    }
}
