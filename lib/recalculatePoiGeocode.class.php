<?php

class recalculatePoiGeocode
{
  /**
   * @var $pois
   */
  private $pois;

  /**
   * @var geoEncode
   */
  private $geoEncode;

  public function __construct()
  {
    $this->geoEncode = new geoEncode();
  }

  /**
   *
   */
  public function setGeoEncoder( geoEncode $geoEncode )
  {
    $this->geoEncode = $geoEncode;
  }

  /**
   * @param Doctrine_Collection $pois
   */
  public function addPois( $collection )
  {
    if( is_null( $collection ) )
      throw new Exception( 'Requires a collection of Pois. Got null.' );

    $type = $this->getTypeOf( $collection );

    if( $type != 'Poi' )
      throw new Exception( 'Requires a collection of Pois. Got collection of ' . $type );

    $this->pois = $collection;
  }

  public function run()
  {
    foreach( $this->pois as $poi )
    {
      $this->recalculateGeocodeFor( $poi );
    }
  }

  private function recalculateGeocodeFor( $poi )
  {
    $poi->save();
  }

  private function getTypeOf( Doctrine_Collection $collection )
  {
    return get_class( $collection->getTable()->getRecord() );
  }
}
