


/**
* ----- WP Geo Admin Post -----
* JavaScript for the WP Go Google Maps interface
* when editing posts and pages.
*/



var map = null;
var geocoder = null;
var marker = null;



function wpgeo_updatedLatLngFields() {

	var latitude  = jQuery("input#wp_geo_latitude").val();
	var longitude = jQuery("input#wp_geo_longitude").val();
	
	if ( latitude == '' || longitude == '' ) {
		marker.setMap(null);
	} else {
		var point = new google.maps.LatLng(latitude, longitude);
		marker.setPosition(point);
		map.setCenter(point);
		marker.setMap(map);
	}
	
}



jQuery(document).ready(function() {
	
	
	
	// Latitude field updated
	jQuery("#wp_geo_latitude").keyup(function() {
		wpgeo_updatedLatLngFields();
	});
	
	
	
	// Longitude field updated
	jQuery("#wp_geo_longitude").keyup(function() {
		wpgeo_updatedLatLngFields();
	});
	
	
	
	// Clear location fields
	jQuery("#wpgeo_location a.wpgeo-clear-location-fields").click(function(e) {
		
		jQuery("input#wp_geo_search").val('');
		jQuery("input#wp_geo_latitude").val('');
		jQuery("input#wp_geo_longitude").val('');
		wpgeo_updatedLatLngFields();
		
		return false;
		
	});
	
	
	
	// Centre Location
	jQuery("#wpgeo_location a.wpgeo-centre-location").click(function(e) {
		
		map.setCenter(marker.getPosition());
		
		return false;
		
	});
	
	
	
	// Location search
	jQuery("#wpgeo_location #wp_geo_search_button").click(function(e) {
		
		var latitude  = jQuery("input#wp_geo_latitude").val();
		var longitude = jQuery("input#wp_geo_longitude").val();
		var address = jQuery("input#wp_geo_search").val();
		
		var geocoder = new google.maps.Geocoder();
		
		if ( geocoder ) {
			geocoder.geocode(
				{ 'address': address },
				function(results, status) {
					if (status == google.maps.GeocoderStatus.OK) {
						map.setCenter(results[0].geometry.location);
						marker.setPosition(results[0].geometry.location);
						marker.setMap(map);
						jQuery("input#wp_geo_latitude").val(results[0].geometry.location.lat());
						jQuery("input#wp_geo_longitude").val(results[0].geometry.location.lng());
					} else {
						alert(address + " not found");
					}
				}
			);
		}
		
		return false;
		
	});
	
	
	
});