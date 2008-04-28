=== WP Geo  ===
Contributors: Ben Huson
Donate link: http://www.benhuson.co.uk/wordpress-plugins/wp-geo/
Tags: maps, map, geo, geocoding, google, location
Requires at least: 2.5
Tested up to: 2.5
Stable tag: 1.0

Add location maps to your posts and pages.

== Description ==
When editing a post or page, you will be able to set a physically location for that post. You can select the location by:

1. Clicking on the map to position the point.
2. Searching for a location, town, city or address.
3. Entering the latitude and longitude. 

The WP Geo location selector is styled to fit seemlessly into the latest version of the WordPress admin.

== Installation ==
1. Download the archive file and uncompress it.
2. Put the "wp_geo" folder in "wp-content/plugins"
3. Enable in WordPress by visiting the "Plugins" menu and activating it.
4. Go to the Settings page in the admin and enter your Google API Key.

(you can sign up for a Google API Key at http://code.google.com/apis/maps/signup.html)

WP Geo will appear on the edit post and edit page screens.
If you set a location, a Google map will automatically appear at the top of that post or page.

You can add a map you your category pages to which will display the locations of any posts within that category.
Simply enter <?php WPGeo::categoryMap(); ?> into your category template where you would like the map to appear.

== Important Note ==


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
