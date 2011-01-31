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
     * Pass in Vendor Poi or Vendor Event category to clean blacklisted category names
     * @param Doctrine_Record $category
     * @return Doctrine_Record
     */
    public function cleanBlackListedCategory( Doctrine_Record $category )
    {
        $vendor_id = $category['vendor_id'];

        $blackListedCategories = $this->getCategoryNameInArrayBy( $vendor_id );
        
        if( !is_array( $blackListedCategories ) || empty( $blackListedCategories ) )
          return $category;

        $categoryExploded = explode( '|', $category['name'] );
        $filteredCategories = array();
        foreach( $categoryExploded as $catName )
        {
            if( !in_array( stringTransform::mb_trim( $catName ), $blackListedCategories ) )
                  $filteredCategories[] = stringTransform::mb_trim( $catName );
        }
        
        if( empty($filteredCategories) )
        {
            return false;
        }

        $category['name'] = stringTransform::concatNonBlankStrings( ' | ' , $filteredCategories );

        return $category;
    }
}