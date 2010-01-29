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
   * @var Category Obj
   */
  private $_noMatchCategory;

  /*
   * Constructor
   *
   * @param boolean
   *
   */
  //public

  /*
   * Maps categories and returns the mapped categories as Doctrine Collecion
   *
   * @param vendorObj Vendor
   * @param mixed $sourceCategory (can be SimpleXMLElement or Array
   * @param string $mapClass ('PoiCategory' or 'EventCategory' supported)
   * @param string $otherCategoryNameString defaults to 'other'
   * @return array of Doctrine_Collection
   *
   */
  public function mapCategories( $vendorObj, $sourceCategory, $mapClass, $noMatchCategoryNameString = 'other' )
  {

    if ( ! in_array( $mapClass, array( 'PoiCategory', 'EventCategory' ) ) )
    {
      Throw new Exception("mapping class not supported");
    }




    $this->_noMatchCategory = Doctrine::getTable( $mapClass )->findOneByName( $noMatchCategoryNameString );

    $categoriesMappingArray = Doctrine::getTable( $mapClass . 'Mapping' )->findByVendorId( $vendorObj[ 'id' ] );




    $mappedCategoriesArray = new Doctrine_Collection( Doctrine::getTable( $mapClass ) );

    foreach( $sourceCategory as $category )
    {
      $match = false;

      foreach ( $categoriesMappingArray as $categoryMappingArray )
      {
        if (  $categoryMappingArray[ 'Vendor' . $mapClass ][ 'name' ] == (string) $category )
        {
          $mappedCategoriesArray[] = $categoryMappingArray[ $mapClass ];
          $match = true;
        }
      }

    }

    if ( $match === false && is_object( $this->_noMatchCategory ) )
    {
      $mappedCategoriesArray[] = $this->_noMatchCategory;
    }

    return $mappedCategoriesArray;
  }

}
?>
