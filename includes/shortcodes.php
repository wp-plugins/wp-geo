<?php



/**
* @package WP Geo
* @subpackage Includes > Shortcodes
*/



// Add Shortcodes
add_shortcode( 'wp_geo_map', 'shortcode_wpgeo_map' );
add_shortcode( 'wpgeo_longitude', 'shortcode_wpgeo_longitude' );
add_shortcode( 'wpgeo_latitude', 'shortcode_wpgeo_latitude' );



/**
 * @method       Shortcode: [wpgeo_latitude]
 * @description  Outputs the post latitude.
 * @return       (Float) Latitude
 */

function shortcode_wpgeo_latitude( $atts, $content = null ) {

	global $post;
	return get_wpgeo_longitude($post->ID);
	
}



/**
 * @method       Shortcode: [wpgeo_longitude]
 * @description  Outputs the post longitude.
 * @return       (Float) Longitude
 */

function shortcode_wpgeo_longitude( $atts, $content = null ) {

	global $post;
	return get_wpgeo_latitude($post->ID);
	
}



/**
 * @method       Shortcode: [wp_geo_map type="G_NORMAL_MAP"]
 * @description  Outputs the post map.
 * @return       HTML required to display map
 */

function shortcode_wpgeo_map( $atts, $content = null ) {

	global $post, $wpgeo;
	
	$id = $post->ID;
	$wp_geo_options = get_option( 'wp_geo_options' );
	
	if ( $wpgeo->show_maps() && $wp_geo_options['show_post_map'] == 'HIDE' && $wpgeo->checkGoogleAPIKey() ) {
		
		$map_atts = array(
			'width' => null,
			'height' => null,
			'lat' => null,
			'long' => null,
			'type' => 'G_NORMAL_MAP',
			'escape' => false
		);
		extract( shortcode_atts( $map_atts, $atts ) );
		
		// Escape?
		if ( $escape == 'true' ) {
			return '[wp_geo_map]';
		}
		
		$map_width = $wp_geo_options['default_map_width'];
		$map_height = $wp_geo_options['default_map_height'];
		
		if ( $atts['width'] != null ) {
			$map_width = $atts['width'];
			if ( is_numeric( $map_width ) ) {
				$map_width = $map_width . 'px';
			}
		}
		if ( $atts['height'] != null ) {
			$map_height = $atts['height'];
			if ( is_numeric( $map_height ) ) {
				$map_height = $map_height . 'px';
			}
		}
	
		// To Do: Add in lon/lat check and output map if needed
		
		return '<div class="wp_geo_map" id="wp_geo_map_' . $id . '" style="width:' . $map_width . '; height:' . $map_height . ';">' . $content . '</div>';
	
	} else {
	
		return '';
	
	}
	
}



?>