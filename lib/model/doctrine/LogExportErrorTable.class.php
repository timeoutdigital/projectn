<?php

class LogExportErrorTable extends Doctrine_Table
{
    /**
     * Get Log Export Errors by vendor, Model and Date range
     * @param int $vendorID
     * @param string $model
     * @param string $dateFrom
     * @param string $dateTo
     * @param int $doctrineHydrateType
     * @return mix
     */
    public function getLogExportErrors( $vendorID, $model, $dateFrom, $dateTo, $doctrineHydrateType = Doctrine_Core::HYDRATE_RECORD )
    {
        $q = Doctrine::getTable( 'LogExportError' )->createQuery('e')
        ->leftJoin('e.LogExport l ON l.id = e.log_export_id')
        ->where('e.model=?', $model )
        ->addWhere('l.vendor_id = ?', $vendorID )
        ->addWhere( 'l.created_at BETWEEN ? AND ?', array( $dateFrom, $dateTo ) );

        return $q->execute( array(), $doctrineHydrateType );
    }
}
