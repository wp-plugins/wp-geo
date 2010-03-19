<?php



/**
 * ----- WP Geo Maps -----
 * This file contains all the classes that manage maps
 * and rendering maps to the page.
 *
 * @package     WP Geo
 * @subpackage  Maps
 * @author      Ben Huson <ben@thewhiteroom.net>
 */



/**
 * ----- WP Geo Maps Class -----
 * The WPGeo_Maps class manages all the maps present
 * on an HTML page and the output of those maps.
 */
class WPGeo_Maps {
	
	
	
	/**
	 * ----- Maps -----
	 * @var  (array) An array of WPGeo_Map objects.
	 */
	var $maps;
	
	
	
	/**
	 * ----- Constructor -----
	 * Sets up the Maps class.
	 */
	function WPGeo_Maps() {
		
		$this->maps = array();
		
	}
	
	
	
	/**
	 * ----- Add Map -----
	 * Adds a WPGeo_Map object to the maps array.
	 *
	 * @param  (object) $map WPGeo_Map object.
	 */
	function add_map( $map ) {
		
		if ( $map->id == 0 ) {
			$map->id = count($this->maps) + 1;
		}
		
		$this->maps[] = $map;
		
		return $map;
		
	}
	
	
	
	/**
	 * ----- Maps JavaScript -----
	 * Get the javascript to display all maps.
	 */
	function get_maps_javascript() {
		
		$javascript = '';
		
		foreach ( $this->maps as $map ) {
			$javascript .= $map->get_map_javascript();
		}
		
		return $javascript;
		
	}
	
	
	
}



/**
 * ----- WP Geo Map Class -----
 * The WPGeo_Map class manages data for a single map
 * and handles the output of that map.
 */
class WPGeo_Map {
	
	
	
	/**
	 * Properties
	 */
	
	var $id;
	var $points;
	var $zoom = 5;
	var $maptype = 'G_NORMAL_MAP';
	var $maptypes;
	var $mapcentre;
	var $mapcontrol = 'GLargeMapControl3D';
	var $show_map_scale = false;
	var $show_map_overview = false;
	var $show_polyline = false;
	
	
	
	/**
	 * @method       Constructor
	 * @description  Initialise the class.
	 */
	
	function WPGeo_Map( $id = 0 ) {
		
		$this->id = $id;
		$this->maptypes = array();
		$this->points = array();
		
	}
	
	
	
	/**
	 * @method       Render Map JavaScript
	 * @description  Outputs the javascript to display maps.
	 * @param        $map_id = The map ID.
	 * @return       (string) JavaScript
	 */
	
