<?php

/**
 * Poi
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @package    sf_sandbox
 * @subpackage model
 * @author     Your name here
 * @version    SVN: $Id: Builder.php 6820 2009-11-30 17:27:49Z jwage $
 */
class Poi extends BasePoi
{
   /**
   * Get a Category by name
   *
   * @param string $name The Cat by name
   *
   * @return object
   */
/*  public function getByVendorPoiId( $id )
  {
    $q = Doctrine_Query::create()
      ->select('p.name AS name, p.id AS id')
      ->from('Poi p')
      ->where('p.name=?', $id);

      return $q->fetchOne();
  }*/
  /**
   *
   */
  private $geoEncodeLookUpString;

  public function setGeoEncodeLookUpString( $geoEncodeLookUpString )
  {
    $this->geoEncodeLookUpString = $geoEncodeLookUpString;
  }
  
  public function addProperty( $lookup, $value )
  {
    $poiPropertyObj = new PoiProperty();
    $poiPropertyObj[ 'lookup' ] = (string) $lookup;
    $poiPropertyObj[ 'value' ] = (string) $value;

    $this[ 'PoiProperty' ][] = $poiPropertyObj;
  }


  public function addVendorCategory( $name, $vendorId )
  {
    if ( is_array( $name ) )
    {
      $name = implode( ' | ', $name );
    }

    $vendorPoiCategoryObj = Doctrine::getTable( 'VendorPoiCategory' )->findOneByNameAndVendorId( $name, $vendorId );

    if ( $vendorPoiCategoryObj === false )
    {
      $vendorPoiCategoryObj = new VendorPoiCategory();
      $vendorPoiCategoryObj[ 'name' ] = $name;
      $vendorPoiCategoryObj[ 'vendor_id' ] = $vendorId;
    }

    $this[ 'VendorPoiCategories' ][] = $vendorPoiCategoryObj;
  }


  public function getName()
  {
    return $this[ 'poi_name' ];
  }

  /**
   * Attempts to fix and / or format fields, e.g. finds a lat long if none provided
   */
  public function preSave( $event )
  {
     
     if(strlen($this['phone']) > 0)
     {
      $this['phone'] = stringTransform::formatPhoneNumber( $this['phone'], $this['Vendor']['inernational_dial_code'] );
     }

     if( $this['url'] != '')
     {
        $this['url'] = stringTransform::formatUrl($this['url']);
     }
     
     //get the longitute and latitude
     $geoEncoder = new geoEncode();
     
     if( is_null( $this['longitude'] ) || is_null( $this['latitude'] ) )
     {
       if( empty( $this->geoEncodeLookUpString ) )
       {
         throw new GeoCodeException( 'geoEncodeLookupString is required to lookup a geoCode for this POI.' );
       }
       
       $geoEncoder->setAddress(  $this->geoEncodeLookUpString );

       $this['longitude'] = $geoEncoder->getLongitude();
       $this['latitude'] = $geoEncoder->getLatitude();

       if( $geoEncoder->getAccuracy() < 5 )
       {
         $this['longitude'] = null;
         $this['latitude'] = null;
         //throw new GeoCodeException('Geo encode accuracy below 5' );
       }
     }
  }

}
