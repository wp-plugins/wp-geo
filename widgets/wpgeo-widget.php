<?php

/**
 * WP Geo Widget
 * A base class providing common WP Geo functionality for all widgets.
 */
class WPGeo_Widget extends WP_Widget {

	/**
	 * Constuctor
	 */
	function WPGeo_Widget( $id_base = false, $name, $widget_options = array(), $control_options = array() ) {
		$this->WP_Widget( $id_base, $name, $widget_options, $control_options );
		add_action( 'wpgeo_widget_form_fields', array( $this, 'widget_form_fields_default' ), 5, 2 );
		add_action( 'wpgeo_widget_form_fields', array( $this, 'widget_form_fields_settings' ), 9, 2 );
	}
	
	/**
	 * Wrap Content
	 *
	 * @param string $content Widget HTML content.
	 * @param array $args Parameters.
	 * @param array $instance Widget instance.
	 * @return string HTML widget output.
	 */
	function wrap_content( $content, $args, $instance ) {
		if ( ! empty( $content ) ) {
			$html = $args['before_widget'];
			if ( ! empty( $instance['title'] ) )
				$html .= $args['before_title'] . $instance['title'] . $args['after_title'];
			$html .= $content . $args['after_widget'];
			return $html;
		}
		return '';
	}
	
	/**
	 * Validate Yes/No
	 *
	 * @param string $yesno String to check for 'Y' or 'N'.
	 * @return bool.
	 */
	function validate_yesno( $yesno ) {
		return in_array( $yesno, array( 'Y', 'N' ) ) ? $yesno : '';
	}
	
	/**
	 * Validate string
	 *
	 * @param string $string String to filter.
	 * @return string Validated string.
	 */
	function validate_string( $string ) {
		return strip_tags( stripslashes( $string ) );
	}
	
	/**
	 * Validate widget instance
	 *
	 * @param array $instance Widget values.
	 * @return array Widget values.
	 */
	function validate_instance( $instance ) {
		$wp_geo_options = get_option( 'wp_geo_options' );
		$validated_instance = wp_parse_args( $instance, array(
			'title'          => 'Map',
			'width'          => '100%',
			'height'         => '150',
			'number'         => 1,
			'maptype'        => $wp_geo_options['google_map_type'],
			'show_polylines' => '',
			'zoom'           => $wp_geo_options['default_map_zoom'],
			'post_type'      => array( 'post' ),
		) );
		
		// Validation
		if ( $validated_instance['zoom'] === null ) {
			$validated_instance['zoom'] = $wp_geo_options['default_map_zoom'];
		}
		if ( ! is_array( $validated_instance['post_type'] ) ) {
			$validated_instance['post_type'] = array( $validated_instance['post_type'] );
		}
		return $validated_instance;
	}
	
	/**
	 * Validate widget display instance
	 *
	 * @param array $instance Widget values.
	 * @return array Widget values.
	 */
	function validate_display_instance( $instance ) {
		$wp_geo_options = get_option( 'wp_geo_options' );

		// Validate the instance
		$instance['title']   = empty( $instance['title'] ) ? '' : apply_filters( 'widget_title', __( $instance['title'] ) );
		$instance['width']   = empty( $instance['width'] ) ? '' : $instance['width'];
		$instance['height']  = empty( $instance['height'] ) ? '' : $instance['height'];
		$instance['maptype'] = empty( $instance['maptype'] ) ? '' : $instance['maptype'];
		if ( $instance['show_polylines'] == 'Y' || $instance['show_polylines'] == 'N' ) {
			$instance['show_polylines'] = $instance['show_polylines'] == 'Y' ? true : false;
		} else {
			$instance['show_polylines'] = $wp_geo_options['show_polylines'] == 'Y' ? true : false;
		}
		$instance['zoom']    = is_numeric( $instance['zoom'] ) ? $instance['zoom'] : $wp_geo_options['default_map_zoom'];
		return $instance;
	}
	
