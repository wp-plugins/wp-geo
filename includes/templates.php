<?php

/**
 * WP Geo Latitude
 * Outputs the post latitude.
 *
 * @param int $post_id (optional) Post ID.
 */
function wpgeo_latitude( $post_id = null ) {
	echo get_wpgeo_latitude( $post_id );
}

/**
 * WP Geo Longitude
 * Outputs the post longitude.
 *
 * @param int $post_id (optional) Post ID.
 */
function wpgeo_longitude( $post_id = null ) {
	echo get_wpgeo_longitude( $post_id );
}

/**
 * WP Geo Title
 * Outputs the post title.
 *
 * @param int $post_id (optional) Post ID
 * @param bool $default_to_post_title (optional) Default to post title if point title empty.
 */
function wpgeo_title( $post_id = null, $default_to_post_title = true ) {
	echo get_wpgeo_title( $post_id, $default_to_post_title );
}

/**
 * Get WP Geo Latitude
 * Gets the post latitude.
 *
 * @param int $post_id (optional) Post ID.
 * @return float Latitude.
 */
function get_wpgeo_latitude( $post_id = 0 ) {
	global $post;
	
	$post_id = absint( $post_id );
	$post_id = $post_id > 0 ? $post_id : $post->ID;
	if ( $post_id > 0 ) {
		return get_post_meta( $post_id, WPGEO_LATITUDE_META, true );
	}
	return null;
}

/**
 * Get WP Geo Longitude
 * Gets the post longitude.
 *
 * @param int $post_id (optional) Post ID.
 * @return float Longitude.
 */
function get_wpgeo_longitude( $post_id = 0 ) {
	global $post;
	
	$post_id = absint( $post_id );
	$post_id = $post_id > 0 ? $post_id : $post->ID;
	if ( $post_id > 0 ) {
		return get_post_meta( $post_id, WPGEO_LONGITUDE_META, true );
	}
	return null;
}

/**
 * Get WP Geo Title
 *
 * @param int $post_id (optional) Post ID.
 * @param bool $default_to_post_title (optional) Default to post title if point title empty.
 * @return string Title.
 */
function get_wpgeo_title( $post_id = 0, $default_to_post_title = true ) {
	global $post;
	
	if ( 'object' == gettype( $post_id ) && 'WP_Post' == get_class( $post_id ) ) {
		$post_id = $post_id->ID;
	} else {
		$post_id = absint( $post_id );
		$post_id = $post_id > 0 ? $post_id : $post->ID;
	}
	
	if ( $post_id > 0 ) {
		$title = get_post_meta( $post_id, WPGEO_TITLE_META, true );
		if ( empty( $title ) && $default_to_post_title ) {
			$p = &get_post( $post_id );
			$title = isset( $p->post_title ) ? $p->post_title : '';
		}
		$title = apply_filters( 'wpgeo_point_title', $title, $post_id );
		return $title;
	}
	return '';
}

/**
 * WP Geo Map Link
 * Gets a link to an external map.
 *
 * @todo This should probably use API but fallback to Google Maps.
 *
 * @param array $args (optional) Array of arguments.
 * @return string Map URL.
 */
function wpgeo_map_link( $args = null ) {
	global $post;
	
	// Validate Args
	$r = wp_parse_args( $args, array(
		'post_id'   => $post->ID,
		'latitude'  => null,
		'longitude' => null,
		'zoom'      => 5,
		'echo'      => 1
	) );
	$r['post_id'] = absint( $r['post_id'] );
	$r['zoom']    = absint( $r['zoom'] );
	$r['echo']    = absint( $r['echo'] );
	
	$coord = new WPGeo_Coord( $r['latitude'], $r['longitude'] );
	
	// If a post is specified override lat/lng...
	if ( ! $coord->is_valid_coord() ) {
		$coord = new WPGeo_Coord( get_wpgeo_latitude( $r['post_id'] ), get_wpgeo_longitude( $r['post_id'] ) );
	}
	
	// If lat/lng...
	$url = '';
	if ( $coord->is_valid_coord() ) {
		$url = 'http://maps.google.co.uk/maps';
		$url = add_query_arg( 'q', $coord->get_delimited(), $url );
		if ( $r['zoom'] )
			$url = add_query_arg( 'z', $r['zoom'], $url );
		$url = apply_filters( 'wpgeo_map_link', $url, $r );
	}
	
	// Output
	if ( $r['echo'] == 0 )
		return $url;
	echo $url;
}

