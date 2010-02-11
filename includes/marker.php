<?php



/**
 * @package     WP Geo
 * @subpackage  Marker Class
 * @author      Ben Huson <ben@thewhiteroom.net>
 */



class WPGeo_Marker {
	
	
	
	/**
	 * @properties
	 */
	
	// Reference
	var $id   = null;
	var $name = null;
	
	// Marker Properties
	var $width   = 20;
	var $height  = 34;
	var $anchorX = 10;
	var $anchorY = 34;
	var $image   = null;
	var $shadow  = null;
	
	
	
	/**
	 * @method       Constructor
	 * @description  Initialise the class.
	 */
	
	function WPGeo_Marker( $id, $name, $width, $height, $anchorX, $anchorY, $image, $shadow = null ) {
		
		$this->set_id( $id );
		$this->set_name( $name );
		$this->set_size( $width, $height );
		$this->set_anchor( $anchorX, $anchorY );
		$this->set_image( $image );
		$this->set_shadow( $shadow );
		
	}
	
	
	
	/**
	 * @method       Set ID
	 * @description  Sets the marker's ID.
	 * @param        $id (string)
	 */
	
	function set_id( $id ) {
		
		$this->id = $id;
		
	}
	
	
	
	/**
	 * @method       Set Name
	 * @description  Sets the marker's name.
	 * @param        $name (string)
	 */
	
	function set_name( $name ) {
		
		$this->name = $name;
		
	}
	
	
	
	/**
	 * @method       Set Size
	 * @description  Sets the marker's width and height dimensions.
	 * @param        $width  (int)
	 * @param        $height (int)
	 */
	
	function set_size( $width, $height ) {
		
		$this->width = $width;
		$this->height = $height;
		
	}
	
	
	
	/**
	 * @method       Set Anchor
	 * @description  Sets the marker's anchor coordinates.
	 * @param        $x (int)
	 * @param        $y (int)
	 */
	
	function set_anchor( $x, $y ) {
		
		$this->anchorX = $x;
		$this->anchorY = $y;
		
	}
	
	
	
	/**
	 * @method       Set Image
	 * @description  Sets the marker's image file.
	 * @param        $image (string)
	 */
	
	function set_image( $image ) {
		
		$this->image = $image;
		
	}
	
	
	
	/**
	 * @method       Set Shadow
	 * @description  Sets the marker's shadow image file.
	 * @param        $shadow (string)
	 */
	
	function set_shadow( $shadow ) {
		
		$this->shadow = $shadow;
		
	}
	
	
	
	/**
	 * @method       Get JavaScript
	 * @description  Gets the JavaScript that defines a marker.
	 * @return       (string) JavaScript
	 */
	
	function get_javascript() {
		
		return "var wpgeo_icon_" . $this->id . " = wpgeo_createIcon(" . $this->width . ", " . $this->height . ", " . $this->anchorX . ", " . $this->anchorY . ", '" . $this->image . "', '" . $this->shadow . "');";
		
	}			
	
	
	
}



?>