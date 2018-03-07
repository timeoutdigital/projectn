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
         $q = $this->createQuery()
             ->from('Poi p')
             ->select('p.latitude, p.longitude, CONCAT( latitude, ", ", longitude ) as myString')
             ->where('p.vendor_id = ?', $vendorId )
             ->groupBy('myString')
             ->having('count( myString ) > 1');

         $q = $this->addWherePoiIsNotDuplicate( $q, $vendorId );
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
      $query = $this->addWherePoiIsNotDuplicate( $query, $vendorId, 'poi' );

      return $query->execute();
    }

    private function addWherePoiIsNotDuplicate(  Doctrine_Query $query, $vendorID, $alias = 'p')
    {
        $query->andWhere( "{$alias}.id NOT IN (SELECT pr.duplicate_poi_id FROM PoiReference pr) " );
        //$query->leftJoin("{$alias}.PoiReference d ON d.duplicate_poi_id = {$alias}.id");
        //$query->addWhere( 'd.duplicate_poi_id IS NULL' );
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
     * @param int $hydrationMode
     * @return mixed
     */
    public function getMasterOf( $poiID, $hydrationMode = Doctrine_Core::HYDRATE_RECORD )
    {
        if( !$poiID || !is_numeric( $poiID ) || $poiID <= 0 )
        {
            throw new PoiTableException( 'Invalid $poiID in parameter' );
        }

        $q = $this->createQuery( 'p' )
                ->where( 'p.id IN ( SELECT pr.master_poi_id FROM PoiReference pr WHERE pr.duplicate_poi_id = ?)', $poiID );

        return $q->fetchOne( array(), $hydrationMode );
    }

    /**
     * Get the Duplicate Pois of Given Master POI ID
     * @param int $poiID
     * @param int $hydrationMode
     * @return mixed
     */
    public function getDuplicatesOf( $poiID, $hydrationMode = Doctrine_Core::HYDRATE_RECORD )
    {
        if( !$poiID || !is_numeric( $poiID ) || $poiID <= 0 )
        {
            throw new PoiTableException( 'Invalid $poiID in parameter' );
        }


        $q = $this->createQuery( 'p' )
                ->where( 'p.id IN ( SELECT pr.duplicate_poi_id FROM PoiReference pr WHERE pr.master_poi_id = ? )', $poiID );

        return $q->execute( array(), $hydrationMode );
    }

    public function searchAllNonDuplicateAndNonMasterPoisBy( $vendorID, $searchKeyword, $hydrationMode = Doctrine_Core::HYDRATE_RECORD )
    {
        $q = $this->createQuery( 'p' )
                ->select( 'p.id, p.poi_name')
                ->where( 'p.vendor_id = ? ', $vendorID )
                ->andWhere( 'p.id NOT IN (select master_poi_id FROM poi_reference UNION select duplicate_poi_id FROM poi_reference )' )
                ->andWhere( ' ( p.poi_name LIKE ? OR p.id LIKE ? )', array( $searchKeyword . '%', $searchKeyword . '%' ) );
        
        return $q->execute( array(), $hydrationMode );
    }

    /**
     * Get all those Poi's that Don't have have Meta Mark and found to be
     * duplicate Geocode with another Poi that may have Meta
     * @param int $vendorId
     * @param Doctrine_Core::HYDRATE_RECORD $hydrationMode
     * @return mixed
     */
    public function findAllDuplicateLatLongPoisNotWhitelistedBy( $vendorId, $hydrationMode = Doctrine_Core::HYDRATE_RECORD )
    {
        $q = $this->createQuery( 'p' )
                ->select( 'CONCAT( p.latitude, ",", p.longitude )' )
                ->where( 'p.vendor_id = ? ', $vendorId )
                ->andWhere('p.latitude is not null ')
                ->andWhere('p.longitude is not null ')
                ->andWhere( 'p.id NOT IN (select duplicate_poi_id from poi_reference) ' );

        $duplicateLatLong = $q->execute( array(), Doctrine_Core::HYDRATE_ARRAY );

        if( !is_array( $duplicateLatLong ) || empty($duplicateLatLong ) )
        {
            return false;
        }

        // Build array of Duplicates
        $duplicates = array();
        foreach ( $duplicateLatLong as $record )
        {
            $duplicates[] = $record['CONCAT'];
        }

        $q = $this->createQuery( 'p' )
                ->select( 'p.*' )
                ->leftJoin( 'p.PoiMeta m WITH m.lookup = ?', "geocodeWhitelist" )
                ->where( 'p.vendor_id = ?', $vendorId )
                ->andWhere( 'p.latitude IS NOT NULL')
                ->andWhere( 'p.longitude IS NOT NULL')
                ->andWhere( 'm.id IS NULL ' )
                ->andWhere( 'p.id NOT IN (select duplicate_poi_id from poi_reference) ' )
                ->andWhereIn( 'CONCAT( p.latitude, ",", p.longitude )', $duplicates );

        return $q->execute( array(), $hydrationMode );
    }
}


class PoiTableException extends Exception{}