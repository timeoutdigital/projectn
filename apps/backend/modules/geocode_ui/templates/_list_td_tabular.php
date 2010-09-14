<td class="sf_admin_text sf_admin_list_td_name">
  <?php echo get_partial('edit_poi', array('type' => 'list', 't_venue' => $t_venue)) ?>
</td>
<td class="sf_admin_text sf_admin_list_td_name">
  <?php echo get_partial('poi_name', array('type' => 'list', 't_venue' => $t_venue)) ?>
</td>
<td class="sf_admin_text sf_admin_list_td_address1">
  <?php echo get_partial('street', array('type' => 'list', 't_venue' => $t_venue)) ?>
</td>
<td class="sf_admin_text sf_admin_list_td_city">
  <?php echo get_partial('city', array('type' => 'list', 't_venue' => $t_venue)) ?>
</td>
<td class="sf_admin_text sf_admin_list_td_lat">
  <a href='javascript:getVenueDetails( "<?php echo $t_venue->getId(); ?> " )'> <?php echo $t_venue->getLatitude();?> </a>
</td>
<td class="sf_admin_date sf_admin_list_td_long">
  <a href='javascript:getVenueDetails( "<?php echo $t_venue->getId(); ?> " )'> <?php echo $t_venue->getLongitude();?> </a>
</td>
