<?php
( 0 < count( $vendor_event_category['EventCategory'] ) ) ? '<ul>' : '';

foreach ( $vendor_event_category['EventCategory'] as $category )
{
  echo '<li>' . $category['name'] . '</li>';
}

( 0 < count( $vendor_event_category['EventCategory'] ) ) ? '</ul>' : '';
?>
