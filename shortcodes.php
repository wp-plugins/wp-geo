<?php



/**
 * The WP Geo Shortcodes class
 */
class WPGeoShortcodes
{
	
	
	
	/**
	 * Shortcode: [wpgeo_map_all]
	 */
	function wpgeo_map_all($atts, $content = null)
	{
	
		return wpgeo_map_all("return");
		
	}
	
	

}



// Hooks
add_shortcode('wpgeo_map_all', array('WPGeoShortcodes', 'wpgeo_map_all'));



?>