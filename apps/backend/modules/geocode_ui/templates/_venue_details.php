<div id="sf_admin_venue_details" style="float:left;">

<table cellspacing="0">
      <tfoot>
        <tr>
          <td colspan="2">
            <a id="t_venue_details_searchurl" href="" target="_blank">Google search </a>
            <input type="button" value="Recentre marker" id="recentre_marker" onclick="recenterCoordinates();" />
          </td>
        </tr>
        <tr>
          <td colspan="2">
           <input type="hidden" id="venue_details_id" />
           <input type="hidden" id="venue_details_city" />
           <input type="button" value="Saved" disabled="true" id="save_venue_details_btn" onclick="saveCoordinates();" />
           <!-- <input type="button" value="Clear Lat / Long" id="reset_venue_details_btn" onclick="resetCoordinates();" /> -->
          </td>
        </tr>
      </tfoot>
      <tbody>
    </tbody>
  <tbody>
  <tr class="sf_admin_form_row sf_admin_text sf_admin_filter_field_name">
    <td>
        <label for="t_venue_details_name">Name</label>
    </td>
    <td>
        <input id="t_venue_details_name" readonly="true" value="" />
    </td>
  </tr>

  <tr class="sf_admin_form_row sf_admin_text sf_admin_filter_field_name">
    <td>
        <label for="t_venue_details_address1">House No</label>
    </td>
    <td>
        <input id="t_venue_details_address1"  readonly="true" value="" />
    </td>
  </tr>

  <tr class="sf_admin_form_row sf_admin_text sf_admin_filter_field_name">
    <td>
        <label for="t_venue_details_address2">Street Name</label>
    </td>
    <td>
        <input id="t_venue_details_address2"  readonly="true" value="" />
    </td>
  </tr>

  <tr class="sf_admin_form_row sf_admin_text sf_admin_filter_field_name">
    <td>
        <label for="t_venue_details_address3">Additional Address Details</label>
    </td>
    <td>
        <input id="t_venue_details_address3"  readonly="true" value="" />
    </td>
  </tr>

  <tr class="sf_admin_form_row sf_admin_text sf_admin_filter_field_name">
    <td>
        <label for="t_venue_details_latitude">Latitude</label>
    </td>
    <td>
        <input id="t_venue_details_latitude" value="" />
    </td>
  </tr>

   <tr class="sf_admin_form_row sf_admin_text sf_admin_filter_field_name">
    <td>
        <label for="t_venue_details_longitude">Longitude</label>
    </td>
    <td>
        <input id="t_venue_details_longitude" value="" />
    </td>
  </tr>

  <tr class="sf_admin_form_row sf_admin_text sf_admin_filter_field_name">
    <td>
        <label for="t_venue_details_longitude">R-GeoCode</label>
    </td>
    <td>
        <textarea readonly = "readonly" id="venue_details_reverse_geocode"></textarea>
    </td>
  </tr>

 </tbody>
</table>
</div>
<div class="clear"></div>

<script language="JavaScript" type="text/javascript">
//<![CDATA[

var oldId = '';

function getVenueDetails( venueId )
{
  $.ajax( {
  url:  '<?php echo url_for( '@geocode_ui' ) ;?>/venueDetails/?venueId=' +  venueId ,

  success: function(data)
  {
      var venue = eval('(' + data + ')');
      
      // Show error when Error Returned
      if(venue.error && venue.error != '')
      {
          alert("Error:\n" + venue.error);
          return false;
      }
      $('#t_venue_details_name').val( venue.name );
      $('#venue_details_city').val( venue.city );
      $('#t_venue_details_address1').val( venue.address1 );
      $('#t_venue_details_address2').val( venue.address2 );
      $('#t_venue_details_address3').val( venue.address3 );
      $('#t_venue_details_latitude').val( venue.latitude );
      $('#t_venue_details_longitude').val( venue.longitude );
      $('#venue_details_reverse_geocode').text( '' );
      $('#save_venue_details_btn').attr( "disabled", false );
      $('#save_venue_details_btn').val( 'Save' );
      $('#venue_details_id').val( venue.id );

      $('#t_venue_details_searchurl').attr( 'href' ,  'http://www.google.com/#hl=en&source=hp&q=' + encodeURIComponent( venue.name)  + ' ' + encodeURIComponent( venue.address1 ) + ' ' + encodeURIComponent( venue.city )   );


      var infoText = '<strong>'+ venue.name +'</strong> <br />' ;
          infoText += venue.address1 + ' ' ;
          infoText += venue.address2 + ' ';
          infoText += venue.address3 + ' ';
          infoText += venue.city ;

     if ( !venue.latitude && !venue.longitude )
     {
    	 markOnMap( infoText, venue.latitude, venue.longitude, venue.geocode_accuracy );
    	 gotoCityCenter( );
     }
     else
     {
         markOnMap( infoText, venue.latitude, venue.longitude, venue.geocode_accuracy );
     }

     if ( oldId != '' )
     {
         $( oldId ).attr( 'class', $( oldId ).attr( 'class' ).replace( ' highlight', '' ) );
     }
     oldId = '#t_venue_row_' + venue.id.replace( ':', '_' );
     $( oldId ).attr( 'class', $( oldId ).attr( 'class' ) + ' highlight' );

     $('#save_venue_details_btn').attr( "disabled", false );
     // $('#save_venue_details_btn_infowindow').attr( "disabled", false );
  }
});
}

function _saveVenue( venueId , latitude, longitude , rGeoCode, geocode_accuracy , reloadDetails )
{ 
    $.ajax( {
        url:  '<?php echo url_for( '@geocode_ui') ;?>/saveVenueDetails/' ,
        data:
        {
            venueId:   venueId,
            latitude:  latitude,
            longitude: longitude,
            geocode_lookup: rGeoCode,
            geocode_accuracy: geocode_accuracy
        },
        success: function( data )
        {
            // Assume that Data is Returned in JSON format
            var result = eval('(' + data + ')');

            // Show error when Error Returned
            if(result.error && result.error != '')
            {
              alert( "Error:\n" + result.error );
              return false;
            }

            if( reloadDetails == true ) getVenueDetails( venueId );

            $('#save_venue_details_btn').val( "Updated"  );

            //$( '#geocode_accuracy_' + venueId.replace( ':', '_' ) ).html( '<strong>Verified</strong>' );

            clearForm();

            // Show Alert when found
            if(result.alert && result.alert != '')
              alert( result.alert );
        }
   } );
}

function clearForm()
{
    $('#save_venue_details_btn').attr( "disabled", true );
}

function saveCoordinates()
{
     var venueId    = $('#venue_details_id').val();
     var latitude   = $('#t_venue_details_latitude').val();
     var longitude  = $('#t_venue_details_longitude').val();
     var rGeoCode   = $('#venue_details_reverse_geocode').val();
     
     var reloadDetails = false;

    _saveVenue( venueId, latitude, longitude, rGeoCode, <?php echo sfConfig::get('app_geocode_override_accuracy'); ?>, reloadDetails );
}

function resetCoordinates()
{
     if( !confirm( 'Do you want to clear Latitude and Longitude of this POI?' ) )
         return;
     
     var venueId    = $('#venue_details_id').val();
     var latitude   = '';
     var longitude  = '';
     var rGeoCode   = '';
     var reloadDetails = true;

    _saveVenue( venueId, latitude, longitude, rGeoCode, 0, reloadDetails );
}

//]]>
</script>
