=== WP Review Restaurant ===
Contributors: kishores
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=L5DAL9AXTAG8L
Tags: restaurant listing, restaurant board, restaurant, restaurants, restaurant
Requires at least: 3.8
Tested up to: 4.6
Stable tag: 1.4

Manage restaurant listings from the WordPress admin panel, and allow users to post restaurants directly to your site.

== Description ==

WP Review Restaurant is a **lightweight** plugin for adding restaurant reviews functionality to your WordPress site. Being shortcode based, it can work with any theme (given a bit of CSS styling) and is really simple to setup.

<blockquote>
  <p>
We are woking on completely new version of it. It is still in beta.
<a href="https://github.com/opentuteplus/restaurants-listings">Restaurants Listings</a>
</p>
  <strong>We are not supporting this plugin any more.Thanks</strong>
</blockquote>

= Features =

* Add, manage, and categorise restaurant listings using the familiar WordPress UI.
* Searchable & filterable ajax powered restaurant listings added to your pages via shortcodes.
* Frontend forms for guests and registered users to submit & manage restaurant listings. (coming soon)
* Allow restaurant listers to preview their listing before it goes live. The preview matches the appearance of a live restaurant listing. (coming soon)
* Each listing can be tied to an email or website address so that foodie can send inquire to the restaurants.
* Searches also display RSS links to allow foodie to be alerted to new restaurants matching their search.
* Allow logged in restaurant administrators to view, edit, mark filled, or delete their active restaurant listings. (coming soon)
* Developer friendly code — Custom Post Types, endpoints & template files.
* Add colors to each restaurant types.

The plugin comes with several shortcodes to output restaurants in various formats.

NOTE: Currently it supports only one resturant type.

<a href="http://opentuteplus.com/wp-review-restaurant/">Demo:</a>



== Installation ==

= Automatic installation =

Automatic installation is the easiest option as WordPress handles the file transfers itself and you don't even need to leave your web browser. To do an automatic install, log in to your WordPress admin panel, navigate to the Plugins menu and click Add New.

In the search field type "WP Review Restaurant" and click Search Plugins. Once you've found the plugin you can view details about it such as the the point release, rating and description. Most importantly of course, you can install it by clicking _Install Now_.

= Manual installation =

The manual installation method involves downloading the plugin and uploading it to your webserver via your favourite FTP application.

* Download the plugin file to your computer and unzip it
* Using an FTP program, or your hosting control panel, upload the unzipped plugin folder to your WordPress installation's `wp-content/plugins/` directory.
* Activate the plugin from the Plugins menu within the WordPress admin.

= Getting started =

Once installed:

1. Create a page called "restaurants" and inside place the `[restaurants]` shortcode. This will list your restaurants. 

**Note when using shortcodes**, if the content looks blown up/spaced out/poorly styled, edit your page and above the visual editor click on the 'text' tab. Then remove any 'pre' or 'code' tags wrapping your shortcode.

= Template overrides =
Within the plugin folder there is a ‘templates’ directory where frontend views are stored. This includes, for example, the form and form fields. These templates get loaded by plugin when it needs to display content.

= Overriding templates via a theme =
Template files can be overridden via your theme should you wish to customise them. To override a template, move it to yourtheme/review_restaurant/, keeping the path within ‘templates’ intact.

For example, if I wanted to override pagination.php I would move it to mytheme/review_restaurant/pagination.php and edit my theme’s version.

Please note, if these files are updated in the core plugin, you may need to update your custom version in the future to maintain compatibility. Therefore it is advised to only override the template files you need to customise.

= Overriding templates through code =
Plugin developers can also override templates by filtering the ‘review_restaurant_locate_template’ filter.
<blockquote>
return apply_filters( 'review_restaurant_locate_template', $template, $template_name, $template_path );
</blockquote>
Returning your own template path will override all others.


== Screenshots ==

1. screenshot-1.png
2. screenshot-2.png



== Changelog ==
= 1.4 =
* We are working on a new plugin, so dumping this version soon.
= 1.3 =
* Added restaurant options for search and bug fix
= 1.2 =
* Added restaurant colors options.
= 1.1 =
* Added review count.
= 1.0 =
* First stable release.

== Upgrade Notice ==
= 1.4 =
* We are working on a new plugin, so dumping this version soon.
= 1.3 =
* Added restaurant options for search and bug fix
= 1.2 =
* Added restaurant colors options.
= 1.1 =
* Added review count.
= 1.0 =
* First stable release.