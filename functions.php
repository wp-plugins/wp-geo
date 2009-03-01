<?php



/**
 * WP Geo: Map All
 */
// Displays a map of all posts
function wpgeo_map_all($ret = "echo")
{

	global $customFields;

	// Query posts
	$customFields = "'_wp_geo_latitude', '_wp_geo_longitude'";
	$customPosts = new WP_Query();
	add_filter('posts_join', array('WPGeoFilters', 'get_custom_field_posts_join'));
	add_filter('posts_groupby', array('WPGeoFilters', 'get_custom_field_posts_group'));
	$customPosts->query('showposts=100');
	remove_filter('posts_join', array('WPGeoFilters', 'get_custom_field_posts_join'));
	remove_filter('posts_groupby', array('WPGeoFilters', 'get_custom_field_posts_group'));
	
	// Loop through posts
	$i = 0;
	while ($customPosts->have_posts()) : $customPosts->the_post();
	$lat = get_post_custom_values("_wp_geo_latitude");
	$long =  get_post_custom_values("_wp_geo_longitude");
	
	$markers_js .= '
		var map_all_0 = new wpgeo_createMarker2(map_all, new GLatLng(' . $lat[0] . ', ' . $long[0] . '), wpgeo_icon_small, "' . the_title('', '', false) . '", "' . get_permalink() . '");
		bounds.extend(new GLatLng(' . $lat[0] . ', ' . $long[0] . '));
		';
	$i++;
	endwhile;
	
	$output = '';
	
	// Output map div
	$output .= '<div id="wp_geo_map_all" style="width:100%; height:300px;"></div>';
	
	// Output JS
	$output .= '
		<script type="text/javascript">
		function mapsLoaded()
		{
		
			var bounds = new google.maps.LatLngBounds();
			
			map_all = new google.maps.Map2(document.getElementById("wp_geo_map_all"));
			var center = new google.maps.LatLng(51.49228593987783, -0.15234947204589844);
			map_all.setCenter(center, 16);
			
			map_all.addMapType(G_PHYSICAL_MAP);
			map_all.setMapType(G_PHYSICAL_MAP);
				
			var mapTypeControl = new google.maps.MapTypeControl();
			map_all.addControl(mapTypeControl);
			var center_visible = new google.maps.LatLng(51.49228593987783, -0.15234947204589844);
			
			' . $markers_js . '
			
			map_all.setCenter(bounds.getCenter(), map_all.getBoundsZoomLevel(bounds));
    		map_all.addControl(new google.maps.ScaleControl());map_all.addControl(new google.maps.OverviewMapControl());
		
		}
		google.load("maps", "2", {"callback" : mapsLoaded});
		</script>';
	
	if ($ret == "return")
	{
		return $output;
	}
	else
	{
		echo $output;
	}
	
}



?>