<?php
function get_latest_geocode_source( $poi )
{
    $lastSource = 'Unknown';
    foreach( $poi['PoiMeta'] as $meta )
    {
        if( $meta['lookup'] == 'Geo_Source' )
        {
            $lastSource = $meta['value'];
        }
    }

    return $lastSource;
}
?>
<div id="sf_admin_container">
  <h1>Duplicate Lat/Long Poi</h1>
    <div class="geo_page">
        <table class="geo_white_list_table">

            <thead>
                <tr>
                    <th title="Marked as master poi">M</th>
                    <th title="Already marked?">E</th>
                    <th title="Geocode Source">G</th>
                    <th>Poi Name</th>
                    <th>Street</th>
                    <th>City</th>
                    <th class="export_th">Export</th>
                </tr>
            </thead>

            <tfoot>
                <tr>
                    <td colspan="7" class="save-button">
                        <span class="back-to-list"><?php echo link_to1('&larr; Back to geo white list', '@poi_geo_white_list' );?></span>
                        <span id="wait">Saving... Please wait... <img src="/images/loading-small.gif" alt="Ajax processing"/></span>
                        <input type="button" value="Update Whitelist" onclick="doSubmit();" /></td>
                </tr>
            </tfoot>

            <tbody>
                <?php $alt = 'alt';
                    foreach( $pois as $poi ):
                    $alt = ($alt == '' ) ? 'alt' : '';
                    $geoSource = get_latest_geocode_source( $poi );
                    ?>
                <tr class="<?php echo $alt;?>">
                    <td><?php echo ($poi->isMaster()) ? '✓' : '-';?></td>
                    <td><?php echo ($poi->getWhitelistGeocode() !== null ) ? '✓' : '-';?></td>
                    <td title="<?php echo $geoSource?>"><?php echo substr($geoSource, 0,1 );?></td>
                    <td><?php echo link_to($poi['poi_name'], 'poi_edit', $poi) ?></td>
                    <td><?php echo $poi['street'];?></td>
                    <td><?php echo $poi['city'];?></td>
                    <td>
                        <select name="isWhitelisted">
                            <option>No</option>
                            <option <?php if($poi->isWhitelistedGeocode()) { echo ' selected="selected"';}?>>Yes</option>
                        </select>
                        <input type="hidden" name="poi" value="<?php echo $poi['id'];?>" />
                    </td>
                </tr>
                <?php endforeach;?>
            </tbody>

        </table>

        <div>
            <strong>M</strong> <small>Poi marked as Master Poi</small> |
            <strong>E</strong> <small>Poi Meta exists?</small> |
            <strong>G</strong> <small>Geocode Source</small>
        </div>
        
    </div>

  <div id="google-map">
      loading map...
  </div>
  
</div>
<script type="text/javascript">
    function doSubmit()
    {
        var pois = $('.geo_white_list_table input[type="hidden"]');
        var poi_meta = $('.geo_white_list_table select');

        show_wait();
        var do_continue = true;
        pois.each( function( index, element ){

            if( !do_continue ) return;
            
            //request_string += '&poi[]=' +  + '&meta[]=' + $(poi_meta[index]).val();
            $.ajax({
                url: "<?php echo url_for('geo_white_list/update_whitelist');?>",
                type: "POST",
                data: ({poi : $(element).val(), meta: $(poi_meta[index]).val() }),
                async:false,
                dataType: "json",
                success: function( data ){
                    if( data.status != 'success' )
                    {
                        var poi_name = $( 'a:first-child', $(element).parent( ).parent() ).text();
                        alert( 'Failed to save poi: ' + poi_name + "\nError: " + data.message );
                        do_continue = false;
                    }
                }
            });
        });

        if( do_continue )
            alert( 'Pois whitelist updated' );
        
        hide_wait();
    }

    function show_wait()
    {
        $('.save-button input').hide();
        $('#wait').show();
    }

    function hide_wait()
    {
        $('.save-button input').show();
        $('#wait').hide();
    }
    function initialize() {
        var myLatlng = new google.maps.LatLng( <?php echo $latitude; ?>, <?php echo $longitude; ?>);
        var myOptions = {
          zoom: 16,
          center: myLatlng,
          mapTypeId: google.maps.MapTypeId.ROADMAP
        }
        var map = new google.maps.Map(document.getElementById("google-map"), myOptions);

        var marker = new google.maps.Marker({
            position: myLatlng,
            map: map,
            title:"Duplicate geocode coordinates"
        });
    }

    $(document).ready(function() {
        initialize();
    });
</script>