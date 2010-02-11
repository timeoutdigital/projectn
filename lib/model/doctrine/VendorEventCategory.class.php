<?php

/**
 * VendorEventCategory
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @package    sf_sandbox
 * @subpackage model
 * @author     Your name here
 * @version    SVN: $Id: Builder.php 6820 2009-11-30 17:27:49Z jwage $
 */
class VendorEventCategory extends BaseVendorEventCategory
{

  public function getVendorName()
  {
    return $this[ 'Vendor' ][ 'city' ];
  }

  public function postSave( $obj )
  {

    foreach ( $this['Events'] as $poi )
    {

      foreach( $this['EventCategories'] as $category )
      {
        $poi['EventCategories'][] = $category;
      }

      $poi->save();
    }
  }

}
