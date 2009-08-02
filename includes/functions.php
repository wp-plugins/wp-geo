<?php



/**
* @package WP Geo
* @subpackage Includes > Functions
*/




/**
 * @method       CSS Dimension
 * @description  If numeric assumes pixels and adds 'px', otherwise treated as string.
 */

function wpgeo_css_dimension( $str = false )
{

	if ( is_numeric( $str ) )
	{
		$str .= 'px';
	}
	return $str;

}



/**
 * @method       Check Domain
 * @description  This function checks that the domainname of the page matches the blog site url.
 *               If it doesn't match we can prevent maps from showing as the Google API Key will not be valid.
 *               This prevent warnings if the site is accessed through Google cache.
 */

function wpgeo_check_domain()
{

	$host = 'http://' . rtrim( $_SERVER["HTTP_HOST"], '/' );
	
	// Blog might not be in site root so strip to domain
	$blog = preg_replace( "/(http:\/\/[^\/]*).*/", "$1", get_bloginfo( 'siteurl' ) );
	
	$match = $host == $blog ? true : false;
	
	return $match;
	
}



?>