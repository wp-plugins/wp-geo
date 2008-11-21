<?php



/**
* WP Geo Widget
* @author Marco Alionso Ramirez, marco@onemarco.com, updated by Ben Huson, ben@thewhiteroom.net
* @version 1.1
* Adds a geocoding widget to WordPress (requires WP Geo plugin)
*/




/**
 * The WP Geo Widget class
 */
class WPGeoWidget
{



	/**
	 * Properties
	 */
	 
	var $version = '1.1';
	
	

	/**
	 * Initialize the map widget
	 */
	function init_map_widget()
	{
	
		// This registers the widget so it appears in the sidebar
		register_sidebar_widget('WP Geo', array('WPGeoWidget', 'map_widget'));
	
		// This registers the  widget control form
		register_widget_control('WP Geo', array('WPGeoWidget', 'map_widget_control'));
	
	}
	
	
	
	/**
	 * Widget to display a map in the sidebar
	 */
	function map_widget($args) 
	{	
	
		global $wpgeo;
		
		// If Google API Key...
		if ($wpgeo->checkGoogleAPIKey())
		{
		
			// Extract the widget options
			extract($args);
			$options = get_option('map_widget');
	
			// Get the options for the widget
			$title 		= empty( $options['title'] ) ? '' : apply_filters('widget_title', __($options['title']));
			$width 		= empty( $options['width'] ) ? '' : $options['width'];
			$height 	= empty( $options['height'] ) ? '' : $options['height'];
			$maptype 	= empty( $options['maptype'] ) ? '' : $options['maptype'];
			
			// Start write widget
			$html_content = '';
			$map_content = WPGeoWidget::add_map($width, $height, $maptype);
			
			if (!empty($map_content))
			{
				$html_content = $before_widget . $before_title . $title . $after_title . WPGeoWidget::add_map($width, $height, $maptype);
				$html_content .= $after_widget;
			}
			
			echo $html_content;
		
		}
		
	}	
	
	
	
	/**
	 * Control panel for the map widget
	 */
	function map_widget_control() 
	{
		
		global $wpgeo;
		$options = $newoptions = get_option('map_widget');
		
		// Get the options
		if ($_POST['wpgeo-submit']) 
		{
			$newoptions['title'] 	= strip_tags(stripslashes($_POST['wpgeo-title']));
			$newoptions['width'] 	= strip_tags(stripslashes($_POST['wpgeo-width']));
			$newoptions['height'] 	= strip_tags(stripslashes($_POST['wpgeo-height']));
			$newoptions['maptype'] 	= strip_tags(stripslashes($_POST['google_map_type']));
		}
		
		// Set the options when they differ
		if ($options != $newoptions)
		{
			$options = $newoptions;
			update_option('map_widget', $options);
		}
	
		// Clean up the options
		$title 		= attribute_escape($options['title']);
		$width 		= attribute_escape($options['width']);
		$height 	= attribute_escape($options['height']);
		$maptype 	= attribute_escape($options['maptype']);
		
		// Write the widget controls
		if (!$wpgeo->checkGoogleAPIKey())
		{
			// NOTE: Check if there is a 'less hard-coded' way to write link to settings page
			echo '<p class="wp_geo_error">WP Geo is not currently active as you have not entered a Google API Key. Please <a href="' . get_bloginfo('url') . '/wp-admin/options-general.php?page=wp-geo/wp-geo.php">update your WP Geo settings</a>.</p>';
		}
		echo '
			<p><label for="wpgeo-title">' . __('Title', 'wp-geo') . ': <input class="widefat" id="wpgeo-title" name="wpgeo-title" type="text" value="' . $title . '" /></label></p>
			<p><label for="wpgeo-width">' . __('Width', 'wp-geo') . ': <input class="widefat" id="wpgeo-width" name="wpgeo-width" type="text" value="' . $width . '" /></label></p>
			<p><label for="wpgeo-height">' . __('Height', 'wp-geo' . ': <input class="widefat" id="wpgeo-height" name="wpgeo-height" type="text" value="' . $height . '" /></label></p>';
		echo '<p>' . $wpgeo->google_map_types('menu', $maptype) . '</p>';
		echo '<input type="hidden" id="wpgeo-submit" name="wpgeo-submit" value="1" />';
	
	}	
	
	
	
