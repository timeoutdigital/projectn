<?php

class PoiReferenceTable extends Doctrine_Table
{
    /**
     * Remove relationship if Exists
     * @param int $duplicate_poi_id
     */
    public function removeRelationShip( $duplicate_poi_id )
    {
        if( !is_numeric($duplicate_poi_id) || intval( $duplicate_poi_id ) <= 0 )
        {
            throw new PoiReferenceTableException( 'Invalid paramer value $duplicate_poi_id' );
        }

        // delete existing if found
        $this->createQuery()
                ->delete()
                ->where( 'duplicate_poi_id = ? ', $duplicate_poi_id )
                ->execute();
    }

    public function removeDuplicateReferences( $master_poi_id )
    {
        if( !is_numeric($master_poi_id) || intval( $master_poi_id ) <= 0 )
        {
            throw new PoiReferenceTableException( 'Invalid paramer value $master_poi_id' );
        }

        // delete existing if found
        $this->createQuery()
                ->delete()
                ->where( 'master_poi_id = ? ', $master_poi_id )
                ->execute();
    }

    /**
     * Create relationship between two POI id's
     * @param int $master_poi_id
     * @param int $duplicate_poi_id
     */
    public function relatePois( $master_poi_id, $duplicate_poi_id )
    {
        if( !is_numeric($master_poi_id) || intval( $master_poi_id ) <= 0 )
        {
            throw new PoiReferenceTableException( 'Invalid paramer value $master_poi_id' );
        }
        
        if( !is_numeric($duplicate_poi_id) || intval( $duplicate_poi_id ) <= 0 )
        {
            throw new PoiReferenceTableException( 'Invalid paramer value $duplicate_poi_id' );
        }

        if( $master_poi_id == $duplicate_poi_id )
        {
            throw new PoiReferenceTableException( 'Invalid paramer value: $master_poi_id and $duplicate_poi_id cannot be same' );
        }

        // See if exists already as PoiReference uses composite primary key and unique dupliate_id field
        $existing = Doctrine::getTable( 'PoiReference' )->findOneByMasterPoiIdAndDuplicatePoiId( $master_poi_id, $duplicate_poi_id );
        if( $existing === false )
        {
            // Ensure that this Duplicate POI is not Master of another POI
            // and this Poi cannot be Existing duplicate as It should be unique by table design
            $is_exists= Doctrine::getTable( 'PoiReference' )->findOneByMasterPoiIdOrDuplicatePoiIdOrDuplicatePoiId( $duplicate_poi_id, $master_poi_id, $duplicate_poi_id );
            if( $is_exists !== false )
            {
                throw new PoiReferenceTableException( 'Duplicate POI already exists or this Duplicate Poi is master of oter Poi(s), One Poi cannot be Master and Duplicate and Duplicate of two different pois' );
            }

            $related = new PoiReference;
            $related['master_poi_id'] = $master_poi_id;
            $related['duplicate_poi_id'] = $duplicate_poi_id;
            $related->save();
        }
    }
}

class PoiReferenceTableException extends Exception{}