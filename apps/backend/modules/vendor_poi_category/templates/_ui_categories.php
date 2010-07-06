<?php
( 0 < count( $vendor_poi_category['UiCategory'] ) ) ? '<ul>' : '';

foreach ( $vendor_poi_category['UiCategory'] as $category )
{
  echo '<li>' . $category['name'] . '</li>';
}

( 0 < count( $vendor_poi_category['UiCategory'] ) ) ? '</ul>' : '';
?>
