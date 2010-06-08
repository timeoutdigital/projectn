<?php

class LogImportTable extends LogTable
{

    /**
     * Get All LogImports via Vendor City Name
     *
     * @return Doctrine_Collection
     */
    public function getAllByCityName( $city_name )
    {
        $result = Doctrine_Query::create()
            ->from( 'LogImport li' )
            ->leftJoin( 'li.Vendor v' )
            ->leftJoin( 'li.LogImportCount lic' )
            ->where( 'v.city = ?', $city_name )
            ->orderBy( 'li.created_at ASC' )
            ->execute();

      // If City Not Found Return Empty Array;
      return ( $result !== false ) ? $result : array();
    }

    /**
     * Get Latest LogImport via Vendor City Name
     *
     * @return LogImport
     */
    public function getLatestOneByCityName( $city_name )
    {
        $result = Doctrine_Query::create()
            ->from( 'LogImport li' )
            ->leftJoin( 'li.Vendor v' )
            ->leftJoin( 'li.LogImportCount lic' )
            ->where( 'v.city = ?', $city_name )
            ->orderBy( 'li.id DESC' )
            ->limit( 1 )
            ->fetchOne();

      // If City Not Found Return Empty LogImport, so methods still return 0;
      return ( $result !== false ) ? $result : new LogImport();
    }
}
