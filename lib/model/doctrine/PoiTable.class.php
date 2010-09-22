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
}
