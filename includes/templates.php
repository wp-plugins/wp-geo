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
 * @method  Get WP Geo Map
 */

function get_wpgeo_map( $query, $options = null ) {
	
	global $wpgeo_map_id;
	
	$wpgeo_map_id++;
	
	$id = 'wpgeo_map_id_' . $wpgeo_map_id;
	
	$wp_geo_options = get_option('wp_geo_options');
	
	$posts = get_posts( $query );
	
	$output = '
		<div id="' . $id . '" class="wpgeo_map" style="width:' . $wp_geo_options['default_map_width'] . '; height:' . $wp_geo_options['default_map_height'] . ';"></div>
		<script type="text/javascript">
		<!--
		jQuery(window).load( function() {
			if ( GBrowserIsCompatible() ) {
				var bounds = new GLatLngBounds();
				map = new GMap2(document.getElementById("' . $id . '"));
				map.addControl(new GLargeMapControl3D());
				';
	foreach ( $posts as $post ) {
		$latitude = get_post_meta($post->ID, WPGEO_LATITUDE_META, true);
		$longitude = get_post_meta($post->ID, WPGEO_LONGITUDE_META, true);
		if ( is_numeric($latitude) && is_numeric($longitude) ) {
			$output .= '
				var center = new GLatLng(' . $latitude . ',' . $longitude . ');
				var marker = new wpgeo_createMarker2(map, center, wpgeo_icon_small, \'' . $post->post_title . '\', \'' . get_permalink($post->ID) . '\');
				bounds.extend(center);
				';
		}
	}
	$output .= '
				zoom = map.getBoundsZoomLevel(bounds);
				map.setCenter(bounds.getCenter(), zoom);
			}
		} );
		-->
		</script>
		';
	
	return $output;
	
}



/**
 * @method  WP Geo Map
 */

function wpgeo_map( $query, $options = null ) {

	echo get_wpgeo_map( $query, $options );
	
}



?>