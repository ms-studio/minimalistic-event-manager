=== Minimalistic Event Manager ===
Contributors: Dan Stefancu, Vlad Socaciu, tar.gz
Tags: dates, events, time
Requires at least: 3.4
Tested up to: 3.5-Beta-2
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

This plugin (MEM or Minimalistic Event Manager) is intended for theme authors and developers, who want a simple, robust and flexible solution for managing event dates. The MEM plugin offers a clean interface that allows to add event dates (start dates, end dates, repeating dates) to posts (and to any custom post type).

== Description ==

The plugin offers the following functionality:

* Dates can be attached to any post type: Posts, Pages, Custom Post Types... (optionally, they can be restricted to specific content types).
* Dates can be of three types: start dates, end dates, repeating dates.
* There is no mandatory date format: Time, Day, and Month are optional. An event date could be "January 1969", or just "1969".

== What the plugin doesn't do ==

The MEM plugin works as a simple interface for entering those dates into custom fields, but it won't do anything on the public side of your theme. What happens with the dates on the public side is entirely up to you. You must decide how to display the dates, and you will have to edit your theme files for that.

If you aren't sure how to achieve this, our wiki will provide a collection of examples to get you started.

== Technical implementation ==

Technically, the MEM plugin stores the dates as ordinary Custom Fields, in a "machine-readable" format (such as "2012-11-23 13:37"). This gives you absolute freedom for displaying your events in your theme:

* You can query for specific date ranges by using the meta_key / meta_compare parameters.
* You can use the php date function to display the date in any possible manner (or date_i18n to localize it properly).

== Plugin options ==

The plugin has two settings that can be modified with a few lines in your functions.php file:

* You can limit it to specific post types.
* You can use an "alpha" mode, which means that you won't have the dropdown menus, just a blank input field for each date. This can be useful if you prefer to enter 2012-01-31 strings manually rather using the dropdown, or if you need to enter dates that don't fit the predefined format.

See the online documentation for the full instructions.

== Links ==

[**Documentation**](https://github.com/ms-studio/minimalistic-event-manager/wiki)

== Installation ==

1. Upload the plugin to your plugins directory
2. Activate the plugin through the 'Plugins' menu in WordPress

For more detailed instructions, see [Installing Plugins](http://codex.wordpress.org/Managing_Plugins#Installing_Plugins).

After activating the plugin, refer to the [wiki](https://github.com/ms-studio/minimalistic-event-manager/wiki) for further instructions.

== Credits ==

* **Concept and maintenance** [ms-studio.net](http://ms-studio.net)
* **Development**: [Dream Production](http://dreamproduction.net/)

== Changelog ==

= 1.0 =
* initial public release