/**
 * WP Geo Post Map
 * Outputs the HTML for a post map.
 *
 * @param int $post_id (optional) Post ID.
 */
function wpgeo_post_map( $post_id = null ) {
	echo get_wpgeo_post_map( $post_id );
}

/**
 * Get WP Geo Post Map
 * Gets the HTML for a post map.
 *
 * @param int $post_id (optional) Post ID.
 * @return string HTML.
 */
function get_wpgeo_post_map( $post_id = 0, $args = null ) {
	global $post, $wpgeo;
	
	$post_id = absint( $post_id );
	$post_id = $post_id > 0 ? $post_id : $post->ID;
	$this_post = get_post( $post_id );
	$wp_geo_options = get_option( 'wp_geo_options' );
	
	$args = wp_parse_args( $args, array(
		'width'          => '',
		'height'         => '',
		'maptype'        => empty( $wp_geo_options['google_map_type'] ) ? 'G_NORMAL_MAP' : $wp_geo_options['google_map_type'],
		'show_polylines' => false,
		'zoom'           => $wp_geo_options['default_map_zoom'],
		'id'             => $post_id,
		'posts'          => array( $this_post ),
		'styles'         => '',
		'content'        => ''
	) );
	
	$show_post_map = apply_filters( 'wpgeo_show_post_map', $wp_geo_options['show_post_map'], $post_id );
	
	$coord = new WPGeo_Coord( get_post_meta( $post_id, WPGEO_LATITUDE_META, true ), get_post_meta( $post_id, WPGEO_LONGITUDE_META, true ) );
	if ( ! $coord->is_valid_coord() )
		return '';
	
	if ( $post_id > 0 && ! is_feed() ) {
		if ( $wpgeo->show_maps() && $show_post_map != 'TOP' && $show_post_map != 'BOTTOM' && $wpgeo->checkGoogleAPIKey() ) {
			$meta = get_post_meta( $post_id, WPGEO_MAP_SETTINGS_META, true );
			$marker_meta = get_post_meta( $post_id, WPGEO_MARKER_META, true );
			$marker_meta = empty( $marker_meta ) ? 'large' : $marker_meta;
			$title_meta = get_post_meta( $post_id, WPGEO_TITLE_META, true );
			$title_meta = empty( $title_meta ) ? get_the_title( $post_id ) : $title_meta;
			if ( is_numeric( $meta['zoom'] ) )
				$args['zoom'] = $meta['zoom'];
			if ( ! empty( $meta['type'] ) )
				$args['maptype'] = $meta['type'];
			$center_coord = $coord;
			if ( ! empty( $meta['centre'] ) ) {
				$center = explode( ',', $meta['centre'] );
				$center_coord = new WPGeo_Coord( $center[0], $center[1] );
			}
			
			$map = new WPGeo_Map( $post_id );
			if ( $center_coord->is_valid_coord() )
				$map->set_map_centre( $center_coord );
			$map->set_map_zoom( $args['zoom'] );
			$map->set_map_type( $args['maptype'] );
			$map->add_point( $coord, array(
				'icon'  => apply_filters( 'wpgeo_marker_icon', $marker_meta, $this_post, 'post' ),
				'title' => $title_meta,
				'link'  => get_permalink( $this_post ),
				'post'  => $this_post
			) );
			if ( ! empty( $args['width'] ) )
				$map->set_width( $args['width'] );
			if ( ! empty( $args['height'] ) )
				$map->set_height( $args['height'] );
			
			$map = $wpgeo->maps2->add_map( $map );
			return $map->get_map_html( $args );
		}
	}
	return '';
}

/**
 * Create Input Map
 */
