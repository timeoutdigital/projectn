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

        /* Logic: For each Category in this array, seprate Database Query will be executed to match
         * using producers match settings and when a match found, it will be removed from the list as
         * it listed in black list category...
         */

        $validCategoryNames = array(); // Store only the valid categories that Did not match the black list and match pattern
        foreach( $nameArray as $category_name )
        {
            // Trim before query
            $category_name = stringTransform::mb_trim( $category_name );

            $foundInBlackList = $this->createQuery( 'b' )
                    ->where( 'b.vendor_id = ? ', $vendor_id )
                    ->andWhere( '( ( b.match_left = false AND b.match_right = false AND b.name = ? ) 
                                    OR (b.match_left = true AND b.match_right = true AND ? LIKE CONCAT("%",b.name,"%"))
                                    OR (b.match_left = true AND b.match_right = false AND ? LIKE CONCAT("%",b.name))
                                    OR (b.match_left = false AND b.match_right = true AND ? LIKE CONCAT(b.name,"%"))
                                    OR ( b.name = ?) )' ,
                            array(
                            $category_name,
                            $category_name,
                            $category_name,
                            $category_name,
                            $category_name
                        ) )
                    ->fetchArray();

            // If nto Found in the database, Add it to valid category
            if( is_array( $foundInBlackList ) && empty( $foundInBlackList ) )
            {
                $validCategoryNames[] = $category_name;
            }
        }

        return $validCategoryNames;
    }

}