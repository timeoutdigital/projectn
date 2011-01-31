<?php


class VendorCategoryBlackListTable extends Doctrine_Table
{
    
    public static function getInstance()
    {
        return Doctrine_Core::getTable('VendorCategoryBlackList');
    }

    public function getCategoryNameInArrayBy( $vendor_id )
    {
        
        $results = Doctrine::getTable( 'VendorCategoryBlackList' )->createQuery( 'b' )
                ->select( 'b.name' )
                ->where( 'b.vendor_id = ?', $vendor_id )
                ->fetchArray();

        if( $results === false || empty( $results ) )
            return false;
        
        // return only name
        $returnArray = array();
        foreach( $results as $result)
            $returnArray[] = $result['name'];

        return $returnArray;
    }
}