	function renderMapJS( $map_id = false ) {
	
		$wp_geo_options = get_option('wp_geo_options');
		
		// ID of div for map output
		$map_id = $map_id ? $map_id : $this->id;
		$div = 'wp_geo_map_' . $map_id;
		
		// Map Types
		$maptypes = $this->maptypes;
		$maptypes[] = $this->maptype;
		$maptypes = array_unique($maptypes);
		$js_maptypes = '';
		if ( in_array('G_PHYSICAL_MAP', $maptypes) )
			$js_maptypes .= 'map_' . $map_id . '.addMapType(G_PHYSICAL_MAP);';
		if ( !in_array('G_NORMAL_MAP', $maptypes) )
			$js_maptypes .= 'map_' . $map_id . '.removeMapType(G_NORMAL_MAP);';
		if ( !in_array('G_SATELLITE_MAP', $maptypes) )
			$js_maptypes .= 'map_' . $map_id . '.removeMapType(G_SATELLITE_MAP);';
		if ( !in_array('G_HYBRID_MAP', $maptypes) )
			$js_maptypes .= 'map_' . $map_id . '.removeMapType(G_HYBRID_MAP);';
		
		// Markers
		$js_markers = '';
		if ( count($this->points) > 0 ) {
			for ( $i = 0; $i < count($this->points); $i++ ) {
				$js_markers .= 'var marker_' . $map_id .'_' . $i . ' = new wpgeo_createMarker2(map_' . $map_id . ', new GLatLng(' . $this->points[$i]['latitude'] . ', ' . $this->points[$i]['longitude'] . '), ' . $this->points[$i]['icon'] . ', \'' . addslashes(__($this->points[$i]['title'])) . '\', \'' . $this->points[$i]['link'] . '\');' . "\n";
				$js_markers .= 'bounds.extend(new GLatLng(' . $this->points[$i]['latitude'] . ', ' . $this->points[$i]['longitude'] . '));';
			}
		}
		
		// Show Polyline
		$js_polyline = '';
		if ( $wp_geo_options['show_polylines'] == 'Y' ) {
			if ( $this->show_polyline ) {
				if ( count($this->points) > 1 ) {
					$polyline_coords = '';
					for ( $i = 0; $i < count($this->points); $i++ ) {
						if ( $i > 0 ) {
							$polyline_coords .= ',';
						}
						$polyline_coords .= 'new GLatLng(' . $this->points[$i]['latitude'] . ', ' . $this->points[$i]['longitude'] . ')' . "\n";
					}
					$js_polyline = 'var polyOptions = {geodesic:true};' . "\n";
					$js_polyline .= 'var polyline = new GPolyline([' . $polyline_coords . '], "' . $wp_geo_options['polyline_colour'] . '", 2, 0.5, polyOptions);' . "\n";
					$js_polyline .= 'map_' . $map_id . '.addOverlay(polyline);' . "\n";
				}
			}
		}
		
		// Zoom
		$js_zoom = '';
		if ( count($this->points) > 1 ) {
			$js_zoom .= 'map_' . $map_id . '.setCenter(bounds.getCenter(), map_' . $map_id . '.getBoundsZoomLevel(bounds));';
		}
		if ( count($this->points) == 1 ) {
			$js_zoom .= 'map_' . $map_id . '.setCenter(new GLatLng(' . $this->mapcentre['latitude'] . ', ' . $this->mapcentre['longitude'] . '));';
		}
		
		// Controls
		$js_controls = '';
		if ( $this->show_map_scale )
			$js_controls .= 'map_' . $map_id . '.addControl(new GScaleControl());';
		if ( $this->show_map_overview )
			$js_controls .= 'map_' . $map_id . '.addControl(new GOverviewMapControl());';
		
		// Map Javascript
		$js = '
			if (document.getElementById("' . $div . '"))
			{
				var bounds = new GLatLngBounds();
    
				map_' . $map_id . ' = new GMap2(document.getElementById("' . $div . '"));
				var center = new GLatLng(' . $this->points[0]['latitude'] . ', ' . $this->points[0]['longitude'] . ');
				map_' . $map_id . '.setCenter(center, ' . $this->zoom . ');
				
				' . $js_maptypes . '
				map_' . $map_id . '.setMapType(' . $this->maptype . ');
				
				var mapTypeControl = new GMapTypeControl();
				map_' . $map_id . '.addControl(mapTypeControl);';
		if ( $this->mapcontrol != "" ) {
			$js .= 'map_' . $map_id . '.addControl(new ' . $this->mapcontrol . '());';
		}
		$js .= '
				var center_' . $map_id .' = new GLatLng(' . $this->points[0]['latitude'] . ', ' . $this->points[0]['longitude'] . ');
				
				' . apply_filters( 'wpgeo_map_js_preoverlays', '', 'map_' . $map_id ) . '
				
				' . $js_markers . '
				' . $js_polyline . '
    			' . $js_zoom . '
    			' . $js_controls . '
				
				//map_' . $map_id . '.addOverlay(new GLayer("org.wikipedia.en"));
				//map_' . $map_id . '.addOverlay(new GLayer("com.panoramio.all"));
				//map_' . $map_id . '.addControl(new google.maps.LocalSearch()); // http://googleajaxsearchapi.blogspot.com/2007/06/local-search-control-for-maps-api.html
				
			}';
		
		return $js;
		
	}
	
	
	
	/**
	 * @method       Get Map HTML
	 * @description  Gets the HTML for a map.
	 */
	
	function get_map_html() {
		
		$wp_geo_options = get_option('wp_geo_options');
		
		// Extract args
		$allowed_args = array(
			'width' => null,
			'height' => null
		);
		$args = wp_parse_args($args, $allowed_args);
		
		$map_width = $wp_geo_options['default_map_width'];
		$map_height = $wp_geo_options['default_map_height'];
		
		if ( $args['width'] != null) {
			$map_width = $args['width'];
			if ( is_numeric($map_width) ) {
				$map_width = $map_width . 'px';
			}
		}
		if ( $args['height'] != null) {
			$map_height = $args['height'];
			if ( is_numeric($map_height) ) {
				$map_height = $map_height . 'px';
			}
		}
		
		return '<div class="wpgeo_map" id="wpgeo_map_' . $this->id . '" style="width:' . $map_width . '; height:' . $map_height . ';"></div>';
		
	}
	
	
	
