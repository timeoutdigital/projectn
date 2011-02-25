<div id="sf_admin_container">
  <h1>Duplicate Lat/Long Poi</h1>

        <table class="geo_white_list_table">

            <thead>
                <tr>
                    <th title="Marked as master poi">M</th>
                    <th title="Already marked?">E</th>
                    <th>Poi Name</th>
                    <th>Street</th>
                    <th>City</th>
                    <th class="export_th">Export</th>
                </tr>
            </thead>

            <tfoot>
                <tr>
                    <td colspan="6" class="save-button">
                        <span id="wait">Saving... Please wait... <img src="/images/loading-small.gif" alt="Ajax processing"/></span>
                        <input type="button" value="Update Whitelist" onclick="doSubmit();" /></td>
                </tr>
            </tfoot>

            <tbody>
                <?php $alt = 'alt';
                    foreach( $pois as $poi ):
                    $alt = ($alt == '' ) ? 'alt' : '';
                    ?>
                <tr class="<?php echo $alt;?>">
                    <td><?php echo ($poi->isMaster()) ? '✓' : '-';?></td>
                    <td><?php echo ($poi->getWhitelistGeocode() !== null ) ? '✓' : '-';?></td>
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
      <strong>E</strong> <small>Poi Meta exists?</small>
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
</script>