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

    /**
     * Get the name of the vendor's uid fieldname, this is a temporary solution
     * @todo rename Poi, Events, Movies etc to have vendor_uid field instead
     * of vendor_<model name>_id to allow polymorphism
     *
     * @return string
     */
    public function getVendorUidFieldName()
    {
      return 'vendor_movie_id';
    }
}
