=== Bookmarkfile ===
Contributors: Ekkart Kleinod
Donate link: http://www.ekkart.de/
Tags: bookmark, bookmarks, links
Requires at least: 3.0.1
Tested up to: 3.5.1
Stable tag: 1.1.2
License: GPL2

This plugin displays a given bookmark file as list of links.

== Description ==

This plugin displays a given bookmark file as list of links.

Thus you can display a "link" section without having to maintain the bookmarks in another place than your browser. At the moment, the following browser bookmark files are supported:
* Opera

You use the plugin by placing the `bookmarkfile` tag in your article resp. page.

**Arguments**

* **filename** mandatory argument stating the bookmark filename relative to the plugin or absolute in the server path. *filename* can be every filename accepted by PHP's fopen function.
* **target** optional argument, if given, it is used as target for the links

**Examples**

`[bookmarkfile filename="opera.adr" /]`
Reads the bookmarks from "opera.adr" and displays them as list.

`[bookmarkfile filename="opera.adr" target="_blank" /]`
Reads the bookmarks from "opera.adr" and displays them as list, each link opening a new page resp. tab.

Live example: [link list of ekkart.de](http://www.ekkart.de/?page_id=143)

== Installation ==

1. Upload the directory `bookmarkfile` to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Place `[bookmarkfile filename="<filename>"]` in your templates

== Frequently Asked Questions ==

= Will you support my browser <browsername>? =

Since I am using Opera only, I focus on supporting Opera. If you want your browser supported, I'm afraid you have to write the code. If you want to, I will include the code in the plugin.
Basically, you only have to write the code for reading your browser's bookmark file, the output is generic.

= Where do I have to put my file to? =

You can put your file at any place that can be opened by PHP's fopen function. Please see PHP documentation for allowed entries.

== Screenshots ==

1. page with bookmarkfile code
2. example for display of links

== Changelog ==

= 1.1.2 (2013-3-19) =
* synchronized version numbers in documentation and plugin (otherwise display on wp plugin site did not work correctly)

= 1.1.1 (2013-3-19) =
* just added example link to documentation

= 1.1.0 (2013-3-19) =
* **new parameter** `target` for link targets
* **bugfix** link urls now in double quotation marks

= 1.0.0 (2010-8-16) =
* initial version of the plugin, support for opera bookmark files

== Upgrade Notice ==

= 1.1 =
No upgrade needed.

= 1.0 =
No upgrade needed, since this is the first version.

== Plans for the future ==

* integrate other browsers
* switch display format via argument

