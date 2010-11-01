<?php
/**
 * Class to access the vendor table
 *
 * @author Tim Bowler <timbowler@timeout.com>
 *
 */
class VendorTable extends Doctrine_Table
{

  /**
   * Get a Vendor by City
   *
   * @param string $city The city of the vendor
   * @param string $language The language of the vendor
   * 
   * @return <type>
   */
  public function getVendorByCityAndLanguage($city, $language)
  {
    $q = Doctrine_Query::create()
      ->select('v.city AS city, v.language')
      ->from('Vendor v')
      ->where('v.city=?', $city)
      ->addWhere('v.language=?', $language);

      return $q->fetchOne();
  }

  /**
   * Find All vendors by alphabetical order
   * @param int $hydrationMode
   * @return mix
   */
  public function findAllVendorsInAlphaBeticalOrder( $hydrationMode = Doctrine_core::HYDRATE_RECORD )
  {
      $q = Doctrine_Query::create()
      ->from( 'Vendor v ' )
      ->orderBy( 'city ASC' );

      return $q->execute( array(), $hydrationMode );
  }

}
