<?php

class LogImportErrorTable extends Doctrine_Table
{
    /**
     * Get Log Import Errors by vendor, Model and Date range
     * @param int $vendorID
     * @param string $model
     * @param string $dateFrom
     * @param string $dateTo
     * @param int $doctrineHydrateType
     * @return mix
     */
    public function getLogImportErrors( $vendorID, $model, $dateFrom, $dateTo, $doctrineHydrateType = Doctrine_Core::HYDRATE_RECORD )
    {
        $q = Doctrine::getTable( 'LogImportError' )->createQuery('e')
        ->leftJoin('e.LogImport l ON l.id = e.log_import_id')
        ->where('e.model=?', $model )
        ->addWhere('l.vendor_id = ?', $vendorID )
        ->addWhere( 'l.created_at BETWEEN ? AND ?', array( $dateFrom, $dateTo ) );

        return $q->execute( array(), $doctrineHydrateType );
    }
}
