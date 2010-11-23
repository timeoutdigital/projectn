<?php

class LogExportTable extends LogTable
{
    public function getLogExportWithCountRecordsForDate( $vendorID, $model, $date = null, $doctrineHydrateType = Doctrine_Core::HYDRATE_RECORD )
    {
        if( is_null( $date ) ) $date = time();

        $q = Doctrine::getTable( 'LogExport' )->createQuery('l')
        ->leftJoin('l.LogExportCount c ON l.id = c.log_export_id')
        ->where('c.model=?', $model )
	->addWhere( 'l.vendor_id=?', $vendorID )
        ->addWhere( 'l.created_at > ?', date( 'Y-m-d', $date ) );

        return $q->execute( array(), $doctrineHydrateType );
    }
    
    /**
     * Get Todays export total by vendor and model
     * @param int $vendorID
     * @param string $model
     * @param int $doctrineHydrateType
     * @return mix
     */
    public function getTodaysLogExportWithCountRecords( $vendorID, $model, $doctrineHydrateType = Doctrine_Core::HYDRATE_RECORD )
    {
        return $this->getLogExportWithCountRecordsForDate( $vendorID, $model, null, $doctrineHydrateType );
    }

    /**
     * Get LogExport and LogExportCount from specified vendor + date from - date to
     * as mixed type (Doctrine_core::Hydrate_Type) result set
     * @param int $vendorID
     * @param string $dateFrom
     * @param string $dateTo
     * @param int $doctrineHydrateType
     * @return Mixed
     */
    public function getLogExportWithCountRecords( $vendorID, $dateFrom, $dateTo, $doctrineHydrateType = Doctrine_Core::HYDRATE_RECORD )
    {
        $q = Doctrine::getTable( 'LogExport' )->createQuery('l')
        ->leftJoin( 'l.LogExportCount lc ON l.id = lc.log_export_id' )
        ->where( 'l.vendor_id=?', $vendorID )
        ->addWhere( 'l.created_at BETWEEN ? AND ?', array( $dateFrom, $dateTo ) );

        return $q->execute( array(), $doctrineHydrateType );
    }

    /**
     * Get logExport and LogExportCount between given date range for all vendors
     * @param string $dateFrom
     * @param string $dateTo
     * @param int $doctrineHydrateType
     * @return mixed
     */
    public function getLogExportWithCountRecordsByDates( $dateFrom, $dateTo, $doctrineHydrateType = Doctrine_Core::HYDRATE_RECORD )
    {
        $q = Doctrine::getTable( 'LogExport' )->createQuery('l')
        ->leftJoin( 'l.LogExportCount lc ON l.id = lc.log_export_id' )
        ->where( 'l.created_at BETWEEN ? AND ?', array( $dateFrom, $dateTo ) );

        return $q->execute( array(), $doctrineHydrateType );
    }
}
