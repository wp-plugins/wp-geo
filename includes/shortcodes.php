<?php

/**
 * Shortcode [wpgeo_latitude]
 * Outputs the post latitude.
 *
 * @param array $atts Shortcode attributes.
 * @param string $content Content between shortcode tags.
 * @return float Latitude.
 */
if ( ! function_exists( 'shortcode_wpgeo_latitude' ) ) {
	function shortcode_wpgeo_latitude( $atts, $content = null ) {
		global $post;
		return get_wpgeo_latitude( $post->ID );
	}
	add_shortcode( 'wpgeo_latitude', 'shortcode_wpgeo_latitude' );
}

/**
 * Shortcode [wpgeo_longitude]
 * Outputs the post longitude.
 *
 * @param array $atts Shortcode attributes.
 * @param string $content Content between shortcode tags.
 * @return float Longitude.
 */
if ( ! function_exists( 'shortcode_wpgeo_longitude' ) ) {
	function shortcode_wpgeo_longitude( $atts, $content = null ) {
		global $post;
		return get_wpgeo_longitude( $post->ID );
	}
	add_shortcode( 'wpgeo_longitude', 'shortcode_wpgeo_longitude' );
}

/**
 * Shortcode [wpgeo_title]
 * Outputs the marker title.
 *
 * @param array $atts Shortcode attributes.
 * @param string $content Content between shortcode tags.
 * @return string Title
 */
if ( ! function_exists( 'shortcode_wpgeo_title' ) ) {
	function shortcode_wpgeo_title( $atts, $content = null ) {
		global $post;
		
		// Validate Args
		$atts = wp_parse_args( $atts, array(
			'default_to_post_title' => true
		) );
		return get_wpgeo_title( $post->ID, $atts['default_to_post_title'] );
	}
	add_shortcode( 'wpgeo_title', 'shortcode_wpgeo_title' );
}

/**
 * Shortcode [wpgeo_map_link target="_self"]
 * Outputs a map link.
 *
 * @param array $atts Shortcode attributes.
 * @param string $content Content between shortcode tags.
 * @return string Map link
 */
if ( ! function_exists( 'shortcode_wpgeo_map_link' ) ) {
	function shortcode_wpgeo_map_link( $atts = null, $content = null ) {
		
		// Validate Args
		$atts = wp_parse_args( $atts, array(
			'target' => '_self'
		) );
		$atts['echo'] = 0;
		
		if ( ! $content )
			$content = __( 'View Larger Map', 'wp-geo' );
		
		return '<a href="' . wpgeo_map_link( $atts ) . '" target="' . $atts['target'] . '">' . do_shortcode( $content ) . '</a>';
	}
	add_shortcode( 'wpgeo_map_link', 'shortcode_wpgeo_map_link' );
}

/**
 * Shortcode [wpgeo_static_map post_id="" width="" height="" maptype="" zoom=""]
 * Outputs a map link.
 *
 * @param array $atts Shortcode attributes.
 * @param string $content Content between shortcode tags.
 * @return string Map link
 */
if ( ! function_exists( 'shortcode_wpgeo_static_map' ) ) {
	function shortcode_wpgeo_static_map( $atts = null, $content = null ) {
		global $post;
		
		$wp_geo_options = get_option( 'wp_geo_options' );
		
		// Validate Args
		$atts = wp_parse_args( $atts, array(
			'post_id' => $post->ID,
			'width'   => trim( $wp_geo_options['default_map_width'], 'px' ),
			'height'  => trim( $wp_geo_options['default_map_height'], 'px' ),
			'maptype' => $wp_geo_options['google_map_type'],
			'zoom'    => $wp_geo_options['default_map_zoom']
		) );
		
		return get_wpgeo_post_static_map( $atts['post_id'], $atts );
	}
	add_shortcode( 'wpgeo_static_map', 'shortcode_wpgeo_static_map' );
}

/**
 * Shortcode [wpgeo_map width="" height="" align="" lat="" long="" type="G_NORMAL_MAP" escape=""]
 * Outputs the post map.
 *
 * @param array $atts Shortcode attributes.
 * @param string $content Content between shortcode tags.
 * @return string HTML required to display map.
 */
if ( ! function_exists( 'shortcode_wpgeo_map' ) ) {
	function shortcode_wpgeo_map( $atts, $content = null ) {
		global $post, $wpgeo;
		
		$wp_geo_options = get_option( 'wp_geo_options' );
		$show_post_map = apply_filters( 'wpgeo_show_post_map', $wp_geo_options['show_post_map'], $post->ID );
		
		if ( $wpgeo->show_maps() && ! is_feed() && $show_post_map != 'TOP' && $show_post_map != 'BOTTOM' && $wpgeo->checkGoogleAPIKey() ) {
			$atts = wp_parse_args( $atts, array(
				'width'  => $wp_geo_options['default_map_width'],
				'height' => $wp_geo_options['default_map_height'],
				'align'  => 'none',
				'lat'    => null,
				'long'   => null,
				'type'   => 'G_NORMAL_MAP',
				'escape' => false
			) );
			
			// Escape?
			if ( $atts['escape'] == 'true' )
				return '[wpgeo_map]';
		
			// To Do: Add in lon/lat check and output map if needed
			
			$map_width  = wpgeo_css_dimension( $atts['width'] );
			$map_height = wpgeo_css_dimension( $atts['height'] );
			$float = in_array( strtolower( $atts['align'] ), array( 'left', 'right' ) ) ? 'float:' . strtolower( $atts['align'] ) . ';' : '';
			
			return '<div class="wp_geo_map" id="wp_geo_map_' . $post->ID . '" style="' . $float . 'width:' . $map_width . '; height:' . $map_height . ';">' . $content . '</div>';
		}
		return '';
	}
	add_shortcode( 'wpgeo_map', 'shortcode_wpgeo_map' );
	// Deprecate this shortcode - standardised to the above.
	add_shortcode( 'wp_geo_map', 'shortcode_wpgeo_map' );
}

/**
 * Shortcode [wpgeo_mashup type="G_NORMAL_MAP"]
 * Outputs a map mashup.
 * Original function by RavanH (updated by Ben Huson)
 * See http://wordpress.org/extend/plugins/wp-geo-mashup-map/
 *
 * @param array $atts Shortcode attributes.
 * @param string $content Content between shortcode tags.
 * @return string HTML required to display map.
 */
if ( ! function_exists( 'shortcode_wpgeo_mashup' ) ) {
	function shortcode_wpgeo_mashup( $atts, $content = null ) {
		global $wpgeo;
		
		$wp_geo_options = get_option( 'wp_geo_options' );
		
		$atts = wp_parse_args( $atts, array(
			'width'           => $wp_geo_options['default_map_width'],
			'height'          => $wp_geo_options['default_map_height'],
			'type'            => $wp_geo_options['google_map_type'],
			'polylines'       => $wp_geo_options['show_polylines'],
			'polyline_colour' => $wp_geo_options['polyline_colour'],
			'align'           => 'none',
			'numberposts'     => -1,
			'posts_per_page'  => -1,
			'post_type'       => null,
			'post_status'     => 'publish',
			'orderby'         => 'post_date',
			'order'           => 'DESC',
			'markers'         => 'large'
		) );
		
		if ( ! is_feed() && isset( $wpgeo ) && $wpgeo->show_maps() && $wpgeo->checkGoogleAPIKey() )
			return get_wpgeo_map( $atts );
		return '';
	}
	add_shortcode( 'wpgeo_mashup', 'shortcode_wpgeo_mashup' );
}

?>