<?php 
//$cities = Doctrine::getTable( 'Vendor')->findAll( );
$cities = $sf_user->getPermittedVendorCities();

$cityNames = array( '' );

foreach ( $cities as $city ) { $cityNames [$city['id']] = $city['city'];  }

// sort( $cityNames );

$filter = $sf_user->getAttribute( 'venue.filters', array(), 'admin_module' );
?>
<tbody>
  <tr class="sf_admin_form_row sf_admin_text sf_admin_filter_field_name">
    <td>
        <label for="poi_filters_vendor_id">City</label>    </td>
    <td>
    <select name="poi_filters[vendor_id]" id="poi_filters_vendor_id" >
    <?php foreach ( $cityNames as $key => $cityName ) :?>
        <option value="<?php echo $key;?>"  <?php if( isset($filter['vendor_id']) && $filter['vendor_id'] == $key ) echo "selected";?> > <?php echo $cityName;?> </option>
    <?php endforeach;?>
    </select></td>
  </tr>
</tbody>