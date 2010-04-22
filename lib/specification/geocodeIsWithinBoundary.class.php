<?php 
/**
 * Checks a geocode is within specified boundary
 *
 * @package projectn
 * @subpackage specification.lib
 *
 * @author Clarence Lee <clarencelee@timout.com>
 * @copyright Timeout Communications Ltd
 *
 * @version 1.0.1
 *
 */
 class geocodeIsWithinBoundary
 {
   /**
    * @var float
    */
   private $leftBound;

   /**
    * @var float
    */
   private $rightBound;

   /**
    * @var float
    */
   private $topBound;

   /**
    * @var float
    */
   private $bottomBound;

   /**
    * Returns all Pois that are not within geocoordinate bounds specified by
    * related Vendor
    *
    * @return Doctrine_Collection (of Pois) 
    */
   public function getFailingPois()
   {
     $failingPois = new Doctrine_Collection( Doctrine::getTable( 'Poi' ) );

     foreach( $this->getAllVendors() as $vendor )
     {
       $this->setBounds( $vendor['geo_boundries'] );
       $failingPois->merge( $this->getPoisNotInBounds() );
     }

     return $failingPois;
   }

   /**
    * Returns all Pois that are within geocoordinate bounds specified by
    * related Vendor
    *
    * @return Doctrine_Collection (of Pois) 
    */
   public function getPassingPois()
   {
     $passingPois = new Doctrine_Collection( Doctrine::getTable( 'Poi' ) );

     foreach( $this->getAllVendors() as $vendor )
     {
       $this->setBounds( $vendor['geo_boundries'] );
       $passingPois->merge( $this->getPoisInBounds() );
     }

     return $passingPois;
   }

   private function getAllVendors()
   {
     return Doctrine::getTable( 'Vendor' )->findAll();
   }

   private function setBounds( $bounds )
   {
     $boundaryArray  = explode( ';', $bounds );
     $this->leftBound   = $boundaryArray[0];
     $this->topBound    = $boundaryArray[1];
     $this->rightBound  = $boundaryArray[2];
     $this->bottomBound = $boundaryArray[3];
   }

   private function getPoisNotInBounds()
   {
     return Doctrine::getTable( 'Poi' )
      ->createQuery( 'p' )
      ->addWhere( 'p.longitude < ?', $this->leftBound )
      ->OrWhere(  'p.longitude > ?', $this->rightBound )
      ->OrWhere(  'p.latitude > ?',  $this->topBound )
      ->OrWhere( 'p.latitude < ?',   $this->bottomBound )
      ->execute();
   }

   private function getPoisInBounds()
   {
     return Doctrine::getTable( 'Poi' )
      ->createQuery( 'p' )
      ->addWhere( 'p.longitude > ?', $this->leftBound )
      ->andWhere(  'p.longitude < ?', $this->rightBound )
      ->andWhere(  'p.latitude < ?',  $this->topBound )
      ->andWhere( 'p.latitude > ?',   $this->bottomBound )
      ->execute();
   }
 }
