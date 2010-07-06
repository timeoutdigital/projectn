<?php
( 0 < count( $vendor_event_category['UiCategory'] ) ) ? '<ul>' : '';

foreach ( $vendor_event_category['UiCategory'] as $category )
{
  echo '<li>' . $category['name'] . '</li>';
}

( 0 < count( $vendor_event_category['UiCategory'] ) ) ? '</ul>' : '';
?>
