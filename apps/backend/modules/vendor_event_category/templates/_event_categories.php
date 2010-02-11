<?php
( 0 < count( $vendor_event_category['EventCategories'] ) ) ? '<ul>' : '';

foreach ( $vendor_event_category['EventCategories'] as $category )
{
  echo '<li>' . $category['name'] . '</li>';
}

( 0 < count( $vendor_event_category['EventCategories'] ) ) ? '</ul>' : '';
?>
