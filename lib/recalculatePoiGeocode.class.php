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
    $geoEncode = $this->geoEncode;
    $geoEncode->setAddress( $poi->getGeoEncodeLookUpString(), $poi['Vendor'] );
    $geoEncode->getGeoCode();

    $poi[ 'longitude' ] = $geoEncode->getLongitude();
    $poi[ 'latitude' ]  = $geoEncode->getLatitude();

    $poi[ 'longitude' ] = $geoEncode->getLongitude();
    $poi[ 'latitude' ]  = $geoEncode->getLatitude();
  }

  private function getTypeOf( Doctrine_Collection $collection )
  {
    return get_class( $collection->getTable()->getRecord() );
  }
}
