<?php
( 0 < count( $vendor_poi_category['PoiCategories'] ) ) ? '<ul>' : '';

foreach ( $vendor_poi_category['PoiCategories'] as $category )
{
  echo '<li>' . $category['name'] . '</li>';
}

( 0 < count( $vendor_poi_category['PoiCategories'] ) ) ? '</ul>' : '';
?>
