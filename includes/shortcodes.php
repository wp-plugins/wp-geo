<?php



/**
 * @package    WP Geo
 * @subpackage Includes > Shortcodes
 * @author     Ben Huson <ben@thewhiteroom.net>
 */



// Add Shortcodes
add_shortcode( 'wp_geo_map', 'shortcode_wpgeo_map' );
add_shortcode( 'wpgeo_longitude', 'shortcode_wpgeo_longitude' );
add_shortcode( 'wpgeo_latitude', 'shortcode_wpgeo_latitude' );
add_shortcode( 'wpgeo_map_link', 'shortcode_wpgeo_map_link' );
add_shortcode( 'wpgeo_mashup', 'shortcode_wpgeo_mashup' );



/**
 * @method       Shortcode [wpgeo_latitude]
 * @description  Outputs the post latitude.
 * @param        $atts = Shortcode attributes
 * @param        $content = Content between shortcode tags
 * @return       (float) Latitude
 */

function shortcode_wpgeo_latitude( $atts, $content = null ) {

	global $post;
	return get_wpgeo_latitude($post->ID);
	
}



/**
 * @method       Shortcode [wpgeo_longitude]
 * @description  Outputs the post longitude.
 * @param        $atts = Shortcode attributes
 * @param        $content = Content between shortcode tags
 * @return       (float) Longitude
 */

function shortcode_wpgeo_longitude( $atts, $content = null ) {

	global $post;
	return get_wpgeo_longitude($post->ID);
	
}



/**
 * @method       WP Geo Map Link
 * @description  Outputs a map link.
 * @param        $atts = Shortcode attributes
 * @param        $content = Content between shortcode tags
 * @return       (string) Map link
 */

function shortcode_wpgeo_map_link( $atts = null, $content = null ) {
	
	$defaults = array(
		'target' => '_self'
	);
	
	// Validate Args
	$r = wp_parse_args( $atts, $defaults );
	
	$atts['echo'] = 0;
	
	$url = wpgeo_map_link( $atts );
	return '<a href="' . $url . '" target="' . $r['target'] . '">' . do_shortcode( $content ) . '</a>';
	
}



/**
 * @method       Shortcode [wp_geo_map type="G_NORMAL_MAP"]
 * @description  Outputs the post map.
 * @param        $atts = Shortcode attributes
 * @param        $content = Content between shortcode tags
 * @return       HTML required to display map
 */

function shortcode_wpgeo_map( $atts, $content = null ) {

	global $post, $wpgeo;
	
	$id = $post->ID;
	$wp_geo_options = get_option( 'wp_geo_options' );
	
	$show_post_map = apply_filters( 'wpgeo_show_post_map', $wp_geo_options['show_post_map'], $id );
	
	if ( $wpgeo->show_maps() && !is_feed() && $show_post_map != 'TOP' && $show_post_map != 'BOTTOM' && $wpgeo->checkGoogleAPIKey() ) {
		
		$map_atts = array(
			'width' => $wp_geo_options['default_map_width'],
			'height' => $wp_geo_options['default_map_height'],
			'align' => 'none',
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
		
		// Alignment
		$float = in_array( strtolower( $atts['align'] ), array( 'left', 'right' ) ) ? 'float:' . strtolower( $atts['align'] ) . ';' : '';
		
		return '<div class="wp_geo_map" id="wp_geo_map_' . $id . '" style="' . $float . 'width:' . $map_width . '; height:' . $map_height . ';">' . $content . '</div>';
	
	} else {
	
		return '';
	
	}
	
}



/**
 * @method       Shortcode [wpgeo_mashup type="G_NORMAL_MAP"]
 * @description  Outputs a map mashup.
 * @param        $atts = Shortcode attributes
 * @param        $content = Content between shortcode tags
 * @return       HTML required to display map
 */

function shortcode_wpgeo_mashup( $atts, $content = null ) {

	// Original function by RavanH (updated by Ben)
	// See http://wordpress.org/extend/plugins/wp-geo-mashup-map/
	
	global $wpgeo;
	
	$wp_geo_options = get_option( 'wp_geo_options' );
	
	// Default attributes
	$map_atts = array(
		'width' => $wp_geo_options['default_map_width'],
		'height' => $wp_geo_options['default_map_height'],
		'align' => 'none',
		'type' => 'G_NORMAL_MAP',
		'numberposts' => -1
	);
	extract( shortcode_atts( $map_atts, $atts ) );
	
	if ( !is_feed() && isset( $wpgeo ) && $wpgeo->show_maps() && $wpgeo->checkGoogleAPIKey() )
		return get_wpgeo_map( $atts );
	else
		return '';
	
}



?>