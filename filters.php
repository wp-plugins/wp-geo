<?php



/**
 * The WP Geo Filters class
 */
class WPGeoFilters
{
	
	

	/**
	 * get_custom_field_posts_join
	 */
	// This join filter is used in conjunction with the get_custom_field_posts_group filter
	// to create a WordPress Query that only returns posts with specific custom fields.
	function get_custom_field_posts_join($join)
	{
	
		global $wpdb, $customFields;
		return $join . " JOIN $wpdb->postmeta postmeta ON (postmeta.post_id = $wpdb->posts.ID and postmeta.meta_key in ($customFields)) ";
	
	}
	
	
	
	/**
	 * get_custom_field_posts_group
	 */
	// This group filter is used in conjunction with the get_custom_field_posts_join filter
	// to create a WordPress Query that only returns posts with specific custom fields.
	function get_custom_field_posts_group($group)
	{
		
		global $wpdb;
		$group .= " $wpdb->posts.ID ";
		return $group;
	
	}
	
	

}



?>