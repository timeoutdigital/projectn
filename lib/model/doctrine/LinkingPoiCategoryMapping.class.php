<?php

/**
 * LinkingPoiCategoryMapping
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @package    sf_sandbox
 * @subpackage model
 * @author     Your name here
 * @version    SVN: $Id: Builder.php 6820 2009-11-30 17:27:49Z jwage $
 */
class LinkingPoiCategoryMapping extends BaseLinkingPoiCategoryMapping
{
  public function getPoiCategoryName()
  {
    return $this[ 'PoiCategory' ][ 'name' ];
  }

  public function getVendorPoiCategoryName()
  {
    return $this[ 'VendorPoiCategory' ][ 'name' ];
  }

  public function getVendorName()
  {
    return $this[ 'VendorPoiCategory' ][ 'Vendor' ][ 'city' ];
  }

}