	/**
	 * @method       Get Map JavaScript
	 * @description  Gets the Javascript for a map.
	 */
	
	function get_map_javascript() {
		
		$js = '
			wpgeo_map_' . $this->id . ' = new GMap2(document.getElementById("wpgeo_map_' . $this->id . '"));
			';
		
		return $js;
		
	}
	
	
	
	/**
	 * @method       Add Point
	 * @description  Adds a point (marker) to this map.
	 * @param        $lat = Latitude
	 * @param        $long = Longitude
	 * @param        $icon = Icon type
	 * @param        $title = Display title
	 * @param        $link = URL to link to when point is clicked
	 */
	
	function addPoint( $lat, $long, $icon = 'wpgeo_icon_large', $title = '', $link = '' ) {
	
		// Save point data
		$this->points[] = array(
			'latitude'  => $lat, 
			'longitude' => $long,
			'icon' => $icon,
			'title' => $title,
			'link' => $link,
		);
	
	}
	
	
	
	/**
	 * @method       Show Polyline
	 * @description  Show polylines on this map?
	 * @param        $bool = Boolean
	 */
	
	function showPolyline( $bool = true ) {
	
		$this->show_polyline = $bool;
		
	}
	
	
	
	/**
	 * @method       Set Map Control
	 * @description  Set the type of map control that should be used for this map.
	 * @param        $mapcontrol = Type of map control
	 */
	
	function setMapControl( $mapcontrol = 'GLargeMapControl3D' ) {
	
		$this->mapcontrol = $mapcontrol;
		
	}
	
	
	
	/**
	 * @method       Set Map Type
	 * @description  Set the type of map.
	 * @param        $maptype = Type of map
	 */
	
	function setMapType( $maptype = 'G_NORMAL_MAP' ) {
	
		if ( $this->is_valid_map_type($maptype) ) {
			$this->maptype = $maptype;
		}
		
	}
	
	
	
	/**
	 * @method       Set Map Centre
	 * @description  Set the centre point of the map.
	 * @param        $latitude = Latitude
	 * @param        $longitude = Longitude
	 */
	
	function setMapCentre( $latitude, $longitude ) {
		
		$this->mapcentre = array(
			'latitude' => $latitude,
			'longitude' => $longitude
		);
		
	}
	
	
	
	/**
	 * @method       Add Map Type
	 * @description  Adds a type of map.
	 * @param        $maptype = Type of map
	 */
	
	function addMapType( $maptype ) {
	
		if ( $this->is_valid_map_type($maptype) ) {
			$this->maptypes[] = $maptype;
			$this->maptypes = array_unique($this->maptypes);
		}
		
	}
	
	
	
	/**
	 * @method       Is Valid Map Type
	 * @description  Check to see if a map type is allowed.
	 * @param        $maptype = Type of map
	 */
	
	function is_valid_map_type( $maptype ) {
	
		$types = array(
			'G_PHYSICAL_MAP',
			'G_NORMAL_MAP',
			'G_SATELLITE_MAP',
			'G_HYBRID_MAP'
		);
		
		return in_array($maptype, $types);
		
	}
	
	
	
	/**
	 * @method       Set Map Zoom
	 * @description  Sets the default zoom of this map.
	 * @param        $zoom = Zoom
	 */
	
	function setMapZoom( $zoom = 5 ) {
	
		$this->zoom = absint($zoom);
		
	}
	
	
	
	/**
	 * @method       Show Map Scale
	 * @description  Show the scale at the bottom of the map?
	 * @param        $bool = Boolean
	 */
	
	function showMapScale( $bool = true ) {
	
		$this->show_map_scale = $bool;
		
	}
	
	
	
	/**
	 * @method       Show Map Overview
	 * @description  Show the mini overview map?
	 * @param        $bool = Boolean
	 */
	
	function showMapOverview( $bool = true ) {
	
		$this->show_map_overview = $bool;
		
	}
	
	

}



?>