function wpgeo_create_input_map( $options = null ) {
	global $wpgeo, $wpgeo_map_id;
	
	$wpgeo_map_id++;
	$id = 'wpgeo_map_id_' . $wpgeo_map_id;
	$wp_geo_options = get_option('wp_geo_options');
	
	$query = array();
	$defaults = array(
		'latitude'        => $wp_geo_options['default_map_latitude'],
		'longitude'       => $wp_geo_options['default_map_longitude'],
		'width'           => $wp_geo_options['default_map_width'],
		'height'          => $wp_geo_options['default_map_height'],
		'type'            => $wp_geo_options['google_map_type'],
		'align'           => 'none',
        'markers'         => 'large'
	);
	
	// Validate Args
	$r = wp_parse_args( $query, $defaults );
	$r['width']  = wpgeo_css_dimension( $r['width'] );
	$r['height'] = wpgeo_css_dimension( $r['height'] );
	
	// Point
	$point = new WPGeo_Coord( $defaults['latitude'], $defaults['longitude'] );
	if ( ! $point->is_valid_coord() ) {
		$point = new WPGeo_Coord( $defaults['default_map_latitude'], $defaults['default_map_longitude'] );
	}
	
	// Map
	$map = new WPGeo_Map( 'id_' . $wpgeo_map_id );
	$map->set_size( $r['width'], $r['height'] );
	$map->set_map_centre( $point );
	$map->set_map_zoom( $wp_geo_options['default_map_zoom'] );
	$map->set_map_type( $r['type'] );
	
	// Points
	$map->add_point( $point, array(
		'icon'  => apply_filters( 'wpgeo_marker_icon', $r['markers'], 0, 'input' )
	) );
	
	$wpgeo->maps2->add_map( $map );
	return $map;
}

/**
 * Get WP Geo Map
 *
 * @param array $query Query args.
 * @param array $options Options array.
 * @return string Output.
 */
function get_wpgeo_map( $query, $options = null ) {
	global $wpgeo, $wpgeo_map_id;
	
	$wpgeo_map_id++;
	$id = 'wpgeo_map_id_' . $wpgeo_map_id;
	$wp_geo_options = get_option('wp_geo_options');
	
	$defaults = array(
		'width'           => $wp_geo_options['default_map_width'],
		'height'          => $wp_geo_options['default_map_height'],
		'type'            => $wp_geo_options['google_map_type'],
		'polylines'       => $wp_geo_options['show_polylines'],
		'polyline_colour' => $wp_geo_options['polyline_colour'],
		'align'           => 'none',
		'numberposts'     => -1,
		'posts_per_page'  => -1,
		'post_type'       => 'post',
		'post_status'     => 'publish',
		'orderby'         => 'post_date',
		'order'           => 'DESC',
		'markers'         => 'large',
        'markers'         => 'large',
        'offset'          => 0,
        'category'        => null,
        'include'         => null,
        'exclude'         => null,
        'meta_key'        => null,
        'meta_value'      => null,
        'post_mime_type'  => null,
        'post_parent'     => null
	);
	
	// Validate Args
	$r = wp_parse_args( $query, $defaults );
	$r['width']  = wpgeo_css_dimension( $r['width'] );
	$r['height'] = wpgeo_css_dimension( $r['height'] );
	
	if ( $r['posts_per_page'] < $r['numberposts'] ) {
		$r['posts_per_page'] = $r['numberposts'];
	}
	
	$posts = get_posts( $r );
	
	// Map
	$map = new WPGeo_Map( 'id_' . $wpgeo_map_id );
	$map->set_size( $r['width'], $r['height'] );
	$map->set_map_centre( new WPGeo_Coord( $wp_geo_options['default_map_latitude'], $wp_geo_options['default_map_longitude'] ) );
	$map->set_map_zoom( $wp_geo_options['default_map_zoom'] );
	$map->set_map_type( $r['type'] );
	
	// Points
	if ( $posts ) {
		foreach ( $posts as $post ) {
			$coord = new WPGeo_Coord( get_post_meta( $post->ID, WPGEO_LATITUDE_META, true ), get_post_meta( $post->ID, WPGEO_LONGITUDE_META, true ) );
			if ( $coord->is_valid_coord() ) {
				$marker = get_post_meta( $post->ID, WPGEO_MARKER_META, true );
				if ( empty( $marker ) )
					$marker = $r['markers'];
				$map->add_point( $coord, array(
					'icon'  => apply_filters( 'wpgeo_marker_icon', $marker, $post, 'template' ),
					'title' => get_wpgeo_title( $post->ID ),
					'link'  => get_permalink( $post ),
					'post'  => $post
				) );
			}
		}
	}
	
	// Polylines
	if ( count( $map->points ) > 0 ) {
		if ( $r['polylines'] == 'Y' ) {
			$polyline = new WPGeo_Polyline( array(
				'color' => $r['polyline_colour']
			) );
			foreach ( $map->points as $point ) {
				$polyline->add_coord( $point->coord );
			}
			$map->add_polyline( $polyline );
		}
	}
	
	$center_coord = $map->get_map_centre();
	
	$wpgeo->maps2->add_map( $map );
	return $map->get_map_html( array( 'styles' => array( 'float' => $r['align'] ) ) );
}

