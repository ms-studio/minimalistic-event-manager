=== Minimalistic Event Manager ===
Contributors: Dan Stefancu, Vlad Socaciu, tar.gz
Tags: connections, custom post types, relationships, many-to-many, users
Requires at least: 3.4
Tested up to: 3.5-alpha
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

The plugin allows to add event dates (start dates, end dates) to posts (and to custom post types).

== Description ==

By default the plugin adds the custom MEM metabox to all post types, but this can be changed by
calling the settings function (mem_plugin_settings):

	function my_mem_settings() {
		mem_plugin_settings( array( 'post', 'page', 'event' ), 'alpha' );
	}
	add_action( 'mem_init', 'my_mem_settings' );

Where the first parameter is an array of post types, or an array with "all" as the only value,
and the second parameter the edit mode (full/alpha).

The plugin checks for valid post types before adding the metabox.

== Changelog ==

= 1.0 =
* initial public release
