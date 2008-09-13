<?php



/**
 * The WP Geo Markers class
 */
class WPGeoMarkers
{
	
	
	
	/**
	 * Properties
	 */
	 
	var $version = '1.0';
	
	
	
	/**
	 * Constructor
	 */
	function WPGeoMarkers()
	{
	}
	
	

	/**
	 * Register Activation
	 */
	function register_activation()
	{
		
		// New Marker Folders
		clearstatcache();
		$old_umask = umask(0);
		mkdir(ABSPATH . '/wp-content/uploads/wp-geo');
		mkdir(ABSPATH . '/wp-content/uploads/wp-geo/markers');
		
		// Marker Folders
		$old_marker_image_dir = ABSPATH . '/wp-content/plugins/wp-geo/img/markers/';
		$new_marker_image_dir = ABSPATH . '/wp-content/uploads/wp-geo/markers/';
		
		// Marker Files
		$this->moveFileOrDelete($old_marker_image_dir . 'dot-marker.png', $new_marker_image_dir . 'dot-marker.png');
		$this->moveFileOrDelete($old_marker_image_dir . 'dot-marker-shadow.png', $new_marker_image_dir . 'dot-marker-shadow.png');
		$this->moveFileOrDelete($old_marker_image_dir . 'large-marker.png', $new_marker_image_dir . 'large-marker.png');
		$this->moveFileOrDelete($old_marker_image_dir . 'large-marker-shadow.png', $new_marker_image_dir . 'large-marker-shadow.png');
		$this->moveFileOrDelete($old_marker_image_dir . 'small-marker.png', $new_marker_image_dir . 'small-marker.png');
		$this->moveFileOrDelete($old_marker_image_dir . 'small-marker-shadow.png', $new_marker_image_dir . 'small-marker-shadow.png');
		
		// Reset default permissions
		umask($old_umask);
		
	}
	
	
	
	/**
	 * Move File or Delete (if already exists)
	 */
	function moveFileOrDelete($old_file, $new_file)
	{
		
		if (!file_exists($new_file))
		{
			$ok = copy($old_file, $new_file);
			if ($ok)
			{
				// Moved OK...
			}
		}
		
	}
	
	
	
	/**
	 * wp_head
	 */
	function wp_head()
	{
	
		$marker_dot = $this->get_marker_meta('dot');
		$marker_small = $this->get_marker_meta('small');
		$marker_large = $this->get_marker_meta('large');
		
		echo '
			<script type="text/javascript">
			//<![CDATA[
			
			// Google Icons for WP Geo
			var wpgeo_icon_dot = wpgeo_createIcon(' . $marker_dot['width'] . ', ' . $marker_dot['height'] . ', ' . $marker_dot['anchorX'] . ', ' . $marker_dot['anchorY'] . ', "' . $marker_dot['image'] . '", "' . $marker_dot['shadow'] . '");
			var wpgeo_icon_small = wpgeo_createIcon(' . $marker_small['width'] . ', ' . $marker_small['height'] . ', ' . $marker_small['anchorX'] . ', ' . $marker_small['anchorY'] . ', "' . $marker_small['image'] . '", "' . $marker_small['shadow'] . '");
			var wpgeo_icon_large = wpgeo_createIcon(' . $marker_large['width'] . ', ' . $marker_large['height'] . ', ' . $marker_large['anchorX'] . ', ' . $marker_large['anchorY'] . ', "' . $marker_large['image'] . '", "' . $marker_large['shadow'] . '");
			
			//]]>
			</script>';
			
	}
	
	

	/**
	 * Get Marker Meta
	 */
	function get_marker_meta($type = 'large')
	{
		
		// Array
		$marker_types = array();
		
		// Large Marker
		$marker_types['large'] = array(
			'width' => 20,
			'height' => 34,
			'anchorX' => 10,
			'anchorY' => 34,
			'image' => get_bloginfo('url') . '/wp-content/uploads/wp-geo/markers/large-marker.png',
			'shadow' => get_bloginfo('url') . '/wp-content/wp-geo/markers/large-marker-shadow.png'
		);
		
		// Small Marker
		$marker_types['small'] = array(
			'width' => 10,
			'height' => 17,
			'anchorX' => 5,
			'anchorY' => 17,
			'image' => get_bloginfo('url') . '/wp-content/uploads/wp-geo/markers/small-marker.png',
			'shadow' => get_bloginfo('url') . '/wp-content/wp-geo/markers/small-marker-shadow.png'
		);			
		
		// Dot Marker
		$marker_types['dot'] = array(
			'width' => 8,
			'height' => 8,
			'anchorX' => 3,
			'anchorY' => 6,
			'image' => get_bloginfo('url') . '/wp-content/uploads/wp-geo/markers/dot-marker.png',
			'shadow' => get_bloginfo('url') . '/wp-content/wp-geo/markers/dot-marker-shadow.png'
		);
		
		// Default return
		return $marker_types[$type];
		
	}
	
	
	
}



?>