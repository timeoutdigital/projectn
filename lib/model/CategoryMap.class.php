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
   * @var Category Obj
   */
  private $_noMatchCategory;

  /*
   * @var Doctrine_Collection holding category mappings
   */
  private $_categoriesMappingLookupCol;

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
   * @param string $otherCategoryNameString defaults to 'other'
   * @return array of Doctrine_Collection
   *
   */
  public function mapCategories( $vendorObj, $sourceCategory, $mapCategoriesFor, $noMatchCategoryNameString = 'other' )
  {

    if ( ! in_array( $mapCategoriesFor, array( 'Poi', 'Event' ) ) )
    {
      Throw new Exception("mapping class not supported");
    }

    //if ( ! $this->_queryCache || $this->_noMatchCategory != $noMatchCategoryNameString )
    //{
      $this->_noMatchCategory = Doctrine::getTable( $mapCategoriesFor . 'Category' )->findOneByName( $noMatchCategoryNameString );
    //}

    //if ( ! $this->_queryCache || $this->_vendorId != $vendorObj[ 'id' ] ||  )
    //{

      $this->_vendorId = $vendorObj[ 'id' ];

      $this->_categoriesMappingLookupCol = Doctrine::getTable( 'Vendor' . $mapCategoriesFor . 'Category' )->findMappingsByVendorId( $this->_vendorId );

    //}

    $mappedCategoriesCol = new Doctrine_Collection( Doctrine::getTable( $mapCategoriesFor . 'Category' ) );

    foreach( $sourceCategory as $category )
    {
      $match = false;

      foreach ( $this->_categoriesMappingLookupCol as $ourCategoryObj )
      {

        foreach( $ourCategoryObj[ 'Vendor' . $mapCategoriesFor . 'Categories' ] as $vendorPoiCategory )
        {

          if (  $vendorPoiCategory[ 'name' ] == (string) $category )
          {
            $mappedCategoriesCol[] = $ourCategoryObj;
            $match = true;
          }
        }
      }
    }

    if ( $match === false && is_object( $this->_noMatchCategory ) )
    {
      $mappedCategoriesCol[] = $this->_noMatchCategory;
    }

    return $mappedCategoriesCol;
  }

}
?>
