=== Minimalistic Event Manager ===
Contributors: de-ce, tar.gz
Tags: dates, events, time
Requires at least: 3.0
Tested up to: 4.8.1
Stable tag: 1.1.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

A simple and flexible solution for managing event dates.

== Description ==

This plugin (MEM or Minimalistic Event Manager) is intended for theme authors and developers who want a simple, robust and flexible solution for managing event dates. 

The MEM plugin offers a clean interface that allows to add _event dates_ (start dates, end dates, repeating dates) to _posts_ (and to any custom post type).

= The main features =

- Event dates can be attached to any post type: Posts, Pages, Custom Post Types... (optionally, they can be restricted to specific content types).
- Event dates can be of three types: start dates, end dates, repeating dates.
- There is no mandatory date format: Time, Day, and Month are optional. An event date could be "January 2046", or just "2046".

= What the plugin doesn’t do =

The MEM plugin works as a simple interface for entering those dates into custom fields, but it won’t do much on the public side of your theme (there's a simple widget for displaying a list of events).

The plugin was created primarily to enable theme developers to create their own display of events on the front-end.

If you aren’t sure how to achieve this, [our wiki](https://github.com/ms-studio/minimalistic-event-manager/wiki) will provide a collection of examples to get you started.

= Technical implementation =

Technically, the MEM plugin stores the dates as ordinary Custom Fields, in a _machine-readable_ format (such as "2016-11-23 13:37"). This gives you absolute freedom for displaying your events in your theme:

- You can query for specific date ranges by using the meta_key / meta_compare parameters.
- You can use the php `date` function to display the date in any possible manner (or `date_i18n` for proper localization).

= Plugin options =

The plugin has two settings that can be modified with a few lines in your `functions.php` file:

- You can limit it to specific post types.
- You can use an "alpha" mode, which gives you a blank input field for each date. This can be useful if you need to enter dates that don’t fit the predefined format.

See the online documentation for the full instructions.

= Links =

[**Documentation**](https://github.com/ms-studio/minimalistic-event-manager/wiki)

= Credits =

- **Concept and maintenance** [ms-studio.net](http://ms-studio.net)
- **Initial development**: [Dream Production](http://dreamproduction.net/)

== Installation ==

1. Upload the plugin to your plugins directory
2. Activate the plugin through the 'Plugins' menu in WordPress

For more detailed instructions, see [Installing Plugins](http://codex.wordpress.org/Managing_Plugins#Installing_Plugins).

After activating the plugin, refer to the [wiki](https://github.com/ms-studio/minimalistic-event-manager/wiki) for further instructions.

== Frequently Asked Questions ==

= There are hundreds of plugins doing exactly this — why a new one? =

All the date management plugins we tested are more complicated than needed. We tried many of them. We believe that our concept is more simple, more robust and more flexible. :) 

= Is the plugin localized? =

Yes it is! For now, it supports English, French and German. Since the interface uses about 4 words, it's not much work to translate it for other languages.

= Can I find you on GitHub? =

Of course, here: [https://github.com/ms-studio/minimalistic-event-manager/](https://github.com/ms-studio/minimalistic-event-manager/) 

== Screenshots ==

1. The initial state, no dates are set yet.
2. The start date is being entered.
3. An event with start date, repeating twice.

== Changelog ==

= 1.1.1 =

* Fixing plugin textdomain
* updating german translation 

= 1.1.0 =

* version bump (WP compatibility)

= 1.0.9 =

* widget improvement: added filter (mem_event_date_display) that allows to customize the widget date format.

= 1.0.8 =

* widget bugfix: when number of upcoming events is greater than display limit, events in the near future should be displayed first.
* removed useless html tag in widget output, thanks to @macemmek
* fixed warning message, thanks to @danhollywells

= 1.0.7 =

* do not load the widget code if PHP is lower than 5.3, to avoid white screen of death.

= 1.0.6 =

* version bump (fixing an issue in 1.0.5 where the funtions directory wasn't included).

= 1.0.5 =

* adding a widget to display a list of events on the frontend.
* adding a function that facilitates custom display of event information.

= 1.0.4 =
* adapting to post-MP6 dashboard design
* removing 32-characters limitation when using "alpha" mode
* adding some date format translation strings, useful when using [the mem-extras](https://github.com/ms-studio/mem-extras)

= 1.0.3 =
* uses the icon font of MP6 if available

= 1.0.2 =
* improves css to adhere to MP6
* fixes a php error visible in debug mode
* fixes a js error, props to @Dan2k3k4

= 1.0.1 =
* fixes activation error on some older php versions

= 1.0 =
* initial public release
