<?php



/**
* @package WP Geo
* @subpackage Includes > Template
*/



/**
 * @method       wpgeo_latitude
 * @description  Outputs the post latitude.
 * @param        $post_id = Post ID (optional)
 */

function wpgeo_latitude( $post_id = null ) {

	echo get_wpgeo_latitude( $post_id );
	
}



/**
 * @method       wpgeo_longitude
 * @description  Outputs the post longitude.
 * @param        $post_id = Post ID (optional)
 */

function wpgeo_longitude( $post_id = null ) {

	echo get_wpgeo_longitude( $post_id );
	
}



/**
 * @method       get_wpgeo_latitude
 * @description  Gets the post latitude.
 * @param        $post_id = Post ID (optional)
 * @return       (Float) Latitude
 */

function get_wpgeo_latitude( $post_id = null ) {

	global $post;
	
	$id = absint($post_id) > 0 ? absint($post_id) : $post->ID;
	
	if ( absint($id) > 0 ) {
		return get_post_meta( absint($id), '_wp_geo_latitude', true );
	}
	
	return null;
	
}



/**
 * @method       get_wpgeo_longitude
 * @description  Gets the post longitude.
 * @param        $post_id = Post ID (optional)
 * @return       (Float) Longitude
 */

function get_wpgeo_longitude( $post_id = null ) {
	
	global $post;
	
	$id = absint($post_id) > 0 ? absint($post_id) : $post->ID;
	
	if ( absint($id) > 0 ) {
		return get_post_meta( absint($id), '_wp_geo_longitude', true );
	}
	
	return null;
	
}



?>