<?php

class PoiTable extends Doctrine_Table
{

    /**
     * Get Poi by vendor name
     *
     * @return <type>
     */
    public function getPoiByVendor($name)
    {
        $q = Doctrine_Query::create()
            ->from('Poi p')
            ->leftJoin('p.Vendor v')
            ->where('v.city=?', $name);

      return $q->execute();
    }

    /**
     * Get the name of the vendor's uid fieldname, this is a temporary solution
     * @todo rename Poi, Events, Movies etc to have vendor_uid field instead
     * of vendor_<model name>_id to allow polymorphism
     *
     * @return string
     */
    public function getVendorUidFieldName()
    {
      return 'vendor_poi_id';
    }

    /**
     *
     * @param int $vendorId
     * @return Doctrine_Collection
     */
    public function findAllValidByVendorId( $vendorId )
    {
      $query = $this->createQuery( 'poi' );
      $query->addWhere( 'poi.vendor_id = ?', $vendorId  );

      $query = $this->addWhereLongitudeLatitudeNotNull( $query );

      return $query->execute();
    }

    private function addWhereLongitudeLatitudeNotNull( Doctrine_Query $query )
    {
      $query
        ->addWhere( 'poi.longitude IS NOT NULL' )
        ->addWhere( 'poi.latitude IS NOT NULL' )
        ;
      return $query;
    }
}
