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
     *
     * @return string
     */
    public function getVendorUidFieldName()
    {
      return 'vendor_poi_id';
    }

    public function findAllDuplicateLatLongs( $vendorId )
    {
         return $this->createQuery( 'p' )
             ->select("p.latitude, p.longitude, CONCAT( latitude, ', ', longitude ) as myString")
             ->where('p.vendor_id = ?', $vendorId )
             ->addWhere('p.id NOT IN ( SELECT pm.record_id FROM PoiMeta pm WHERE pm.lookup = "Duplicate" )')
             ->groupBy('myString')
             ->having('count( myString ) > 1')
             ->execute( array(), Doctrine_Core::HYDRATE_ARRAY );
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
      $query = $this->addWhereNotMarkedAsDuplicate( $query );

      return $query->execute();
    }
    private function addWhereNotMarkedAsDuplicate( Doctrine_Query $query )
    {
      $query
        ->addWhere('poi.id NOT IN ( SELECT pm.record_id FROM PoiMeta pm WHERE pm.lookup = "Duplicate" )')
        ;
      return $query;
    }

    private function addWhereLongitudeLatitudeNotNull( Doctrine_Query $query )
    {
      $query
        ->addWhere( 'poi.longitude IS NOT NULL' )
        ->addWhere( 'poi.latitude IS NOT NULL' )
        ;
      return $query;
    }


    /**
     * Get all poi categories
     *
     * @return <type>
     */
    public function getAllDuplicatedLongLatPois()
    {
        $query = Doctrine_Query::create()
                     ->select('p.id AS id, p.latitude AS latitude, p.longitude as longitude, count(*) as total')
                     ->from('Poi p')
                     ->groupBy('p.longitude')
                     ->orderBy('p__3 DESC')
                     ->having('count(*) > 1');



        return $query->execute(array(), Doctrine_Core::HYDRATE_ARRAY);



    }

    public function findByVendorPoiIdAndVendorLanguage( $vendorPoiId, $vendorLanguage )
    {
      $vendors = Doctrine::getTable( 'Vendor' )->findByLanguage( $vendorLanguage );

      $vendorIds = array();
      foreach( $vendors as $vendor )
        $vendorIds[] = $vendor[ 'id' ];

      $poi = $this
        ->createQuery( 'p' )
        ->andWhere( 'p.vendor_poi_id = ?', $vendorPoiId )
        ->andWhereIn( 'p.vendor_id', $vendorIds )
        ->fetchOne()
        ;
      return $poi;
    }

    public function getVendorPoiCategoryByVendorId( $vendorID, $order = 'name ASC' )
    {
        $query = Doctrine_Query::create( )
            ->from( 'VendorPoiCategory v' )
            ->andWhere( 'vendor_id = ?', $vendorID )
            ->orderBy( $order );

        return $query->execute();
    }
}
