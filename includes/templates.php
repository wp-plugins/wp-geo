<?php



/**
 * @package    WP Geo
 * @subpackage Includes > Template
 * @author     Ben Huson <ben@thewhiteroom.net>
 */



/**
 * @method       WP Geo Latitude
 * @description  Outputs the post latitude.
 * @param        $post_id = Post ID (optional)
 */

function wpgeo_latitude( $post_id = null ) {

	echo get_wpgeo_latitude( $post_id );
	
}



/**
 * @method       WP Geo Longitude
 * @description  Outputs the post longitude.
 * @param        $post_id = Post ID (optional)
 */

function wpgeo_longitude( $post_id = null ) {

	echo get_wpgeo_longitude( $post_id );
	
}



/**
 * @method       WP Geo Title
 * @description  Outputs the post title.
 * @param        $post_id = Post ID (optional)
 * @param        $default_to_post_title = Default to post title if point title empty (optional)
 */

function wpgeo_title( $post_id = null, $default_to_post_title = true ) {

	echo get_wpgeo_title( $post_id, $default_to_post_title );
	
}



/**
 * @method       Get WP Geo Latitude
 * @description  Gets the post latitude.
 * @param        $post_id = Post ID (optional)
 * @return       (float) Latitude
 */

function get_wpgeo_latitude( $post_id = null ) {

	global $post;
	
	$id = absint($post_id) > 0 ? absint($post_id) : $post->ID;
	
	if ( absint($id) > 0 ) {
		return get_post_meta( absint($id), WPGEO_LATITUDE_META, true );
	}
	
	return null;
	
}



/**
 * @method       Get WP Geo Longitude
 * @description  Gets the post longitude.
 * @param        $post_id = Post ID (optional)
 * @return       (float) Longitude
 */

function get_wpgeo_longitude( $post_id = null ) {
	
	global $post;
	
	$id = absint($post_id) > 0 ? absint($post_id) : $post->ID;
	
	if ( absint($id) > 0 ) {
		return get_post_meta( absint($id), WPGEO_LONGITUDE_META, true );
	}
	
	return null;
	
}



/**
 * @method       Get WP Geo Title
 * @description  Gets the post title.
 * @param        $post_id = Post ID (optional)
 * @param        $default_to_post_title = Default to post title if point title empty (optional)
 * @return       (string) Title
 */

function get_wpgeo_title( $post_id = null, $default_to_post_title = true ) {
	
	global $post;
	
	$id = absint( $post_id ) > 0 ? absint( $post_id ) : $post->ID;
	
	if ( absint( $id ) > 0 ) {
		$title = get_post_meta( $id, WPGEO_TITLE_META, true );
		if ( empty( $title ) && $default_to_post_title ) {
			$p = &get_post( $id );
			$title = isset( $p->post_title ) ? $p->post_title : '';
		}
		$title = apply_filters( 'wpgeo_point_title', $title, $id );
		return $title;
	}
	
	return null;
	
}



/**
 * @method       WP Geo Post Map
 * @description  Outputs the HTML for a post map.
 * @param        $post_id = Post ID (optional)
 */

function wpgeo_post_map( $post_id = null ) {

	echo get_wpgeo_post_map( $post_id );
	
}



/**
 * @method       Get WP Geo Post Map
 * @description  Gets the HTML for a post map.
 * @param        $post_id = Post ID (optional)
 * @return       (string) HTML
 */

function get_wpgeo_post_map( $post_id = null ) {

	global $post, $wpgeo;
	
	$id = absint($post_id) > 0 ? absint($post_id) : $post->ID;
	$wp_geo_options = get_option( 'wp_geo_options' );
	
	$show_post_map = apply_filters( 'wpgeo_show_post_map', $wp_geo_options['show_post_map'], $id );
	
	if ( $id > 0 && !is_feed() ) {
		if ( $wpgeo->show_maps() && $show_post_map != 'TOP' && $show_post_map != 'BOTTOM' && $wpgeo->checkGoogleAPIKey() ) {
			
			$map_width = $wp_geo_options['default_map_width'];
			$map_height = $wp_geo_options['default_map_height'];
		
			if ( is_numeric( $map_width ) ) {
				$map_width = $map_width . 'px';
			}
		
			if ( is_numeric( $map_height ) ) {
				$map_height = $map_height . 'px';
			}
			
			return '<div class="wp_geo_map" id="wp_geo_map_' . $id . '" style="width:' . $map_width . '; height:' . $map_height . ';"></div>';
		}
	}
	
	return '';
	
}



