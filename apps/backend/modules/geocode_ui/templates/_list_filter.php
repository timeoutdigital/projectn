<?php 
$filter = $sf_user->getAttribute('geocode_ui.filters', array(), 'admin_module');
$list = ( isset( $filter['list'] ) ) ? $filter['list'] : 'duplicate';

$tabs = array(
    'duplicate' => 'duplicate',
    'non-geocoded' => 'non - Geocoded',
    'geocoded' => 'Geocoded',
    'manual' => 'Manually Geocoded',
);
?>
<tbody>
  <tr class="sf_admin_form_row sf_admin_text sf_admin_filter_field_name">
    <td>
        <label for="poi_filters_list">Show</label>    </td>
    <td>
    <select name="poi_filters[list]" id="poi_filters_list" >
    <?php foreach ( $tabs as $key => $tabName ) :?>
        <option value="<?php echo $key;?>"  <?php if( isset($filter['list']) && $filter['list'] == $key ) echo "selected";?> > <?php echo $tabName;?> </option>
    <?php endforeach;?>
    </select></td>
  </tr>
</tbody>