	/**
	 * Add the map to the widget
	 * TODO: integrate the code better into the existing one
	 */
	function add_map($width = '100%', $height = 150, $maptype = '') 
	{
	
		global $posts, $wpgeo;
		
		// If Google API Key...
		if ($wpgeo->checkGoogleAPIKey())
		{
		
			// Set default width and height
			if (empty($width))
			{
				$width = '100%';
			}
			if (empty($height))
			{
				$height = '150';
			}
			
			// Get the basic settings of wp geo
			$wp_geo_options = get_option('wp_geo_options');
			
			
			// Find the coordinates for the posts
			$coords = array();
			for ($i = 0; $i < count($posts); $i++)
			{
			
				$post 		= $posts[$i];
				$latitude 	= get_post_meta($post->ID, '_wp_geo_latitude', true);
				$longitude 	= get_post_meta($post->ID, '_wp_geo_longitude', true);
				$post_id 	= get_post($post->ID);
				$title 		= $post_id->post_title;
				
				if (is_numeric($latitude) && is_numeric($longitude))
				{
					$push = array(
						'id' 		=> $post->ID,
						'latitude' 	=> $latitude,
						'longitude' => $longitude,
						'title' 	=> $title
					);
					array_push($coords, $push);
				}
				
			}
			
			// Markers JS (output)
			$markers_js = '';
			
			// Only show map widget if there are coords to show
			if (count($coords) > 0)
			{
			
				$google_maps_api_key = $wp_geo_options['google_api_key'];
				$zoom = $wp_geo_options['default_map_zoom'];
				
				if (empty($maptype))
				{
					$maptype = empty($wp_geo_options['google_map_type']) ? 'G_NORMAL_MAP' : $wp_geo_options['google_map_type'];			
				}
				
				// Polyline JS
				$polyline_coords_js = '[';
				
				for ($i = 0; $i < count($coords); $i++)
				{
					$polyline_coords_js .= 'new GLatLng(' . $coords[$i]['latitude'] . ', ' . $coords[$i]['longitude'] . '),';
				}
				
				$polyline_coords_js .= ']';		
		
				for ($i = 0; $i < count($coords); $i++)
				{
					$markers_js .= 'marker' . $i . ' = wpgeo_createMarker(new GLatLng(' . $coords[$i]['latitude'] . ', ' . $coords[$i]['longitude'] . '), wpgeo_icon_small, "' . addslashes($coords[$i]['title']) . '", "' . get_permalink($coords[$i]['id']) . '");' . "\n";
				}
							
				// Html JS
				$wpgeo->includeGoogleMapsJavaScriptAPI();
				
				$small_marker = $wpgeo->markers->get_marker_meta('small');
				
				$html_js .= '
					<script type="text/javascript">
					//<![CDATA[
					
					
					
					/**
					* Define variables
					*/
					
					var map = "";
					var bounds = "";
					
					
					
					/**
					* Add events to load the map
					*/
				
					GEvent.addDomListener(window, "load", createMapWidget);
					GEvent.addDomListener(window, "unload", GUnload);
				
				
				
					/**
					* Create the map
					*/
					
					function createMapWidget()
					{
						if(GBrowserIsCompatible())
						{
							map = new GMap2(document.getElementById("wp_geo_map_widget"));
							map.addControl(new GSmallZoomControl());
							map.setCenter(new GLatLng(0, 0), 0);
							map.setMapType(' . $maptype . ');
									
							bounds = new GLatLngBounds();		
							
							// Add the markers	
							'.	$markers_js .'
											
							// draw the polygonal lines between points
							map.addOverlay(wpgeo_createPolyline(' . $polyline_coords_js . ', "#ffffff", 2, 0.50));
							
							// Center the map to show all markers
							var center = bounds.getCenter();
							var zoom = map.getBoundsZoomLevel(bounds)
							
							map.setCenter(center, zoom);
						}
					}
					
					
					//]]>
					</script>';
				
				// Set width and height
				if (is_numeric($width))
					$width = $width . 'px';
				if (is_numeric($height))
					$height = $height . 'px';
				
				$html_js .= '<div class="wp_geo_map" id="wp_geo_map_widget" style="width:' . $width . '; height:' . $height . ';"></div>';
			
			}
			
			return $html_js;
		
		}
		
	}
	
	
		
}



// Widget Hooks
add_action('init', array('WPGeoWidget', 'init_map_widget'));



?>