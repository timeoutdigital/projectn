<?php use_helper('jQuery'); ?>
<script type="text/javascript">
/*<![CDATA[*/
    function showDuplicatePois( link, masterPoiID )
    {
        doAjaxRequest( masterPoiID, link, 'duplicate' );
    }

    function showMasterPoi( link, duplicatePoiID )
    {
        doAjaxRequest( duplicatePoiID, link, 'master' );
    }
    
    function doAjaxRequest( poiID, link, type )
    {
        parentTR = jQuery(link).parent( ).parent();
        jQuery(link).parent().html( jQuery( link ).text() );

        jQuery.ajax({
            type: "POST",
            url: "<?php echo url_for('poi/ajaxPoiList');?>",
            dataType: 'json',
            data: { current_poi_id: poiID, get_type: type },
            success: function( data )
            {
                ajaxSuccess( data, parentTR );
            }
        });
    }

    function ajaxSuccess( data, parentTR )
    {
        if( data.status != 'success' )
        {
            alert( 'Request Error:\n'+data.message );
            return;
        }
        jQuery.each( data.pois, function ( i, poi){
            trData  = '<tr><td colspan="5">&nbsp;&rarr;&nbsp;<em>' + poi.name + '</em></td>';
            trData += '<td><ul class="sf_admin_td_actions"><li class="sf_admin_action_edit"><a href="<?php echo url_for('poi');?>/' + poi.id + '/edit">Edit</a></li></ul></td>';
            trData += '</tr>';
            jQuery( parentTR ).after( trData );
        });
        // Add Duplicate POIS
    }

/*]]>*/
</script>