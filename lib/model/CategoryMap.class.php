<?php
/**
 * Description of CategoryMapping
 *
 * @package projectn
 * @subpackage lib
 *
 * @author Ralph Schwaninger <ralphschwaninger@timeout.com>
 * @copyright Timeout Communications Ltd
 *
 *
 * @version 1.0.0
 *
 * <b>Example</b>
 * <code>
 * </code>
 *
 */
class CategoryMap {

  /*
   * @var boolean flag to indicate if explicit querying should be used of if
   *      category data should be cached in the object
   */
  private $_queryCache;

  /*
   * @var Doctrine_Collection holding VendorCategories
   */
  private $_vendorCategories;

  /*
   * @var int vendor id
   */
  private $_vendorId;

  /*
   * Constructor
   *
   * @param boolean $explicitQuery, defaults to false
   *
   */
  public function __construct( $queryCache = true )
  {
    $this->_queryCache = $queryCache;
  }

  /*
   * Maps categories and returns the mapped categories as Doctrine Collecion
   *
   * @param vendorObj Vendor
   * @param mixed $sourceCategory (can be SimpleXMLElement or Array)
   * @param string $type ('poi' or 'event' supported)
   * @return array of Doctrine_Collection
   *
   */
  public function mapCategories( $vendorObj, $sourceCategories, $mapCategoriesFor )
  {

    if ( ! in_array( $mapCategoriesFor, array( 'Poi', 'Event' ) ) )
    {
      Throw new Exception("mapping class not supported");
    }

    if ( !$this->_queryCache || $this->_vendorId != $vendorObj[ 'id' ] )
    {
        $this->_vendorId = $vendorObj[ 'id' ];
        $this->_vendorCategories = Doctrine::getTable( 'Vendor' . $mapCategoriesFor . 'Category' )->findByVendorId( $this->_vendorId );
    }

    $returnCategories = new Doctrine_Collection( Doctrine::getTable( $mapCategoriesFor . 'Category' ) );

    foreach( $sourceCategories as $category )
    {
        foreach( $this->_vendorCategories as $vendorCategory )
        {
          if (  $vendorCategory[ 'name' ] == (string) $category[ 'name' ] )
          {
            foreach( $vendorCategory[ $mapCategoriesFor . 'Category' ] as $destinationCategory )
            {
                $returnCategories[] = $destinationCategory;
            }
          }
        }
    }

    return $returnCategories;
  }

}
?>
