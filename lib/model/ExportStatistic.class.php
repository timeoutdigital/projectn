<?php
/**
 * Description of ExportStatistic
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
 *
 * $this->object = new ExportStatistic();
 *
 * $stats = $this->object->getFullStats();
 *
 * </code>
 *
 */
class ExportStatistic {

  public function getFullStats()
  {
    return $this->_getStats();
  }

  public function getStatsByModel( $model )
  {
    return $this->_getStats( false, $model );
  }

  public function getStatsByVendor( $vendorId )
  {
    return $this->_getStats( $vendorId );
  }

  private function _getStats( $limitByVendor = false, $limitByModel = false )
  {
    $q = Doctrine_Query::create()
                 ->select('v.id AS vendor_id,
                           v.language AS language,
                           v.city AS city,
                           el.model,
                           COUNT(eli.id) AS cnt')
                ->from('ExportLogger el')
                ->leftJoin( 'el.Vendor v' )
                ->leftJoin( 'el.ExportLoggerItem eli' );

    if ( $limitByVendor !== false)
    {
        $q->andWhere( 'el.vendor_id = ?', $limitByVendor);
    }
    if ( $limitByModel !== false)
    {
        $q->andWhere( 'el.model = ?', $limitByModel);
    }

    $q->groupBy( 'el.model, el.vendor_id' );
    
    $stats = $q->execute();

    $statsArray = array();

    foreach( $stats as $stat )
    {
        $statsArray[] = array( 'vendor_id' => $stat[ 'vendor_id' ],
                             'language' => $stat[ 'language' ],
                             'city' => $stat[ 'city' ],
                             'model' => $stat[ 'model' ],
                             'count' => $stat[ 'cnt' ]
                            );
    }

    return $statsArray;
  }

}
?>
