<?php
/**
 * Business Logic for Movie table
 *
 * @package projectn
 * @subpackage model
 *
 * @author Ralph Schwaninger <ralphschwaninger@timeout.com>
 * @copyright Timeout Communications Ltd 2009
 *
 *
 */
class MovieTable extends Doctrine_Table
{

   /**
   * Get a Movie by Vendor
   *
   * @param string $city The city of the vendor
   *
   * @return <type>
   */
  public function getMovieByVendor($city)
  {
    $q = Doctrine_Query::create()
      ->from('Movie m')
      ->leftJoin('m.Vendor v')
      ->where('v.city=?', $city);

      return $q->execute();
  }
}
