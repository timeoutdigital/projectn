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

    /**
     * Filter given category names array for black listed vendor category.
     * @param int $vendor_id
     * @param array $nameArray
     * @return array
     */
    public function filterByCategoryBlackList( $vendor_id, $nameArray )
    {
        if( !is_array( $nameArray ) || empty($nameArray) )
        {
            return array();
        }

        // Get categories black lisetd for this vendor to match
        $blackListedCategories = $this->getCategoryNameInArrayBy( $vendor_id );
        if( !is_array($blackListedCategories) || empty($blackListedCategories) )
        {
            return $nameArray;
        }

        // match and remove blacklisted category from the nameArray
        foreach( $nameArray as $key=>$category )
        {
            if( in_array( $category, $blackListedCategories) )
            {
                unset($nameArray[$key]); // delete this category from the List
            }
        }

        return $nameArray;
        
    }

}