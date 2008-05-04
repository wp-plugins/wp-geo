<?php



/*
Plugin Name: WP Geo
Plugin URI: http://www.benhuson.co.uk/
Description: Adds geocoding to WordPress.
Version: 1.0
Author: Ben Huson
Author URI: http://www.benhuson.co.uk/
Minimum WordPress Version Required: 2.5
*/



/**
 * The WP Geo class
 */
class WPGeo
{



	/**
	 * Category Map
	 */
	function categoryMap()
	{
		
		global $posts;
		
		$showmap = false;
		
		for ($i = 0; $i < count($posts); $i++)
		{
			$post = $posts[$i];
			$latitude = get_post_meta($post->ID, '_wp_geo_latitude', true);
			$longitude = get_post_meta($post->ID, '_wp_geo_longitude', true);
			
			if (is_numeric($latitude) && is_numeric($longitude))
			{
				$showmap = true;
			}
			
		}
		
		if ($showmap)
		{
			echo '<div id="wp_geo_map" style="height:300px;"></div>';
		}
		
	}
	


	/**
	 * Hook: wp_head
	 */
	function wp_head()
	{
		
		global $posts;
		
		$wp_geo_options = get_option('wp_geo_options');
		
		// Only add wp_head when viewing a single post or a page.
		if (!(is_single() || is_page() || is_category())) return;
		
		// Coords to show on map?
		$coords = array();
		for ($i = 0; $i < count($posts); $i++)
		{
			$post = $posts[$i];
			$latitude = get_post_meta($post->ID, '_wp_geo_latitude', true);
			$longitude = get_post_meta($post->ID, '_wp_geo_longitude', true);
			
			if (is_numeric($latitude) && is_numeric($longitude))
			{
				$push = array(
					'id' => $post->ID,
					'latitude' => $latitude,
					'longitude' => $longitude
				);
				array_push($coords, $push);
			}
			
		}
		
		// Need a map?
		if (count($coords) > 0)
		{
		
			$google_maps_api_key = $wp_geo_options['google_api_key'];
			$zoom = 5;
			
			if (count($coords) > 1)
			{
				$zoom = 3;
			}
			
			$maptype = is_category() ? 'G_SATELLITE_MAP' : 'G_HYBRID_MAP';
			
			// Points JS
			$points_js = '';
			for ($i = 0; $i < count($coords); $i++)
			{
				$points_js .= 'center_' . $i .' = new GLatLng(' . $coords[$i]['latitude'] . ', ' . $coords[$i]['longitude'] . ');' . "\n";
				$points_js .= 'marker_' . $i .' = new GMarker(center_' . $i .', {draggable: false});' . "\n";
				$points_js .= 'GEvent.addListener(marker_' . $i . ', "dragstart", function() {
						map.closeInfoWindow();
					});' . "\n";
				$points_js .= 'map.addOverlay(marker_' . $i . ');' . "\n";
			}
			
			// Polyline JS
			$polyline_js = '';
			if (count($coords) > 1)
			{
				$polyline_coords = '';
				for ($i = 0; $i < count($coords); $i++)
				{
					if ($i > 0)
					{
						$polyline_coords .= ',';
					}
					$polyline_coords .= 'new GLatLng(' . $coords[$i]['latitude'] . ', ' . $coords[$i]['longitude'] . ')';
				}
				$polyline_js = 'var polyOptions = {geodesic:true}; 
					var polyline = new GPolyline([' . $polyline_coords . '], "#ffffff", 2, 0.5, polyOptions);
						map.addOverlay(polyline);';
			}
					
			// Script
			$html_content = '<script src="http://maps.google.com/maps?file=api&amp;v=2&amp;key=' . $google_maps_api_key . '" type="text/javascript"></script>';
			$html_content .= '
			<script type="text/javascript">
			//<![CDATA[
			
			var map = null;
		    var marker = null;
			
