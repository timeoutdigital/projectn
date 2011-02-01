<?php
    // Get the Pois based on Current POI
    $pois = Doctrine::getTable( 'Poi' )->findByVendorIdAndPoiNameAndLatitudeAndLongitude( $foundpoi['vendor_id'], $foundpoi['poi_name'], $foundpoi['latitude'], $foundpoi['longitude'] );

    // To prevent having multiple masters, we have to check each pois for existing master
    $masterExists = false;
    foreach( $pois as $poi ) if( $poi->isMaster() ) { $masterExists = true; break; }

?>
<td colspan="2">
    <form action="<?php echo url_for('duplicate_pois/processMasterSlave');?>" method="post">
        <table>

            <thead>
                <tr>
                    <th>ID</th>
                    <th>Poi name</th>
                    <th>Latitude</th>
                    <th>Longitude</th>
                    <th>City</th>
                    <th id="sf_admin_list_th_actions"><?php echo __('Master', array(), 'sf_admin') ?></th>
                </tr>
            </thead>

            <tfoot>
                <tr>
                    <th colspan="6">
                        <input type="submit" value="Save Changes" />
                    </th>
                </tr>
            </tfoot>

            <tbody>
                <?php foreach ($pois as $i => $poi): $odd = fmod(++$i, 2) ? 'odd' : 'even' ?>
                <tr class="sf_admin_row <?php echo $odd;?>">
                    <td><a href="<?php echo url_for('poi/edit?id='.$poi['id']);?>" target="_blank"><?php echo $poi['id']; ?></a></td>
                    <td><?php echo $poi['poi_name']; ?></td>
                    <td><?php echo $poi['latitude']; ?></td>
                    <td><?php echo $poi['longitude']; ?></td>
                    <td><?php echo $poi['city']; ?></td>
                    <td>
                        <?php
                        if( $masterExists ) :
                            if( $poi->isMaster() )
                            {
                                printf( '<input type="hidden" name="masterid[]" value="%s" /><label>Master</label>', $poi['id'] );
                            } else {
                                printf( '<label>%s</label>', $poi->isDuplicate() ? 'Duplicate' : 'New' );
                            }
                        else:
                        ?>
                            <label><input type="checkbox" name="masterid[]" value="<?php echo $poi['id'];?>" class="selectas" onClick="uncheckOthers(this);" /> yes</label>
                        <?php endif; ?>
                        <input type="hidden" name="poi_id[]" value="<?php echo $poi['id'];?>" />
                    </td>
                </tr>
                <?php endforeach;?>
            </tbody>

        </table>
    </form>
<?php use_helper('jQuery'); ?>
<script type="text/javascript">
/*<![CDATA[*/
    function uncheckOthers( chkBox )
    {
        selected = jQuery(chkBox).attr('checked');
        // de select all other and select this
        jQuery('.selectas:checked').each( function(){

                jQuery(this).attr('checked', false);
        });

        jQuery(chkBox).attr('checked', selected);
    }
/*]]>*/
</script>
</td>