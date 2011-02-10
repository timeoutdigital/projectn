<?php

class PoiReferenceTable extends Doctrine_Table
{
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
}

class PoiReferenceTableException extends Exception{}