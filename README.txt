=== Multisite Media Manager ===
Contributors: pradhan007p
Donate link:
Tags: multisite, media manager, wordpress, multipress, wordpress multisite, multisite media manager
Requires at least: 3.3
Tested up to: 4.9.*
Stable tag: trunk
Requires PHP: 5.2.4
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Organize Media with ease across all the child websites.

== Description ==

Multisite Media Manager is a WordPress plugin to manage all the uploaded media to one central folder making it accessible across all the Multisite Network. The media is generally stored in parent website folder and can be accessed by all the child website.

This plugin is useful only for the multisite enabled WordPress site.

== Installation ==

1. Upload `multisite-media-manager` to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Update `MUMM_PARENT_ID` to the required parent site ID. This step is optional.
1. Call `mumm_get_featured_image()` function in your templates to get the attached feature image. You can also pass the size as a parameter.

==  Frequently asked questions  ==

=  What is this plugin for?  =
** This plugin is for organizing the media across child websites in multisite enabled WordPress site.

==  Screenshots  ==

==  Changelog  ==

= 1.0.0 =
* Clean up the plugin code and data validations



