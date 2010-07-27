<div id="google-map" style="width:700px; height:400px;float:left;margin-right:15px"></div>
<script language="JavaScript" type="text/javascript">
//<![CDATA[
    var map ;
    var marker = null;
    var infowindow = null;
    var infoText ;

    $(document).ready( function()
    {
        var latlng = new google.maps.LatLng(-34.397, 150.644);
        var myOptions = {
          zoom: 14,
          center: latlng,
          mapTypeId: google.maps.MapTypeId.ROADMAP
        };
        map = new google.maps.Map(document.getElementById("google-map"), myOptions);

    });

   function getControls( city )
   {        
       var html  = '<br /> <input type="button" value="' + city + '" id="city_center_venue_details_btn_infowindow" onclick="gotoCityCenter();" >';
       return html;
    }

    function markOnMap( infotxt, latitude, longitude, accuracy )
    {
        var myLatLng = new google.maps.LatLng( latitude, longitude );

        var newZoom = parseInt( ( accuracy / 10 ) * 25 );
        if ( newZoom == 0 )
        {
            newZoom = 6;
        }
        
        if( marker )
        {
            marker.setMap( null );
        }
        if( infowindow )
        {
            infowindow.close();
        }
        infoText = infotxt + getControls( 'Go to ' + $('#venue_details_city').val() );

        var re= /<\S[^><]*>/g;

        marker  = new google.maps.Marker( {
            position: myLatLng,
            map: map,
            draggable: true,
            title: infoText.replace( re, "" )
        } );

        map.setCenter( myLatLng );
        map.setZoom( newZoom );

        showInfoWindow();

        google.maps.event.addListener( marker, 'dragend', updatePositition );

        google.maps.event.addListener( marker, 'click', showInfoWindow );

    }

    function showInfoWindow()
    {
        infowindow = new google.maps.InfoWindow({});

        infowindow.setContent( infoText );

        infowindow.open( map, marker );
    }

    function updatePositition( )
    {
        $('#t_venue_details_latitude').val( parseFloat( marker.getPosition().lat() ).toFixed( 6 ) );

        $('#t_venue_details_longitude').val( parseFloat( marker.getPosition().lng() ).toFixed( 6 ) );

        $('#save_venue_details_btn').val( "Save Changes" );

        $('#save_venue_details_btn').attr( "disabled", false );

        getAddressReverseGeocode();

    }

    function gotoCityCenter( )
    {
        var geocoder = new google.maps.Geocoder();

        geocoder.geocode( { 'address' :  $('#venue_details_city').val() }, function( results, status )
        {
            if (status == google.maps.GeocoderStatus.OK)
            {
                map.setCenter(results[0].geometry.location);

                marker.setPosition( results[0].geometry.location );
            }
            else
            {
             alert("Geocoder failed due to: " + status);
            }
       }
       );
    }

    function recenterCoordinates()
    {
        var latLng = new google.maps.LatLng( parseFloat( marker.getPosition().lat() ).toFixed( 6 ), parseFloat( marker.getPosition().lng() ).toFixed( 6 ) );

        map.setCenter( latLng );
        marker.setPosition( latLng );
        
    }

    function getAddressReverseGeocode( )
    {
        var geocoder = new google.maps.Geocoder();

        geocoder.geocode( { 'latLng' :  marker.getPosition() }, function(results, status)
        {
            if ( status == google.maps.GeocoderStatus.OK )
            {
                $('#venue_details_reverse_geocode').text( results[1].formatted_address );
            }
            else
            {
                $('#venue_details_reverse_geocode').text( status );
            }
       }
       );
    }
    //]]>
</script>