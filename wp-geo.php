<?php



/*
Plugin Name: WP Geo
Plugin URI: http://www.benhuson.co.uk/wordpress-plugins/wp-geo/
Description: Adds geocoding to WordPress.
Version: 2.1.2
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
	 * Properties
	 */
	
	// Folders
	/*
	var $plugin_folder = '/wp-content/plugins/wp-geo';
	var $content_folder = '/wp-content/uploads/wp-geo';
	*/
	
	// Marker images
	/*
	var $marker_dot = 'dot-marker.png';
	var $marker_dot_trans = 'dot-marker-trans.png';
	var $marker_large = 'large-marker.png';
	var $marker_large_trans = 'large-marker-trans.png';
	var $marker_small = 'small-marker.png';
	var $marker_small_trans = 'small-marker-trans.png';
	*/
	
	
	
	/**
	 * Constructor
	 */
	function WPGeo()
	{
	}
	
	

	/**
	 * Register Activation
	 */
	function register_activation()
	{
	
		$options = array(
			'google_api_key' => '', 
			'google_map_type' => 'G_NORMAL_MAP', 
			'show_post_map' => 'TOP', 
			'default_map_width' => '100%', 
			'default_map_height' => '300px',
			'default_map_zoom' => '5',
			'show_maps_on_home' => 'Y',
			'show_maps_on_pages' => 'Y',
			'show_maps_on_posts' => 'Y',
			'show_maps_in_datearchives' => 'Y',
			'show_maps_in_categoryarchives' => 'Y',
			'add_geo_information_to_rss' => 'Y'
		);
		add_option('wp_geo_options', $options);
		$wp_geo_options = get_option('wp_geo_options');
		foreach ($options as $key => $val)
		{
			if (!isset($wp_geo_options[$key]))
			{
				$wp_geo_options[$key] = $options[$key];
			}
		}
		update_option('wp_geo_options', $wp_geo_options);
		
		// Files
		clearstatcache();
		$old_umask = umask(0);
		mkdir(ABSPATH . '/wp-content/uploads/wp-geo');
		mkdir(ABSPATH . '/wp-content/uploads/wp-geo/markers');
		
		$old_marker_image_dir = ABSPATH . '/wp-content/plugins/wp-geo/img/markers/';
		$new_marker_image_dir = ABSPATH . '/wp-content/uploads/wp-geo/markers/';
		
		WPGeo::moveFileOrDelete($old_marker_image_dir . 'dot-marker.png', $new_marker_image_dir . 'dot-marker.png');
		WPGeo::moveFileOrDelete($old_marker_image_dir . 'dot-marker-shadow.png', $new_marker_image_dir . 'dot-marker-shadow.png');
		WPGeo::moveFileOrDelete($old_marker_image_dir . 'large-marker.png', $new_marker_image_dir . 'large-marker.png');
		WPGeo::moveFileOrDelete($old_marker_image_dir . 'large-marker-shadow.png', $new_marker_image_dir . 'large-marker-shadow.png');
		WPGeo::moveFileOrDelete($old_marker_image_dir . 'small-marker.png', $new_marker_image_dir . 'small-marker.png');
		WPGeo::moveFileOrDelete($old_marker_image_dir . 'small-marker-shadow.png', $new_marker_image_dir . 'small-marker-shadow.png');
		
		umask($old_umask);
		
	}
	
	
	
	/**
	 * Move File or Delete (if already exists)
	 */
	function moveFileOrDelete($old_file, $new_file)
	{
		
		if (!file_exists($new_file))
		{
			$ok = copy($old_file, $new_file);
			if ($ok)
			{
				// Moved OK...
			}
		}
		
	}
	
	
	
	/**
	 * Shortcode: [wp_geo_map type="G_NORMAL_MAP"]
	 */
	function shortcode_wpgeo_map($atts, $content = null)
	{
	
		global $post;
		$id = $post->ID;
		
		$wp_geo_options = get_option('wp_geo_options');
		
		if (WPGeo::show_maps() && $wp_geo_options['show_post_map'] == 'HIDE')
		{
			$map_atts = array('type' => 'G_NORMAL_MAP');
			extract(shortcode_atts($map_atts, $atts));
			return '<div class="wp_geo_map" id="wp_geo_map_' . $id . '" style="width:' . $wp_geo_options['default_map_width'] . '; height:' . $wp_geo_options['default_map_height'] . ';">' . $content . '</div>';
		}
		else
		{
			return '';
		}
		
	}
	


	/**
	 * Category Map
	 */
	function categoryMap()
	{
		
		global $posts;
		
		$wp_geo_options = get_option('wp_geo_options');
		
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
			echo '<div class="wp_geo_map" id="wp_geo_map" style="width:' . $wp_geo_options['default_map_width'] . '; height:' . $wp_geo_options['default_map_height'] . ';"></div>';
		}
		
	}
	


	/**
	 * Hook: wp_head
	 */
	function wp_head()
	{
		
		// CSS
		echo '<link rel="stylesheet" href="' . get_bloginfo('url') . '/wp-content/plugins/wp-geo/wp-geo.css" type="text/css" />';
		
		if (WPGeo::show_maps())
		{
		
			global $posts;
			
			$wp_geo_options = get_option('wp_geo_options');
			
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
				$zoom = $wp_geo_options['default_map_zoom']; //5;
				
				if (count($coords) > 1)
				{
					$zoom = $wp_geo_options['default_map_zoom']; // 3;
				}
				
				$maptype = empty($wp_geo_options['google_map_type']) ? 'G_NORMAL_MAP' : $wp_geo_options['google_map_type'];			
				
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
				
				// Post Maps and Markers
				$js_map_inits = '';
				$js_marker_inits = '';
				$js_map_writes = '';
				for ($i = 0; $i < count($coords); $i++)
				{
					$js_map_inits .= 'var map' . $coords[$i]['id'] . ' = null; ';
					$js_marker_inits .= 'var marker' . $coords[$i]['id'] . ' = null; ';
					$js_map_writes .= '
						if (document.getElementById("wp_geo_map_' . $coords[$i]['id'] . '"))
						{
							map' . $coords[$i]['id'] . ' = new GMap2(document.getElementById("wp_geo_map_' . $coords[$i]['id'] . '"));
							var mapTypeControl = new GMapTypeControl();
							var center = new GLatLng(' . $coords[$i]['latitude'] . ', ' . $coords[$i]['longitude'] . ');
							map' . $coords[$i]['id'] . '.setCenter(center, ' . $zoom . ');
							map' . $coords[$i]['id'] . '.setMapType(' . $maptype . ');
							map' . $coords[$i]['id'] . '.addControl(new GLargeMapControl());
							map' . $coords[$i]['id'] . '.addControl(mapTypeControl);
							
							var center' . $coords[$i]['id'] .' = new GLatLng(' . $coords[$i]['latitude'] . ', ' . $coords[$i]['longitude'] . ');
							marker' . $coords[$i]['id'] .' = new GMarker(center' . $coords[$i]['id'] .', {draggable: false});
							
							GEvent.addListener(marker' . $coords[$i]['id'] . ', "dragstart", function() {
								map.closeInfoWindow();
							});
							map' . $coords[$i]['id'] . '.addOverlay(marker' . $coords[$i]['id'] . ');
							
							GEvent.addListener(map' . $coords[$i]['id'] . ', "zoomend", function(oldLevel, newLevel) {
								map' . $coords[$i]['id'] . '.setCenter(marker' . $coords[$i]['id'] . '.getLatLng());
							});
						}';
				}
						
				// Script
				WPGeo::includeGoogleMapsJavaScriptAPI();
				$html_content .= '
				<script type="text/javascript">
				//<![CDATA[
				
				var map = null; ' . $js_map_inits . '
				var marker = null; ' . $js_marker_inits . '
				
				function init_wp_geo_map()
				{
					if (GBrowserIsCompatible())
					{
						if (document.getElementById("wp_geo_map"))
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
						' . $js_map_writes . '
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
		
	}



	/**
	 * Hook: admin_head
	 */
	function admin_head()
	{
	
		// Only load if on a post or page
		if (WPGeo::show_maps())
		{
		
		//global $post_ID;
		//if ($post_ID > 0)
		//{
			
			// Get post location
			$latitude = get_post_meta($post_ID, '_wp_geo_latitude', true);
			$longitude = get_post_meta($post_ID, '_wp_geo_longitude', true);
			$default_latitude = $latitude;
			$default_longitude = $longitude;
			$default_zoom = 13;
			
			$panel_open = false;
			$hide_marker = false;
			
			echo WPGeo::mapScriptsInit($default_latitude, $default_longitude, $default_zoom, $panel_open, $hide_marker);
		//}
		//else
		//{
			// Only need on post and page editing pages in the admin
			//echo WPGeo::mapScriptsInit(null, null);
		//}
		
		}
		
	}
	
	
	
	/**
	 * Include Google Maps JavaScript API
	 */
	function includeGoogleMapsJavaScriptAPI()
	{
	
		if (WPGeo::show_maps())
		{
			$wp_geo_options = get_option('wp_geo_options');
			wp_enqueue_script('jquery');
			wp_register_script('googlemaps', 'http://maps.google.com/maps?file=api&amp;v=2&amp;key=' . $wp_geo_options['google_api_key'], false, '');
			wp_enqueue_script('googlemaps');
			return '';
		}
		
	}
	
	
	
	/**
	 * Map Scripts Init
	 */
	function mapScriptsInit($latitude, $longitude, $zoom = 5, $panel_open = false, $hide_marker = false)
	{
		
		$wp_geo_options = get_option('wp_geo_options');
		$maptype = empty($wp_geo_options['google_map_type']) ? 'G_NORMAL_MAP' : $wp_geo_options['google_map_type'];	
		
		if (!is_numeric($latitude) || !is_numeric($longitude))
		{
			// Centre on London
			$latitude = 51.492526418807465;
			$longitude = -0.15754222869873047;
			$zoom = $wp_geo_options['default_map_zoom']; //5;
			$panel_open = true;
			$hide_marker = true;
		}
		
		// Vars
		$google_maps_api_key = $wp_geo_options['google_api_key'];
		$panel_open ? $panel_open = 'jQuery(\'#wpgeolocationdiv.postbox h3\').click();' : $panel_open = '';
		$hide_marker ? $hide_marker = 'marker.hide();' : $hide_marker = '';
		
		// Script
		WPGeo::includeGoogleMapsJavaScriptAPI();
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
					map.addMapType(G_PHYSICAL_MAP);
					map.addControl(new GLargeMapControl());
					map.addControl(mapTypeControl);
					map.setMapType(' . $maptype . ');
					
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
	
		if (WPGeo::show_maps())
		{
		
			global $posts, $post;
			
			$wp_geo_options = get_option('wp_geo_options');
			
			// Get the post
			$id = $post->ID;
		
			// Get latitude and longitude
			$latitude = get_post_meta($post->ID, '_wp_geo_latitude', true);
			$longitude = get_post_meta($post->ID, '_wp_geo_longitude', true);
			
			// Need a map?
			if (is_numeric($latitude) && is_numeric($longitude))
			{
				if ($wp_geo_options['show_post_map'] == 'TOP')
				{
					// Show at top of post
					return '<div class="wp_geo_map" id="wp_geo_map_' . $id . '" style="width:' . $wp_geo_options['default_map_width'] . '; height:' . $wp_geo_options['default_map_height'] . ';"></div>' . $content;
				}
				elseif ($wp_geo_options['show_post_map'] == 'BOTTOM')
				{
					// Show at bottom of post
					return $content . '<div class="wp_geo_map" id="wp_geo_map_' . $id . '" style="width:' . $wp_geo_options['default_map_width'] . '; height:' . $wp_geo_options['default_map_height'] . ';"></div>';
				}
			}
		
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
	 * Show Maps
	 */
	function show_maps()
	{
	
		global $post_ID;
		$wp_geo_options = get_option('wp_geo_options');
		
		if (is_home() && $wp_geo_options['show_maps_on_home'] == 'Y')
		{
			return true;
		}
		if (is_single() && $wp_geo_options['show_maps_on_posts'] == 'Y')
		{
			return true;
		}
		if (is_page() && $wp_geo_options['show_maps_on_pages'] == 'Y')
		{
			return true;
		}
		if (is_date() && $wp_geo_options['show_maps_in_datearchives'] == 'Y')
		{
			return true;
		}
		if (is_category() && $wp_geo_options['show_maps_in_categoryarchives'] == 'Y')
		{
			return true;
		}
		if (is_feed() && $wp_geo_options['add_geo_information_to_rss'] == 'Y')
		{
			return true;
		}
		
		// Activate maps in admin...
		if (is_admin())
		{
			// Note: Can easily detect manage post/page pages but possible to detect new post/page pages?
			//if (is_numeric($post_ID) && $post_ID > 0)
			//{
				return true;
			//}
		}
		
		return false;
		
	}



	/**
	 * Options Checkbox
	 */
	function options_checkbox($id, $val, $checked)
	{
	
		$is_checked = '';
		if ($val == $checked)
		{
			$is_checked = 'checked="checked" ';
		}
		return '<input name="' . $id . '" type="checkbox" id="' . $id . '" value="' . $val . '" ' . $is_checked . '/>';
	
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
			$wp_geo_options['google_map_type'] = $_POST['google_map_type'];
			$wp_geo_options['show_post_map'] = $_POST['show_post_map'];
			$wp_geo_options['default_map_width'] = WPGeo::numberPercentOrPx($_POST['default_map_width']);
			$wp_geo_options['default_map_height'] = WPGeo::numberPercentOrPx($_POST['default_map_height']);
			$wp_geo_options['default_map_zoom'] = $_POST['default_map_zoom'];
			
			$wp_geo_options['show_maps_on_home'] = $_POST['show_maps_on_home'];
			$wp_geo_options['show_maps_on_pages'] = $_POST['show_maps_on_pages'];
			$wp_geo_options['show_maps_on_posts'] = $_POST['show_maps_on_posts'];
			$wp_geo_options['show_maps_in_datearchives'] = $_POST['show_maps_in_datearchives'];
			$wp_geo_options['show_maps_in_categoryarchives'] = $_POST['show_maps_in_categoryarchives'];
			
			$wp_geo_options['add_geo_information_to_rss'] = $_POST['add_geo_information_to_rss'];
			
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
						<td><input name="google_api_key" type="text" id="google_api_key" value="' . $wp_geo_options['google_api_key'] . '" size="50" /></td>
					</tr>
					<tr valign="top">
						<th scope="row">Map Type</th>
						<td>' . WPGeo::google_map_types('menu', $wp_geo_options['google_map_type']) . '</td>
					</tr>
					<tr valign="top">
						<th scope="row">Show Post Map</th>
						<td>' . WPGeo::post_map_menu('menu', $wp_geo_options['show_post_map']) . '</td>
					</tr>
					<tr valign="top">
						<th scope="row">Default Map Width</th>
						<td><input name="default_map_width" type="text" id="default_map_width" value="' . $wp_geo_options['default_map_width'] . '" size="10" /></td>
					</tr>
					<tr valign="top">
						<th scope="row">Default Map Height</th>
						<td><input name="default_map_height" type="text" id="default_map_height" value="' . $wp_geo_options['default_map_height'] . '" size="10" /></td>
					</tr>
					<tr valign="top">
						<th scope="row">Default Map Zoom</th>
						<td>' . WPGeo::selectMapZoom('menu', $wp_geo_options['default_map_zoom']) . '</td>
					</tr>
					<tr valign="top">
						<th scope="row">Show Maps On</th>
						<td>
							' . WPGeo::options_checkbox('show_maps_on_pages', 'Y', $wp_geo_options['show_maps_on_pages']) . ' Pages<br />
							' . WPGeo::options_checkbox('show_maps_on_posts', 'Y', $wp_geo_options['show_maps_on_posts']) . ' Posts (single posts)<br />
							' . WPGeo::options_checkbox('show_maps_on_home', 'Y', $wp_geo_options['show_maps_on_home']) . ' Posts home page<br />
							' . WPGeo::options_checkbox('show_maps_in_datearchives', 'Y', $wp_geo_options['show_maps_in_datearchives']) . ' Posts in date archives<br />
							' . WPGeo::options_checkbox('show_maps_in_categoryarchives', 'Y', $wp_geo_options['show_maps_in_categoryarchives']) . ' Posts in category archives
						</td>
					</tr>
					<tr valign="top">
						<th scope="row">Feeds</th>
						<td>' . WPGeo::options_checkbox('add_geo_information_to_rss', 'Y', $wp_geo_options['add_geo_information_to_rss']) . ' Add geographic information</td>
					</tr>
				</table>
				<p class="submit">
					<input type="submit" name="Submit" value="Save Changes" />
					<input type="hidden" name="action" value="update" />
					<input type="hidden" name="option_fields" value="google_api_key,google_map_type,show_post_map" />
				</p>
			</form>
			<h2>Documentation</h2>
			<p>If you set the Show Post Map setting to &quot;Manual&quot;, you can use the Shortcode <code>[wp_geo_map]</code> in a post to display a map (if a location has been set for the post). You can only include the Shortcode once within a post. If you select another Show Post Map option then the Shortcode will be ignored and the map will be positioned automatically.</p>
		</div>';
		
	}
	
	
	
	/**
	 * Number Percent Or Px
	 */
	function numberPercentOrPx($str = false)
	{
	
		if (is_numeric($str))
		{
			$str .= 'px';
		}
		return $str;
	
	}



	/**
	 * Select Map Zoom
	 */
	function selectMapZoom($return = 'array', $selected = '')
	{
		
		// Array
		$map_type_array = array(
			'0' 	=> '0 - Zoomed Out', 
			'1' 	=> '1', 
			'2' 	=> '2', 
			'3' 	=> '3', 
			'4' 	=> '4', 
			'5' 	=> '5', 
			'6' 	=> '6', 
			'7' 	=> '7', 
			'8' 	=> '8', 
			'9' 	=> '9', 
			'10' 	=> '10', 
			'11' 	=> '11', 
			'12' 	=> '12', 
			'13' 	=> '13', 
			'14' 	=> '14', 
			'15' 	=> '15', 
			'16' 	=> '16', 
			'17' 	=> '17', 
			'18' 	=> '18', 
			'19' 	=> '19 - Zoomed In', 
		);
		
		// Menu?
		if ($return = 'menu')
		{
			$menu = '';
			foreach ($map_type_array as $key => $val)
			{
				$is_selected = $selected == $key ? ' selected="selected"' : '';
				$menu .= '<option value="' . $key . '"' . $is_selected . '>' . $val . '</option>';
			}
			$menu = '<select name="default_map_zoom" id="default_map_zoom">' . $menu. '</select>';
			return $menu;
		}
		
		// Default return
		return $map_type_array;
		
	}
	


	/**
	 * Google Map Types
	 */
	function google_map_types($return = 'array', $selected = '')
	{
		
		// Array
		$map_type_array = array(
			'G_NORMAL_MAP' 		=> 'Normal', 
			'G_SATELLITE_MAP' 	=> 'Satellite', 
			'G_HYBRID_MAP' 		=> 'Hybrid (Satellite with roads etc.)', 
			'G_PHYSICAL_MAP' 	=> 'Physical (Terrain information)'
		);
		
		// Menu?
		if ($return = 'menu')
		{
			$menu = '';
			foreach ($map_type_array as $key => $val)
			{
				$is_selected = $selected == $key ? ' selected="selected"' : '';
				$menu .= '<option value="' . $key . '"' . $is_selected . '>' . $val . '</option>';
			}
			$menu = '<select name="google_map_type" id="google_map_type">' . $menu. '</select>';
			return $menu;
		}
		
		// Default return
		return $map_type_array;
		
	}



	/**
	 * Post Map Menu
	 */
	function post_map_menu($return = 'array', $selected = '')
	{
		
		// Array
		$map_type_array = array(
			'TOP' 		=> 'At top of post', 
			'BOTTOM' 	=> 'At bottom of post', 
			'HIDE' 		=> "Manually"
		);
		
		// Menu?
		if ($return = 'menu')
		{
			$menu = '';
			foreach ($map_type_array as $key => $val)
			{
				$is_selected = $selected == $key ? ' selected="selected"' : '';
				$menu .= '<option value="' . $key . '"' . $is_selected . '>' . $val . '</option>';
			}
			$menu = '<select name="show_post_map" id="show_post_map">' . $menu. '</select>';
			return $menu;
		}
		
		// Default return
		return $map_type_array;
		
	}



	/**
	 * Get Marker Meta
	 */
	function get_marker_meta($type = 'large')
	{
		
		// Array
		$marker_types = array();
		
		// Large Marker
		$marker_types['large'] = array(
			'width' => 10,
			'height' => 17,
			'anchorX' => 5,
			'anchorY' => 17,
			'image' => get_bloginfo('url') . '/wp-content/uploads/wp-geo/markers/small-marker.png',
			'shadow' => get_bloginfo('url') . '/wp-content/wp-geo/markers/small-marker-shadow.png'
		);
		
		// Small Marker
		$marker_types['small'] = array(
			'width' => 10,
			'height' => 17,
			'anchorX' => 5,
			'anchorY' => 17,
			'image' => get_bloginfo('url') . '/wp-content/uploads/wp-geo/markers/small-marker.png',
			'shadow' => get_bloginfo('url') . '/wp-content/wp-geo/markers/small-marker-shadow.png'
		);			
		
		// Dot Marker
		$marker_types['dot'] = array(
			'width' => 10,
			'height' => 17,
			'anchorX' => 5,
			'anchorY' => 17,
			'image' => get_bloginfo('url') . '/wp-content/uploads/wp-geo/markers/small-marker.png',
			'shadow' => get_bloginfo('url') . '/wp-content/wp-geo/markers/small-marker-shadow.png'
		);
		
		// Default return
		return $marker_types[$type];
		
	}
	


	/**
	 * GeoRSS Namespace
	 */
	function georss_namespace() 
	{
	
		if (WPGeo::show_maps())
		{			
			echo 'xmlns:georss="http://www.georss.org/georss"';
		}
	
	}



	/**
	 * GeoRSS Tag
	 */
	function georss_item() 
	{
	
		if (WPGeo::show_maps())
		{
			global $post;
			
			// Get the post
			$id = $post->ID;		
		
			// Get latitude and longitude
			$latitude = get_post_meta($post->ID, '_wp_geo_latitude', true);
			$longitude = get_post_meta($post->ID, '_wp_geo_longitude', true);
			
			// Need a map?
			if (is_numeric($latitude) && is_numeric($longitude))
			{
				echo '<georss:point>' . $latitude . ' ' . $longitude . '</georss:point>';
			}
		}
		
	}
	
	

}



// Init.
$wpgeo = new WPGeo();

// Hooks
register_activation_hook(__FILE__, array('WPGeo', 'register_activation'));
add_shortcode('wp_geo_map', array('WPGeo', 'shortcode_wpgeo_map'));
add_action('wp_print_scripts', array('WPGeo', 'includeGoogleMapsJavaScriptAPI'));

// Frontend Hooks
add_action('wp_head', array('WPGeo', 'wp_head'));
add_filter('the_content', array('WPGeo', 'the_content'));

// Admin Hooks
add_action('admin_menu', array('WPGeo', 'admin_menu'));
add_action('admin_head', array('WPGeo', 'admin_head'));
add_action('dbx_post_advanced', array('WPGeo', 'dbx_post_advanced'));
add_action('edit_page_form', array('WPGeo', 'dbx_post_advanced'));
add_action('save_post', array('WPGeo', 'save_post'));

// Feed Hooks
add_action('rss2_ns', array('WPGeo', 'georss_namespace'));
add_action('atom_ns', array('WPGeo', 'georss_namespace'));
add_action('rdf_ns', array('WPGeo', 'georss_namespace'));
add_action('rss_item', array('WPGeo', 'georss_item'));
add_action('rss2_item', array('WPGeo', 'georss_item'));
add_action('atom_entry', array('WPGeo', 'georss_item'));
add_action('rdf_item', array('WPGeo', 'georss_item'));

// Includes
include('wp-geo-widget.php');



?>