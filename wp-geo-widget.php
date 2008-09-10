<?php



/**
* WP Geo Widget
* @author Marco Alionso Ramirez, marco@onemarco.com
* @version 1.0
* Adds a geocoding widget to WordPress (requires WP Geo plugin)
*/




/**
 * The WP Geo Widget class
 */
class WPGeoWidget
{



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
	
		// Extract the widget options
	  	extract($args);
		$options = get_option('map_widget');

		// Get the options for the widget
		$title 		= empty( $options['title'] ) ? '' : apply_filters('widget_title', __($options['title']));
		$width 		= empty( $options['width'] ) ? '' : $options['width'];
		$height 	= empty( $options['height'] ) ? '' : $options['height'];
		$maptype 	= empty( $options['maptype'] ) ? '' : $options['maptype'];
		//$url 		= empty( $options['url'] ) ? '' : $options['url'];
		//$url_name 	= empty( $options['url'] ) ? '' : $options['url_name'];
		
		// Start write widget
		$html_content = '';
		$map_content = WPGeoWidget::add_map($width, $height, $maptype);
		
		if (!empty($map_content))
		{
			$html_content = $before_widget . $before_title . $title . $after_title . WPGeoWidget::add_map($width, $height, $maptype);
			//$html_content .= '<p><a href="http://maps.google.ch/maps?f=q&hl=de&geocode=&q=' . $url . '&ie=UTF8&t=h&z=6" target="_blank">' . $url_name . '</a></p>';
			$html_content .= $after_widget;
		}
		
