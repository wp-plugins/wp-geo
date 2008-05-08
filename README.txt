=== WP Geo  ===
Contributors: Ben Huson
Donate link: http://www.benhuson.co.uk/wordpress-plugins/wp-geo/
Tags: maps, map, geo, geocoding, google, location
Requires at least: 2.5
Tested up to: 2.5.1
Stable tag: 1.3

Add location maps to your posts and pages.

== Description ==
When editing a post or page, you will be able to set a physically location for that post. You can select the location by:

1. Clicking on the map to position the point.
2. Searching for a location, town, city or address.
3. Entering the latitude and longitude. 

The WP Geo location selector is styled to fit seemlessly into the latest version of the WordPress admin.

= Features =

*  NEW - Set default width and height for maps
*  NEW - Shortcode [wp_geo_map] to insert map within your post
*  NEW - Select your preferred map type
*  NEW - Select wether to show your map at the top or bottom of posts (or not at all)
*  Set a location by clicking on a map or
*  Set a location by searching for a location, town, city or address or
*  Set a location by entering the latitude and longitude

== Installation ==
1. Download the archive file and uncompress it.
2. Put the "wp_geo" folder in "wp-content/plugins"
3. Enable in WordPress by visiting the "Plugins" menu and activating it.
4. Go to the Settings page in the admin and enter your Google API Key and customise the settings.

(you can sign up for a Google API Key at http://code.google.com/apis/maps/signup.html)

WP Geo will appear on the edit post and edit page screens.
If you set a location, a Google map will automatically appear on your post or page (if your settings are set to).

You can add a map you your category pages to which will display the locations of any posts within that category.
Simply enter <?php WPGeo::categoryMap(); ?> into your category template where you would like the map to appear.

= Upgrading =

If upgrading from a previous version of the plugin:

1. Deactivate and reactivate the plugin to ensure any new features are correctly installed.
2. Visit the settings page after installing the plugin to customise any new options.

== License ==
Copyright (C) 2008 Ben Huson

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

http://www.gnu.org/licenses/gpl.html
