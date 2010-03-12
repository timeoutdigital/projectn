<?php
( 0 < count( $vendor_poi_category['PoiCategory'] ) ) ? '<ul>' : '';

foreach ( $vendor_poi_category['PoiCategory'] as $category )
{
  echo '<li>' . $category['name'] . '</li>';
}

( 0 < count( $vendor_poi_category['PoiCategory'] ) ) ? '</ul>' : '';
?>