			function init_wp_geo_map()
			{
				if (GBrowserIsCompatible() && document.getElementById("wp_geo_map"))
				{
					map = new GMap2(document.getElementById("wp_geo_map"));
					var mapTypeControl = new GMapTypeControl();
					var center = new GLatLng(' . $coords[0]['latitude'] . ', ' . $coords[0]['longitude'] . ');
					map.setCenter(center, ' . $zoom . ');
					map.setMapType(' . $maptype . ');
					map.addControl(new GLargeMapControl());
					map.addControl(mapTypeControl);
					
					' . $points_js . '
					
					' . $polyline_js . '
					
					GEvent.addListener(map, "zoomend", function(oldLevel, newLevel) {
						map.setCenter(marker_0.getLatLng());
					});
				}
			}
			if (document.all&&window.attachEvent) { // IE-Win
				window.attachEvent("onload", function () { init_wp_geo_map(); });
				window.attachEvent("onunload", GUnload);
			} else if (window.addEventListener) { // Others
				window.addEventListener("load", function () { init_wp_geo_map(); }, false);
				window.addEventListener("unload", GUnload, false);
			}
			//]]>
			</script>';
			
			echo $html_content;
			
		}
	
		// Check if plugin head needed
		// Check for Google API key
		// Write Javascripts and CSS
		
	}



	/**
	 * Hook: admin_head
	 */
	function admin_head()
	{
		global $post_ID;
		if ($post_ID > 0)
		{
			
			// Get post location
			$latitude = get_post_meta($post_ID, '_wp_geo_latitude', true);
			$longitude = get_post_meta($post_ID, '_wp_geo_longitude', true);
			$default_latitude = $latitude;
			$default_longitude = $longitude;
			$default_zoom = 13;
			
			$panel_open = false;
			$hide_marker = false;
			
			echo WPGeo::mapScriptsInit($default_latitude, $default_longitude, $default_zoom, $panel_open, $hide_marker);
		}
		else
		{
			echo WPGeo::mapScriptsInit(null, null);
		}
	}
	
	
	
	/**
	 * Map Scripts Init
	 */
	function mapScriptsInit($latitude, $longitude, $zoom = 5, $panel_open = false, $hide_marker = false)
	{
		
		$wp_geo_options = get_option('wp_geo_options');
		
		if (!is_numeric($latitude) || !is_numeric($longitude))
		{
			// Centre on London
			$latitude = 51.492526418807465;
			$longitude = -0.15754222869873047;
			$zoom = 5;
			$panel_open = true;
			$hide_marker = true;
		}
		
		// Vars
		$google_maps_api_key = $wp_geo_options['google_api_key'];//'ABQIAAAAFI7dhz07QTtQ4ZYBlayWkhQwjJUkeIrZUptL_je98VVn1CFdMRS4IkExSEz1qUoz6w3885JcPVY5CA';
		$panel_open ? $panel_open = 'jQuery(\'#wpgeolocationdiv.postbox h3\').click();' : $panel_open = '';
		$hide_marker ? $hide_marker = 'marker.hide();' : $hide_marker = '';
		
		// Script
		$html_content = '<script src="http://maps.google.com/maps?file=api&amp;v=2&amp;key=' . $google_maps_api_key . '" type="text/javascript"></script>';
		$html_content .= '
			<script type="text/javascript">
			//<![CDATA[
			
			var map = null;
    		var geocoder = null;
		    var marker = null;
			
			function clearLatLngFields()
			{
				var searchField = document.getElementById("wp_geo_search");
				var latField = document.getElementById("wp_geo_latitude");
				var lngField = document.getElementById("wp_geo_longitude");
				searchField.value = \'\';
				latField.value = \'\';
				lngField.value = \'\';
				marker.hide();
			}
			
			function wp_geo_showAddress()
			{
				var searchField = document.getElementById("wp_geo_search");
				var latField = document.getElementById("wp_geo_latitude");
				var lngField = document.getElementById("wp_geo_longitude");
				var address = searchField.value;
				if (geocoder)
				{
					geocoder.getLatLng(
						address,
						function(point)
						{
							if (!point)
							{
								alert(address + " not found");
							}
							else
							{
								map.setCenter(point);
								marker.setPoint(point);
								marker.show();
								latField.value = point.lat();
								lngField.value = point.lng();
							}
						}
					);
				}
			}
			
			function init_wp_geo_map()
			{
				if (GBrowserIsCompatible() && document.getElementById("wp_geo_map"))
				{
					map = new GMap2(document.getElementById("wp_geo_map"));
					var mapTypeControl = new GMapTypeControl();
					var center = new GLatLng(' . $latitude . ', ' . $longitude . ');
					map.setCenter(center, ' . $zoom . ');
					map.addControl(new GLargeMapControl());
					map.addControl(mapTypeControl);
					
					geocoder = new GClientGeocoder();
					 
					GEvent.addListener(map, "click", function(overlay, latlng) {
						var latField = document.getElementById("wp_geo_latitude");
						var lngField = document.getElementById("wp_geo_longitude");
						latField.value = latlng.lat();
						lngField.value = latlng.lng();
						marker.setPoint(latlng);
						marker.show();
					});
					
					GEvent.addListener(map, "zoomend", function(oldLevel, newLevel) {
						map.setCenter(marker.getLatLng());
					});
					
					marker = new GMarker(center, {draggable: true});
					
					GEvent.addListener(marker, "dragstart", function() {
						map.closeInfoWindow();
					});
					
					GEvent.addListener(marker, "dragend", function() {
						var coords = marker.getLatLng();
						var latField = document.getElementById("wp_geo_latitude");
						var lngField = document.getElementById("wp_geo_longitude");
						latField.value = coords.lat();
						lngField.value = coords.lng();
					});
					
					map.addOverlay(marker);
					
					' . $panel_open . '
					
					var latField = document.getElementById("wp_geo_latitude");
					var lngField = document.getElementById("wp_geo_longitude");
					
					' . $hide_marker . '
					
				}
			}
			if (document.all&&window.attachEvent) { // IE-Win
				window.attachEvent("onload", function () { init_wp_geo_map(); });
				window.attachEvent("onunload", GUnload);
			} else if (window.addEventListener) { // Others
				window.addEventListener("load", function () { init_wp_geo_map(); }, false);
				window.addEventListener("unload", GUnload, false);
			}
			//]]>
			</script>';
			
		return $html_content;
		
	}



	/**
	 * Hook: dbx_post_advanced
	 */
	function dbx_post_advanced()
	{
		global $post_ID;
		
		// Get post location
		$latitude = get_post_meta($post_ID, '_wp_geo_latitude', true);
		$longitude = get_post_meta($post_ID, '_wp_geo_longitude', true);
		
		// Output
		echo WPGeo::mapForm($latitude, $longitude);
		
	}



	/**
	 * Map Form
	 */
	function mapForm($latitude = null, $longitude = null, $search = '')
	{
	
		// Output
		$edit_html = '
			<div id="wpgeolocationdiv" class="postbox if-js-open">
				<h3>WP Geo Location</h3>
				<div class="inside">
					<table cellpadding="3" cellspacing="5" class="form-table">
						<tr>
							<th scope="row">Search for location<br /><span style="font-weight:normal;">(town, postcode or address)</span></th>
							<td><input name="wp_geo_search" type="text" size="45" id="wp_geo_search" value="' . $search . '" /> <span class="submit"><input type="button" id="wp_geo_search_button" name="wp_geo_search_button" value="Search" onclick="wp_geo_showAddress();" /></span></td>
						</tr>
						<tr>
							<td colspan="2">
							<div id="wp_geo_map" style="height:300px; width:100%; padding:0px; margin:0px;">
								Loading Google map...
							</div>
							</td>
						</tr>
						<tr>
							<th scope="row">Latitude, Longitude<br /><a href="#" onclick="clearLatLngFields(); return false;">clear location</a></th>
							<td><input name="wp_geo_latitude" type="text" size="25" id="wp_geo_latitude" value="' . $latitude . '" /> <input name="wp_geo_longitude" type="text" size="25" id="wp_geo_longitude" value="' . $longitude . '" /></td>
						</tr>
					</table>
				</div>
			</div>';
		
		return $edit_html;
		
	}



	/**
	 * Hook: save_post
	 */
	function save_post($post_id)
	{
		delete_post_meta($post_id, '_wp_geo_latitude');
		delete_post_meta($post_id, '_wp_geo_longitude');
		if (isset($_POST['wp_geo_latitude']) && isset($_POST['wp_geo_longitude']))
		{
			if (is_numeric($_POST['wp_geo_latitude']) && is_numeric($_POST['wp_geo_longitude']))
			{
				add_post_meta($post_id, '_wp_geo_latitude', $_POST['wp_geo_latitude']);
				add_post_meta($post_id, '_wp_geo_longitude', $_POST['wp_geo_longitude']);
			}
		}
	}



	/**
	 * Hook: the_content
	 */
	function the_content($content = '')
	{
	
		global $posts;
		
		// Only add wp_head when viewing a single post or a page.
		if (!(is_single() || is_page())) return $content;
		
		// Get the post
		$post = $posts[0];
		$id = $post->ID;
	
		// Get latitude and longitude
		$latitude = get_post_meta($post->ID, '_wp_geo_latitude', true);
		$longitude = get_post_meta($post->ID, '_wp_geo_longitude', true);
		
		// Need a map?
		if (is_numeric($latitude) && is_numeric($longitude))
		{
			return '<div id="wp_geo_map" style="height:300px;"></div>' . $content;
		}
		
		return $content;
		
	}

	
	
	/**
	 * Hook: admin_menu
	 */
	function admin_menu()
	{
		if (function_exists('add_options_page'))
		{
			add_options_page('WP Geo Options', 'WP Geo', 8, __FILE__, array('WPGeo', 'options_page'));
		}
	}



	/**
	 * Options Page
	 */
	function options_page()
	{
		
		$wp_geo_options = get_option('wp_geo_options');
		
		// Process option updates
		if (isset($_POST['action']) && $_POST['action'] == 'update')
		{
			$wp_geo_options['google_api_key'] = $_POST['google_api_key'];
			update_option('wp_geo_options', $wp_geo_options);
			echo '<div class="updated"><p>Options updated</p></div>';
		}

		// Create form elements
		
		// Write the form
		echo '
		<div class="wrap">
			<h2>WP Geo Options</h2>
			<form method="post">
				<table class="form-table">
					<tr valign="top">
						<th scope="row">Google API Key</th>
						<td><input name="google_api_key" type="text" id="google_api_key" value="' . $wp_geo_options['google_api_key'] . '" size="90" /></td>
					</tr>
				</table>
				<p class="submit">
					<input type="submit" name="Submit" value="Save Changes" />
					<input type="hidden" name="action" value="update" />
					<input type="hidden" name="option_fields" value="google_api_key" />
				</p>
			</form>
		</div>';
	}
	
	

}



// Frontend Hooks
add_action('wp_head', array('WPGeo', 'wp_head'));
add_filter('the_content', array('WPGeo', 'the_content'));

// Admin Hooks
add_action('admin_menu', array('WPGeo', 'admin_menu'));
add_action('admin_head', array('WPGeo', 'admin_head'));
add_action('dbx_post_advanced', array('WPGeo', 'dbx_post_advanced'));
add_action('edit_page_form', array('WPGeo', 'dbx_post_advanced'));
add_action('save_post', array('WPGeo', 'save_post'));



?>