	/**
	 * Validate Update
	 *
	 * @param array $new_instance New widget values.
	 * @param array $old_instance Old widget values.
	 * @return array New values.
	 */
	function validate_update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title']          = $this->validate_string( $new_instance['title'] );
		$instance['width']          = $this->validate_string( $new_instance['width'] );
		$instance['height']         = $this->validate_string( $new_instance['height'] );
		$instance['maptype']        = $this->validate_string( $new_instance['maptype'] );
		$instance['show_polylines'] = $this->validate_yesno( $new_instance['show_polylines'] );
		$instance['zoom']           = absint( $new_instance['zoom'] );
		return $instance;
	}
	
	/**
	 * Check API Key Message
	 * Returns a message is no Google API Key set.
	 *
	 * @todo Check if there is a 'less hard-coded' way to write link to settings page
	 *
	 * @return string HTML message.
	 */
	function check_api_key_message() {
		global $wpgeo;
		if ( ! $wpgeo->checkGoogleAPIKey() ) {
			return '<p class="wp_geo_error">' . __( 'WP Geo is not currently active as you have not entered a Google API Key', 'wp-geo') . '. <a href="' . admin_url( '/options-general.php?page=wp-geo/includes/wp-geo.php' ) . '">' . __( 'Please update your WP Geo settings', 'wp-geo' ) . '</a>.</p>';
		}
		return '';
	}

	/**
	 * Add widget map
	 *
	 * @param array $args Args.
	 * @return string Output.
	 */
	function add_widget_map( $args = null ) {
		global $wpgeo, $post;
		$wp_geo_options = get_option( 'wp_geo_options' );
		$current_post = $post->ID;
		
		$html_js = '';
		$markers_js = '';
		$polyline_js = '';
		
		$args = wp_parse_args( $args, array(
			'width'         => '100%',
			'height'        => 150,
			'maptype'       => empty( $wp_geo_options['google_map_type'] ) ? 'G_NORMAL_MAP' : $wp_geo_options['google_map_type'],
			'show_polylines' => false,
			'zoom'          => $wp_geo_options['default_map_zoom'],
			'id'            => 'wpgeo_widget_map',
			'posts'         => null
		) );
		if ( ! $args['posts'] )
			return $html_js;
		
		// If Google API Key...
		if ( $wpgeo->checkGoogleAPIKey() ) {
			
			// Find the coordinates for the posts
			$coords = array();
			for ( $i = 0; $i < count( $args['posts'] ); $i++ ) {
				$post 		= $args['posts'][$i];
				$latitude 	= get_post_meta( $post->ID, WPGEO_LATITUDE_META, true );
				$longitude 	= get_post_meta( $post->ID, WPGEO_LONGITUDE_META, true );
				$post_id 	= get_post( $post->ID );
				$title 	    = get_post_meta( $post->ID, WPGEO_TITLE_META, true );
				if ( empty( $title ) )
					$title = $post->post_title;
				if ( wpgeo_is_valid_geo_coord( $latitude, $longitude ) ) {
					array_push( $coords, array(
						'id' 		=> $post->ID,
						'latitude' 	=> $latitude,
						'longitude' => $longitude,
						'title' 	=> $title,
						'post'		=> $post
					) );
				}
			}
			
			// Only show map widget if there are coords to show
			if ( count( $coords ) > 0 ) {
				
				// Polylines
				if ( $args['show_polylines'] ) {
					$polyline = new WPGeo_Polyline( array(
						'color' => $wp_geo_options['polyline_colour']
					) );
					for ( $i = 0; $i < count( $coords ); $i++ ) {
						$polyline->add_coord( $coords[$i]['latitude'], $coords[$i]['longitude'] );
					}
					$polyline_js = WPGeo_API_GMap2::render_map_overlay( 'map', WPGeo_API_GMap2::render_polyline( $polyline ) );
				}
				
				// Markers
				for ( $i = 0; $i < count( $coords ); $i++ ) {
					$icon = 'wpgeo_icon_' . apply_filters( 'wpgeo_marker_icon', 'small', $coords[$i]['post'], 'widget' );
					$markers_js .= 'marker' . $i . ' = wpgeo_createMarker(new GLatLng(' . $coords[$i]['latitude'] . ', ' . $coords[$i]['longitude'] . '), ' . $icon . ', "' . addslashes( __( $coords[$i]['title'] ) ) . '", "' . get_permalink( $coords[$i]['id'] ) . '");' . "\n";
				}
				
				$wpgeo->includeGoogleMapsJavaScriptAPI();
				$small_marker = $wpgeo->markers->get_marker_by_id( 'small' );
				
				$html_js .= '
					<script type="text/javascript">
					//<![CDATA[
					
					/**
					 * Widget Map (' . $args['id'] . ')
					 */
					
					// Define variables
					var map = "";
					var bounds = "";
					
					// Add events to load the map
					GEvent.addDomListener(window, "load", createMapWidget);
					GEvent.addDomListener(window, "unload", GUnload);
					
					// Create the map
					function createMapWidget() {
						if (GBrowserIsCompatible()) {
							map = new GMap2(document.getElementById("' . $args['id'] . '"));
							' . WPGeo_API_GMap2::render_map_control( 'map', 'GSmallZoomControl3D' ) . '
							map.setCenter(new GLatLng(0, 0), 0);
							map.setMapType(' . $args['maptype'] . ');
							bounds = new GLatLngBounds();
							
							// Add the markers	
							'.	$markers_js .'
							
							// Draw the polygonal lines between points
							' . $polyline_js . '
							
							// Center the map to show all markers
							var center = bounds.getCenter();
							var zoom = map.getBoundsZoomLevel(bounds)
							if (zoom > ' . $args['zoom'] . ') {
								zoom = ' . $args['zoom'] . ';
							}
							map.setCenter(center, zoom);
							
							' . apply_filters( 'wpgeo_map_js_preoverlays', '', 'map' ) . '
						}
					}
					
					//]]>
					</script>';
				
				$html_js .= '<div class="wp_geo_map" id="' . $args['id'] . '" style="width:' . wpgeo_css_dimension( $args['width'] ) . '; height:' . wpgeo_css_dimension( $args['height'] ) . ';"></div>';
			}
			return $html_js;
		}
	}
	
	/**
	 * Default Fields
	 * Title, width and height fields.
	 *
	 * @param array $instance Widget values.
	 * @param object $widget Widget.
	 */
	function widget_form_fields_default( $instance, $widget ) {
		if ( $widget == $this ) {
			echo '
				<p><label for="' . $this->get_field_id( 'title' ) . '">' . __( 'Title', 'wp-geo' ) . ': <input class="widefat" id="' . $this->get_field_id( 'title' ) . '" name="' . $this->get_field_name( 'title' ) . '" type="text" value="' . $instance['title'] . '" /></label></p>
				<p><label for="' . $this->get_field_id( 'width' ) . '">' . __( 'Width', 'wp-geo' ) . ': <input class="widefat" id="' . $this->get_field_id( 'width' ) . '" name="' . $this->get_field_name( 'width' ) . '" type="text" value="' . $instance['width'] . '" /></label></p>
				<p><label for="' . $this->get_field_id( 'height' ) . '">' . __( 'Height', 'wp-geo' ) . ': <input class="widefat" id="' . $this->get_field_id( 'height' ) . '" name="' . $this->get_field_name( 'height' ) . '" type="text" value="' . $instance['height'] . '" /></label></p>';
		}
	}
	
	/**
	 * Settings Fields
	 *
	 * @param array $instance Widget values.
	 * @param object $widget Widget.
	 */
	function widget_form_fields_settings( $instance, $widget ) {
		global $wpgeo;
		if ( $widget == $this ) {
			echo '<p><strong>' . __( 'Zoom', 'wp-geo' ) . ':</strong> ' . $wpgeo->selectMapZoom( null, null, array( 'return' => 'menu', 'selected' => $instance['zoom'], 'id' => $this->get_field_id( 'zoom' ), 'name' => $this->get_field_name( 'zoom' ) ) ) . '<br />
			<small>' . __( 'If not all markers fit, the map will automatically be zoomed so they do.', 'wp-geo' ) . '</small></p>';
			echo '<p><strong>' . __( 'Settings', 'wp-geo' ) . ':</strong></p>';
			echo '<p>' . __( 'Map Type', 'wp-geo' ) . ':<br />' . $wpgeo->google_map_types( null, null, array( 'return' => 'menu', 'selected' => $instance['maptype'], 'id' => $this->get_field_id( 'maptype' ), 'name' => $this->get_field_name( 'maptype' ) ) ) . '</p>';
			echo '<p>' . __( 'Polylines', 'wp-geo' ) . ':<br />' . wpgeo_show_polylines_options( array( 'return' => 'menu', 'selected' => $instance['show_polylines'], 'id' => $this->get_field_id( 'show_polylines' ), 'name' => $this->get_field_name( 'show_polylines' ) ) ) . '</p>';
		}
	}
	
}

?>