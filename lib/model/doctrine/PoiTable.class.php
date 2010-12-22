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

    public function findAllDuplicateLatLongsAndApplyWhitelist( $vendorId )
    {
         $q = $this->createQuery()
             ->from('Poi p')
             ->select('p.latitude, p.longitude, CONCAT( latitude, ", ", longitude ) as myString')
             ->where('p.vendor_id = ?', $vendorId )
             ->addWhere('p.id NOT IN ( SELECT pm.record_id FROM PoiMeta pm WHERE pm.lookup = "Duplicate" )')
             ->addWhere('CONCAT( p.latitude, ", ", p.longitude ) NOT IN ( SELECT CONCAT( g.latitude, ", ", g.longitude ) FROM GeoWhiteList g )')
             ->groupBy('myString')
             ->having('count( myString ) > 1');

         return $q->execute( array(), Doctrine_Core::HYDRATE_ARRAY );
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

    /**
     * Check for Duplicate POI by poi_id
     * @param int $poiID
     * @return boolean
     */
    public function isDuplicate( $poiID )
    {
        if( !$poiID || !is_numeric( $poiID ) || $poiID <= 0 )
        {
            throw new PoiTableException( 'Invalid $poiID in parameter' );
        }

        $poiReference = Doctrine::getTable( 'PoiReference' )->findOneBy( 'duplicate_poi_id', $poiID );

        if( $poiReference === false )
        {
            return false;
        }
        
        return true;
    }

    /**
     * Get the Master of given POI ID or return null
     * @param int $poiID
     * @return mixed
     */
    public function getMasterOf( $poiID )
    {
        if( !$poiID || !is_numeric( $poiID ) || $poiID <= 0 )
        {
            throw new PoiTableException( 'Invalid $poiID in parameter' );
        }

        $q = $this->createQuery( 'p' )
                ->leftJoin( 'p.PoiReference r')
                ->where( 'r.duplicate_poi_id = ?', $poiID );

        return $q->fetchOne();
    }

    public function searchAllNonDuplicateAndNonMasterPoisBy( $vendorID, $searchKeyword, $hydrationMode = Doctrine_Core::HYDRATE_RECORD )
    {
        $q = $this->createQuery( 'p' )
                ->select( 'p.id, p.poi_name')
                ->where( 'p.vendor_id = ? ', $vendorID )
                ->andWhere( 'p.id NOT IN (select master_poi_id FROM poi_reference UNION select duplicate_poi_id FROM poi_reference )' )
                //->andWhere( 'r.master_poi_id IS NULL AND r.duplicate_poi_id IS NULL ')
                ->andWhere( ' ( p.poi_name LIKE ? OR p.id LIKE ? )', array( $searchKeyword . '%', $searchKeyword . '%' ) );
        
        return $q->execute( array(), $hydrationMode );
    }
}


class PoiTableException extends Exception{}