/**
 * WP Geo Map
 *
 * @param array $query Query args.
 * @param array $options Options array.
 * @return string Output.
 */
function wpgeo_map( $query, $options = null ) {
	echo get_wpgeo_map( $query, $options );
}

/**
 * WP Geo Post Static Map
 * Outputs the HTML for a static post map.
 *
 * @param int $post_id (optional) Post ID.
 * @param array $query (optional) Parameters.
 */
function wpgeo_post_static_map( $post_id = 0, $query = null ) {
	echo get_wpgeo_post_static_map( $post_id, $query );
}

/**
 * Get WP Geo Post Static Map
 * Gets the HTML for a static post map.
 *
 * @todo Should be implemented via API but fallback to Google Static Maps.
 *
 * @param int $post_id (optional) Post ID.
 * @param array $query (optional) Parameters.
 * @return string HTML.
 */
function get_wpgeo_post_static_map( $post_id = 0, $query = null ) {
	global $post, $wpgeo;
	
	$post_id = absint( $post_id );
	$post_id = $post_id > 0 ? $post_id : $post->ID;

	if ( ! $post_id || is_feed() || ! $wpgeo->show_maps() || ! $wpgeo->checkGoogleAPIKey() )
		return '';
	
	$coord = new WPGeo_Coord( get_post_meta( $post_id, WPGEO_LATITUDE_META, true ), get_post_meta( $post_id, WPGEO_LONGITUDE_META, true ) );
	if ( ! $coord->is_valid_coord() )
		return '';
	
	// Fetch wp geo options & post settings
	$wp_geo_options = get_option( 'wp_geo_options' );
	$settings  = get_post_meta( $post_id, WPGEO_MAP_SETTINGS_META, true );
	
	// Options
	$defaults = array(
		'width'   => trim( $wp_geo_options['default_map_width'], 'px' ),
		'height'  => trim( $wp_geo_options['default_map_height'], 'px' ),
		'maptype' => $wp_geo_options['google_map_type'],
		'zoom'    => $wp_geo_options['default_map_zoom'],
	);
	$options = wp_parse_args( $query, $defaults );
	
	// Can't do percentage sizes to abort
	if ( strpos( $options['width'], '%' ) !== false || strpos( $options['height'], '%' ) !== false )
		return '';

	// Convert WP Geo map types to static map type url param
	$types = array(
		'G_NORMAL_MAP'    => 'roadmap',
		'G_SATELLITE_MAP' => 'satellite',
		'G_PHYSICAL_MAP'  => 'terrain',
		'G_HYBRID_MAP'    => 'hybrid'
	);	

	// Center on location marker by default
	$center_coord = new WPGeo_Coord( $coord->latitude(), $coord->longitude() );

	// Custom map settings?
	if ( isset( $settings['zoom'] ) && is_numeric( $settings['zoom'] ) ) {
		$options['zoom'] = $settings['zoom'];
	}
	if ( ! empty( $settings['type'] ) ) {
		$options['maptype'] = $settings['type'];
	}
	if ( ! empty( $settings['centre'] ) ) {
		$center = explode( ',', $settings['centre'] );
		$maybe_center_coord = new WPGeo_Coord( $center[0], $center[1] );
		if ( $maybe_center_coord->is_valid_coord() ) {
			$center_coord = $maybe_center_coord;
		}
	}
	
	$url = add_query_arg( array(
		'center'  => $center_coord->get_delimited(),
		'zoom'    => $options['zoom'],
		'size'    => $options['width'] . 'x' . $options['height'],
		'maptype' => $types[$options['maptype']],
		'markers' => 'color:red%7C' . $coord->get_delimited(),
		'sensor'  => 'false'
	), 'http://maps.googleapis.com/maps/api/staticmap' );
	
	return '<img id="wp_geo_static_map_' . $post_id . '" src="' . $url . '" class="wp_geo_static_map" />';
}
