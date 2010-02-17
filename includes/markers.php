<?php



/**
 * @package    WP Geo
 * @subpackage Markers Class
 * @author     Ben Huson <ben@thewhiteroom.net>
 */



class WPGeo_Markers {
	
	
	
	/**
	 * Properties
	 */
	var $version = '1.0';
	var $marker_image_dir = '/uploads/wp-geo/markers/';
	var $markers;
	
	
	
	/**
	 * @method       Constructor
	 * @description  Initialise the class.
	 */
	function WPGeo_Markers() {
		
		$this->markers = array();
		
		$dir = WP_CONTENT_URL . $this->marker_image_dir;
		
		$this->markers[] = new WPGeo_Marker( 'dot', 'WP Geo: Dot', 8, 8, 3, 6, $dir . 'dot-marker.png', $dir . 'dot-marker-shadow.png' );
		$this->markers[] = new WPGeo_Marker( 'small', 'WP Geo: Dot', 10, 17, 5, 17, $dir . 'small-marker.png', $dir . 'small-marker-shadow.png' );
		$this->markers[] = new WPGeo_Marker( 'large', 'WP Geo: Dot', 20, 34, 10, 34, $dir . 'large-marker.png', $dir . 'large-marker-shadow.png' );
		
	}
	
	
	
	/**
	 * @method       Get Marker by ID
	 * @description  Retur s marker object.
	 */
	function get_marker_by_id( $marker_id ) {
		
		foreach ( $this->markers as $m ) {
			if ( $m->id == $marker_id ) {
				return $m;
			}
		}
		
	}
	
	
	
	/**
	 * @method       Marker Folder Exists
	 * @description  Checks that the marker images folder has been created.
	 * @return       (boolean)
	 */
	function marker_folder_exists() {
		
		if ( is_dir( WP_CONTENT_DIR . '/uploads/wp-geo/markers' ) ) {
			return true;
		}
		return false;
		
	}
	
	
	
	/**
	 * @method       Register Activation
	 * @description  When the plugin is activated, created all the required folder
	 *               and move the marker images there.
	 */
	function register_activation() {
		
		// New Marker Folders
		clearstatcache();
		$old_umask = umask( 0 );
		mkdir( WP_CONTENT_DIR . '/uploads/wp-geo' );
		mkdir( WP_CONTENT_DIR . '/uploads/wp-geo/markers' );
		
		// Marker Folders
		$old_marker_image_dir = WP_CONTENT_DIR . '/plugins/wp-geo/img/markers/';
		$new_marker_image_dir = WP_CONTENT_DIR . $this->marker_image_dir;
		
		// Marker Files
		$this->moveFileOrDelete( $old_marker_image_dir . 'dot-marker.png', $new_marker_image_dir . 'dot-marker.png' );
		$this->moveFileOrDelete( $old_marker_image_dir . 'dot-marker-shadow.png', $new_marker_image_dir . 'dot-marker-shadow.png' );
		$this->moveFileOrDelete( $old_marker_image_dir . 'large-marker.png', $new_marker_image_dir . 'large-marker.png' );
		$this->moveFileOrDelete( $old_marker_image_dir . 'large-marker-shadow.png', $new_marker_image_dir . 'large-marker-shadow.png' );
		$this->moveFileOrDelete( $old_marker_image_dir . 'small-marker.png', $new_marker_image_dir . 'small-marker.png' );
		$this->moveFileOrDelete( $old_marker_image_dir . 'small-marker-shadow.png', $new_marker_image_dir . 'small-marker-shadow.png' );
		
		// Reset default permissions
		umask( $old_umask );
		
	}
	
	
	
	/**
	 * @method       Move File or Delete
	 * @description  Move a file, or replace it if one already exists.
	 */
	function moveFileOrDelete( $old_file, $new_file ) {
		
		if ( !file_exists( $new_file ) ) {
			$ok = copy( $old_file, $new_file );
			if ( $ok ) {
				// Moved OK...
			}
		}
		
	}
	
	
	
	/**
	 * @method       WP Head
	 * @description  Output HTML header.
	 */
	function wp_head() {
		
		$js = '';
		
		foreach ( $this->markers as $m ) {
			$js .= $m->get_javascript();
		}
		
		echo '
		
			<script type="text/javascript">
			//<![CDATA[
			// ----- WP Geo Marker Icons -----
			' . $js . '
			//]]>
			</script>
			
			';
			
	}
	
	
	
}



?>