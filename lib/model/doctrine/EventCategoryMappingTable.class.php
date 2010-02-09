<?php
/**
 * Business logic for Event Category Mappings
 *
 *
 * @package projectn
 * @subpackage model
 *
 * @author Ralph Schwaninger <ralphschwaninger@timeout.com>
 * @copyright Timeout Communications Ltd 2009
 *
 *
 */
class EventCategoryMappingTable extends Doctrine_Table
{

  public function findByVendorId( $vendorId )
  {
    return $this->createQuery('ecm')
                ->leftJoin( 'ecm.VendorEventCategory vec' )
                ->leftJoin( 'ecm.EventCategory ec' )
                ->where('vec.vendor_id = ?', $vendorId)
                ->execute();
  }

}