		echo $html_content;	
		
	}	
	
	
	
	/**
	 * Control panel for the map widget
	 */
	function map_widget_control() 
	{
	
		$options = $newoptions = get_option('map_widget');
		
		// Get the options
		if ($_POST['wpgeo-submit']) 
		{
			$newoptions['title'] 	= strip_tags(stripslashes($_POST['wpgeo-title']));
			$newoptions['width'] 	= strip_tags(stripslashes($_POST['wpgeo-width']));
			$newoptions['height'] 	= strip_tags(stripslashes($_POST['wpgeo-height']));
			$newoptions['maptype'] 	= strip_tags(stripslashes($_POST['google_map_type']));
			//$newoptions['url'] 		= strip_tags(stripslashes($_POST['wpgeo-rss-url']));
			//$newoptions['url_name'] = strip_tags(stripslashes($_POST['wpgeo-rss-url-name']));
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
		//$url 		= attribute_escape($options['url']);
		//$url_name 	= attribute_escape($options['url_name']);
		
		// Write the widget controls
		echo '
			<p><label for="wpgeo-title">Title: <input class="widefat" id="wpgeo-title" name="wpgeo-title" type="text" value="' . $title . '" /></label></p>
			<p><label for="wpgeo-width">Width: <input class="widefat" id="wpgeo-width" name="wpgeo-width" type="text" value="' . $width . '" /></label></p>
			<p><label for="wpgeo-height">Height: <input class="widefat" id="wpgeo-height" name="wpgeo-height" type="text" value="' . $height . '" /></label></p>';
		echo '<p>' . WPGeo::google_map_types('menu', $maptype) . '</p>';
		/*
		echo '
			<p><label for="wpgeo-rss-url-name">View enlarged: <input class="widefat" id="wpgeo-rss-url-name" name="wpgeo-rss-url-name" type="text" value="' . $url_name . '" /></label></p>
			<p><label for="wpgeo-rss-url">View enlarged GeoRSS: <input class="widefat" id="wpgeo-rss-url" name="wpgeo-rss-url" type="text" value="' . $url . '" /></label></p>';
		*/
		echo '<input type="hidden" id="wpgeo-submit" name="wpgeo-submit" value="1" />';
	
	}	
	
	
	
	/**
	 * Add the map to the widget
	 * TODO: integrate the code better into the existing one
	 */
	function add_map($width = '100%', $height = 150, $maptype = '') 
	{
	
		global $posts;
		
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
					'id' => $post->ID,
					'latitude' => $latitude,
					'longitude' => $longitude,
					'title' => $title
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
				$markers_js .= 'marker' . $i . ' = createMarker(new GLatLng(' . $coords[$i]['latitude'] . ', ' . $coords[$i]['longitude'] . '), "' . $coords[$i]['title'] . '", "' . get_permalink($coords[$i]['id']) . '");' . "\n";
			}
						
			// Html JS
			WPGeo::includeGoogleMapsJavaScriptAPI();
			$html_js .= '<script type="text/javascript" src="' . get_bloginfo('url') . '/wp-content/plugins/wp-geo/js/Tooltip.js"></script>';
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
			
				GEvent.addDomListener(window,"load",loadMap);
				GEvent.addDomListener(window,"unload",GUnload);
				
				
				
				/**
				* Check for Google maps compatibility and load the map
				*/
				
				function loadMap()
				{
					if(GBrowserIsCompatible()) 
					{
						createMap();
						//initWPGeoWidget();
					}
					else
					{
						alert("Sorry, the Google Maps API is not compatible with this browser.");
						return;
					}
				}
			
			
			
				/**
				* Create the map
				*/
				
				function createMap()
				{
					map = new GMap2(document.getElementById("wp_geo_map_widget"));
					map.addControl(new GSmallZoomControl());
					map.setCenter(new GLatLng(0, 0), 0);
					map.setMapType(' . $maptype . ');
							
					bounds = new GLatLngBounds();		
					
					// Add the markers	
					'.	$markers_js .'
									
					// draw the polygonal lines between points
					drawPolylines(' . $polyline_coords_js . ', "#000000", 2, 0.50);
							
					// Center the map to show all markers
					var center = bounds.getCenter();
					var zoom = map.getBoundsZoomLevel(bounds)
					
					map.setCenter(center, zoom);
				}
		
				
				/**
				* Create a marker for the map
				*/
				function createMarker(latlng, title, link) 
				{	
					// Create the custom icon for the marker			
					var icon = createIcon(10, 17, 5, 17, "' . get_bloginfo('url') . '/wp-content/uploads/wp-geo/markers/small-marker.png", "' . get_bloginfo('url') . '/wp-content/wp-geo/markers/small-marker-trans.png");
								
					// Create the marker
					var marker = new GMarker(latlng, icon);
				
					// Create a custom tooltip
					var tooltip = new Tooltip(marker,title,2)
					
					marker.tooltip = tooltip;
					marker.title = title;
					marker.link = link;
					marker.latlng = latlng;
					
					GEvent.addListener(marker, "mouseover", overHandler);
					GEvent.addListener(marker, "mouseout", outHandler);
					GEvent.addListener(marker, "click", clickHandler);
		
					map.addOverlay(marker);
					map.addOverlay(tooltip);
					
					bounds.extend(marker.getPoint());
					
					return marker;
				}
				
				
				/**
				* Create a custom marker icon for the map
				*/
				function createIcon(width, height, anchorX, anchorY, image, transparent) 
				{
					var icon = new GIcon();
					
					icon.image = image;
					icon.iconSize = new GSize(width, height);
					icon.iconAnchor = new GPoint(anchorX, anchorY);
					icon.shadow = transparent;
					
					return icon;
				}
				
		
				/**
				* Draw the polygonal lines between markers
				*/
				function drawPolylines(coords, color, thickness, alpha)
				{
					var polyOptions = {clickable:true, geodesic:true};
					var polyline = new GPolyline(coords, color, thickness, alpha, polyOptions);
					
					map.addOverlay(polyline);		
				}
				
				
				/**
				* Handles the roll over event for a marker
				*/
				function overHandler() 
				{
					if(!(this.isInfoWindowOpen) && !(this.isHidden())){
						this.tooltip.show();
					}
				}
				
					
				/**
				* Handles the roll out event for a marker
				*/
				function outHandler() 
				{
					this.tooltip.hide();
				}
				
				
				/**
				* Handles the click event for a marker
				*/
				function clickHandler() 
				{
					window.location.href= this.link;
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



// Widget Hooks
add_action('init', array('WPGeoWidget', 'init_map_widget'));



?>