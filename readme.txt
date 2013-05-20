=== Shownotes ===
Contributors: simonwaldherr
Donate link: https://flattr.com/profile/simonwaldherr
Tags: shownotes, osf, md, markdown, markup, podcast
Requires at least: 3.2
Tested up to: 3.5
Stable tag: 0.2.5
License: MIT License
License URI: http://opensource.org/licenses/mit-license.php

simplifies Show Notes, write them in OSF, get them as HTML

== Description ==

This Plugin converts <a href="https://github.com/shownotes/OSF-in-a-Nutshell/blob/master/OSF-in-a-Nutshell.md">Shownotes in OSF</a> to HTML

== Installation ==

1. Upload the `shownotes-shortcode` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the `Plugins` menu in WordPress
3. Use the `[shownotes]` shortcode in your post or page with the options on the front page.
4. Visit the options page

== Frequently Asked Questions ==

= What does this Plugin? =

convert OSF to HTML

= What is OSF? =

a simple format for (Podcast-) Shownotes, more informations about OSF are at the <a href="https://github.com/shownotes/OSF-in-a-Nutshell/blob/master/OSF-in-a-Nutshell.md">OSF-in-a-Nutshell Repo</a>

= Why should i use it? =

it's easy to write

== Support ==

= only on GitHub =

if you need help, go to <a href="https://github.com/SimonWaldherr/wp-osf-shownotes/issues">github.com/simonwaldherr/wp-osf-shownotes</a>

== Screenshots ==

1. Options
2. Result (Anycast style)
3. Result (Wikigeeks style)
4. Meta box for OSF/MD input

== Changelog ==

= 0.2.5 =
* delimiter bug fixed
* titles added to items
* switch between in- and exclude tags
* much better tags handling

= 0.2.4 =
* put osf code in a separate file
* requires at least 3.1 (no, not really, but there is no reason to run WP3.0)
* filter disturbing chars at beginning and end of items
* fix custom field rename bug

= 0.2.3 =
* rename custom field shownotes to _shownotes to hide it from custon fields list
* open links in new tab/window
* podcaster template added
* header/get persons code improved
* change - to &amp; #8209;
* show more debug info

= 0.2.2 =
* delimiter after last item added

= 0.2.1 =
* delimiter after subitem bugfix

= 0.2 =
* debug mode added
* only show template associated options
* chapter delemiter added
* preview in popup added

= 0.1.3 =
* show all podcasts in dropdown (asterisk)

= 0.1.2 =
* better templates
* various improvements
* load Pads and Padcontent via AJAX
* better shortcode handling

= 0.1.1 =
* delimiter changeable
* shortcodes changeable
* better shownoter matching
* also include subitems without tags
* small bugs removed

= 0.1.0 =
* typographically correct quotation marks
* export modes renamed and glossary added
* show shownoter (as new output mode)
* tags selectable via attributes
* markdown support added
* settings rearranged

= 0.0.6 =
* small fullmode fix

= 0.0.5 =
* shownot.es CSS can be turned off
* import from ShowPad
* export tags changeable

= 0.0.4 =
* wikigeeks style added

= 0.0.3 =
* first Version on Wordpress.org.
* working Version with meta_box and shortcode content.

== Upgrade Notice ==

= 0.2.5 =
much better tag handling and configurability added and small bugs fixed

= 0.2.4 =
small fixes

= 0.2.3 =
shownotes custom field renamed to _shownotes to hide it from custom fields list and important improvements

= 0.2.2 =
delimiter after last item added

= 0.2.1 =
delimiter after subitem bugfix

= 0.2 =
preview added, chapter delimiter added, options improved

= 0.1.3 =
show all podcasts in import dropdown

= 0.1.2 =
pad import and output templates improved

= 0.1.1 =
more adjustment options and tiny improvements

= 0.1.0 =
many important improvements

= 0.0.6 =
fullmode fixed

= 0.0.5 =
export tags now changeable and osf shownotes can be imported via ajax

= 0.0.4 =
new style added (wikigeeks)

= 0.0.3 =
first Version on Wordpress.org.