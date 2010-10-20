<?php

class LogExportTable extends LogTable
{
    /**
     * Get Todays export total by vendor and model
     * @param int $vendorID
     * @param string $model
     * @param int $doctrineHydrateType
     * @return mix
     */
    public function getTodaysLogExportCount( $vendorID, $model, $doctrineHydrateType = Doctrine_Core::HYDRATE_RECORD )
    {
        $q = Doctrine::getTable( 'LogExport' )->createQuery('l')
        ->leftJoin('l.LogExportCount c ON l.id = c.log_export_id')
        ->where('c.model=?', $model )
	->addWhere( 'l.vendor_id=?', $vendorID )
        ->limit(1)
        ->addWhere( 'l.created_at > DATE( NOW() )' );

        return $q->execute( array(), $doctrineHydrateType );
    }
}
