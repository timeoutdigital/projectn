<td class="sf_admin_text sf_admin_list_td_name">
  <?php echo get_partial('poi_name', array('type' => 'list', 't_venue' => $t_venue)) ?>
</td>
<td class="sf_admin_text sf_admin_list_td_city">
  <?php echo get_partial('city', array('type' => 'list', 't_venue' => $t_venue)) ?>
</td>
<td class="sf_admin_text sf_admin_list_td_address1">
  <?php echo get_partial('street', array('type' => 'list', 't_venue' => $t_venue)) ?>
</td>
<td class="sf_admin_text sf_admin_list_td_geocode">
  <?php echo get_partial('geocodeaccuracy', array('type' => 'list', 't_venue' => $t_venue)) ?>
</td>
<td class="sf_admin_date sf_admin_list_td_import_date">
  <?php echo get_partial('updated_at', array('type' => 'list', 't_venue' => $t_venue)) ?>
</td>
