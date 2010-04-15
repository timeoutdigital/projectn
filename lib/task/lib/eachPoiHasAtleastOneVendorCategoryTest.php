<?php 
/**
 * Checks place-ids in an event xml exist in the poi xml
 *
 * @package projectn
 * @subpackage lib
 *
 * @author Clarence Lee <clarencelee@timout.com>
 * @copyright Timeout Communications Ltd
 *
 * @version 1.0.1
 *
 */
class eachPoiHasAtleastOneVendorCategory extends baseVerifyTask
{
  /**
   * @var Array
   */
  private $entriesWithoutVendorCategories;

  /**
   * @see baseVerifyTask 
   * @return boolean
   */
  protected function verify()
  {
    $entriesWithoutVendorCategories = array();
    $entryTags = $this->getPoiXml()->xpath( '//entry' );

    foreach( $entryTags as $entryTag )
    {
      $vendorCategories = $entryTag->content->{ 'vendor-category' };
      $numVendorCategories = count( $vendorCategories );

      if( $numVendorCategories == 0 )
        $entriesWithoutVendorCategories[] = $entryTag;
    }

    $this->entriesWithoutVendorCategories = $entriesWithoutVendorCategories;
    return count( $entriesWithoutVendorCategories ) == 0;
  }

  /**
   * Provides a success or failure message about the verification
   * 
   * @return string
   */
  public function getMessage()
  {
    if( count( $this->entriesWithoutVendorCategories ) == 0 )
      return 'All POI entries have atleast one vendor-category tag.';

    $numEntriesWithoutVendorCategories = count( $this->entriesWithoutVendorCategories );

    $message = $numVendorCategories . ' POI entries have no vendor-category tags, showing first 10:' . PHP_EOL;

    $limit = 10;
    foreach( $this->entriesWithoutVendorCategories as $entryTag )
    {
      $limit--;
      if( $limit <= 0 ) continue;

      $message .= $entryTag->name . PHP_EOL;
    }
    return $message;
  }
}
