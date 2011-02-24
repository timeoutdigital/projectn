<div id="sf_admin_container">
  <h1>Duplicate Lat/Long Poi</h1>

        <table class="geo_white_list_table">

            <thead>
                <tr>
                    <th>Poi Name</th>
                    <th>Street</th>
                    <th>City</th>
                    <th class="export_th">Export</th>
                </tr>
            </thead>

            <tfoot>
                <tr>
                    <td colspan="4" class="save-button"><input type="button" value="Update Whitelist" onclick="doSubmit();" /></td>
                </tr>
            </tfoot>

            <tbody>
                <?php $alt = 'alt';
                    foreach( $pois as $poi ):
                    $alt = ($alt == '' ) ? 'alt' : '';
                    ?>
                <tr class="<?php echo $alt;?>">
                    <td><?php echo link_to($poi['poi_name'], 'poi_edit', $poi) ?></td>
                    <td><?php echo $poi['street'];?></td>
                    <td><?php echo $poi['city'];?></td>
                    <td>
                        <select name="isWhitelisted">
                            <option>Yes</option>
                            <option>No</option>
                        </select>
                        <input type="hidden" name="poi" value="<?php echo $poi['id'];?>" />
                    </td>
                </tr>
                <?php endforeach;?>
            </tbody>

        </table>
</div>
<script type="text/javascript">
    function doSubmit()
    {
        $('.geo_white_list_table input[type="hidden"]').each( function( index, element ){
            alert( index );
        });
    }
</script>