/**
 * @method  Get WP Geo Map
 */

function get_wpgeo_map( $query, $options = null ) {
	
	global $wpgeo_map_id;
	
	$wpgeo_map_id++;
	
	$id = 'wpgeo_map_id_' . $wpgeo_map_id;
	
	$wp_geo_options = get_option('wp_geo_options');
	$maptype = 'ROADMAP';
	
	$posts = get_posts( $query );
	
	$output = '
		<div id="' . $id . '" class="wpgeo_map" style="width:' . $wp_geo_options['default_map_width'] . '; height:' . $wp_geo_options['default_map_height'] . ';"></div>
		<script type="text/javascript">
		<!--
		jQuery(window).load( function() {
			var bounds;
			
			var myOptions = {
				zoom: wpgeo_zoom,
				disableDefaultUI: true,
				navigationControl: true,
				mapTypeId: google.maps.MapTypeId.' . $maptype . ',
			}		
			var map_' . $id . ' = new google.maps.Map(document.getElementById("' . $id . '"), myOptions);
			
			';
	
	$marker_count = 0;
	foreach ( $posts as $post ) {
		$latitude = get_post_meta( $post->ID, WPGEO_LATITUDE_META, true );
		$longitude = get_post_meta( $post->ID, WPGEO_LONGITUDE_META, true );
		if ( is_numeric( $latitude ) && is_numeric( $longitude ) ) {
			$icon = 'wpgeo_icon_' . apply_filters( 'wpgeo_marker_icon', 'wpgeo_icon_small', $post, 'wpgeo_map' );
			$output .= '
				var ' . $id . '_' . $marker_count . ' = wpgeo_createMarker(map_' . $id . ', new google.maps.LatLng(' . $latitude . ', ' . $longitude . '), ' . $icon . ', "' . esc_js( __($post->post_title) ) . '", "' . get_permalink($post->ID) . '");
				';
			if ( $marker_count > 0 ) {
				$output .= 'bounds.extend(new google.maps.LatLng(' . $latitude . ', ' . $longitude . '));';
			} else {
				$output .= 'bounds = new google.maps.LatLngBounds(new google.maps.LatLng(' . $latitude . ', ' . $longitude . '));';
			}
			$marker_count++;
		}
	}
	
	$output .= '
			map_' . $id . '.fitBounds(bounds);
		} );
		-->
		</script>
		';
	
	
	/*
$markers_js .= '
					var point = new google.maps.LatLng(' . $coords[0]['latitude'] . ', ' . $coords[0]['longitude'] . ');
					bounds = new google.maps.LatLngBounds(point, point);';
				for ( $i = 0; $i < count($coords); $i++ ) {
					$icon = apply_filters( 'wpgeo_marker_icon', 'wpgeo_icon_small', $coords[$i]['post'], 'widget' );
					$markers_js .= '
					marker_' . $i . ' = wpgeo_createMarker(map, new google.maps.LatLng(' . $coords[$i]['latitude'] . ', ' . $coords[$i]['longitude'] . '), ' . $icon . ', "' . addslashes(__($coords[$i]['title'])) . '", "' . get_permalink($coords[$i]['id']) . '");
				
					';
					if ( $i > 0 ) {
						$markers_js .= 'bounds.extend(new google.maps.LatLng(' . $coords[$i]['latitude'] . ', ' . $coords[$i]['longitude'] . '));';
					}
				}
				$markers_js .= 'map.fitBounds(bounds);';
						
				// Html JS
				$wpgeo->includeGoogleMapsJavaScriptAPI();
				
				$small_marker = $wpgeo->markers->get_marker_by_id('small');
				
				$html_js .= '
					<script type="text/javascript">
					//<![CDATA[
			
					
					var map;
					var bounds;
					var center = new google.maps.LatLng(0, 0, 0);
					
					function createMapWidget() {
						
						var myOptions = {
							zoom: 0,
							center: center,
							disableDefaultUI: true,
							navigationControl: true,
							mapTypeId: google.maps.MapTypeId.' . $maptype . ',
						}
						map = new google.maps.Map(document.getElementById("wp_geo_map_widget"), myOptions);
						
						// Add the markers	
						'.	$markers_js .'
										
						// draw the polygonal lines between points
						';
				
		
			
			$html_js .='
						// Center the map to show all markers
						map.fitBounds(bounds);
	*/
	
	return $output;
	
}



/**
 * @method  WP Geo Map
 */

function wpgeo_map( $query, $options = null ) {

	echo get_wpgeo_map( $query, $options );
	
}



?>