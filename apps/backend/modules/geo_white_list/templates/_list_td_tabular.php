<td class="sf_admin_text sf_admin_list_td_id">
  <?php echo link_to($poi->getId(), 'poi_edit', $poi) ?>
</td>
<td class="sf_admin_text sf_admin_list_td_poi_name">
  <?php echo $poi->getPoiName() ?>
</td>
<td class="sf_admin_text sf_admin_list_td_latitude">
  <?php echo $poi->getLatitude() ?>
</td>
<td class="sf_admin_text sf_admin_list_td_longitude">
  <?php echo $poi->getLongitude() ?>
</td>
