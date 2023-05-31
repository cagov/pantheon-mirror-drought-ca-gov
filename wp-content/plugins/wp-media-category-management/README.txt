=== Plugin Name ===
Contributors: DeBAAT, freemius
Donate link: https://www.de-baat.nl/WP_MCM
Tags: media library, bulk action, bulk toggle, toggle category, taxonomy, taxonomies, attachment, media category, media categories, media tag, media tags, media taxonomy, media taxonomies, media filter, media organizer, media types, media uploader, custom, media management, attachment management, files management, user experience, wp-admin, admin, user media management, export
Requires at least: 5.0
Tested up to: 6.2.2
Stable tag: 2.1.1
License: GPLv3
License URI: https://www.gnu.org/licenses/gpl-3.0.html

A plugin to provide bulk category management functionality for media in WordPress sites.

== Description ==
This WordPress plugin will ease the management of media categories, including bulk actions.
It supports categories for media using either the existing post categories or a dedicated media_category custom taxonomy.
The plugin supports easy category toggling on the media list page view and also bulk toggling for multiple media at once.
It now also supports post tags and media taxonomies defined by other plugins.

= Main Features =

* Use post categories or dedicated MCM media categories.
* Control your media categories via admin the same way as post categories.
* Bulk toggle any media taxonomy assignment from Media Library via admin.
* Filter media files in Media Library by your custom taxonomies, both in List and Grid view.
* Use new or existing shortcode to filter the media on galleries in posts and pages.
* Use a default category while uploading (see FAQ section). 

= Premium Features =

* Use functionality to manage whether users are allowed or disallowed to manage media.
* Use WPMCM Gallery block to filter the media on galleries in posts and pages.
* Filter media per user on several places like List or Grid view and showing media with block or shortcode.
* Export MCM Categories and / or attachment information filtered by user.
* Use WP Importer functionality for exported information, even on sites without WP MCM Premium installed.
* Use WPMCM Gallery block layout when using wp-mcm shortcode to filter the media in posts and pages.

== Installation ==

1. Upload plugin folder to '/wp-content/plugins/' directory
1. Activate the plugin through 'Plugins' menu in WordPress admin
1. Adjust plugin's settings on **WP MCM -> Settings**
1. Enjoy WordPress Media Category Management!
1. Use shortcode `[wp_mcm taxonomy="<slug>" category="<slugs>"]` in your posts or pages, see also **WP MCM -> Shortcodes**

== Frequently Asked Questions ==

= How do I use this plugin? =

On the options page, define which taxonomy to use for media: either use the standard post taxonomy, a dedicated media taxonomy or a custom media taxonomy.
Define the categories to be used for media.
Toggle category assignments to media, either individually or in bulk.
Use category filter when adding media to posts or pages.

= How do I use this plugin to support the media taxonomy of another plugin? =

There are a number of plugins available for managing media categories.
This plugin now supports the settings previously defined to support those media categories.

Check out the **MCM Settings** page which shows an option "Media Taxonomy To Use".
The dropdown list of this option shows a list of all taxonomies currently used by this WordPress installation.
The option "**(P) Categories**" is the taxonomy defined by default for posts.
The option "**MCM Categories**" is the taxonomy previously defined as "**Media Categories**" by version 1.1 and earlier of this plugin.
If there are other taxonomies currently assigned to attachments, the list shows the corresponding taxonomy slug prefixed with **(*)**.
When such a taxonomy is selected to be used, the taxonomy will be registered anew with the indication "**(*) Custom MCM Categories**".
As long as this taxonomy is selected, the functionality available for "**MCM Categories**" is now available for these "**(*) Custom MCM Categories**", i.e. toggling and filtering.
The name shown for the "**(*) Custom MCM Categories**" can be changed using the option "**Name for Custom MCM Taxonomy**" on the **MCM Settings** page.

= How can I use the "Default Media Category"? =

First enable the option "**Use Default Category**" on the **MCM Settings** page.
When enabled and a media attachment has no category defined yet, the value of "**Default Media Category**" will be assigned automatically when a media attachment is added or edited.
The default value is also used in the `[wp_mcm]` shortcode to automatically filter the attachments to be shown.

= Steps to assign a default category while uploading: =

1. Enable "Use default category" in Settings
1. Define the default category to use
1. Upload the media for this category
1. Repeat from step 2 for other categories

= How do I use the shortcode of this plugin? =

Use the `[wp_mcm]` shortcode. Various shortcode uses are explained in the **WP MCM -> Shortcodes** page.

== Screenshots ==

1. The admin page showing the options for this plugin.
2. Managing the new Media Category taxonomy.
3. Setting Media Category options for a media post.
4. Media List page view showing individual toggle options for first media post.
5. Media List page view showing bulk toggle actions for selected media post.
6. Media List page view showing filter options for Media Categories.
7. Media Grid page view showing filter options for Media Categories.
8. The admin page showing the shortcode explanations for this plugin.
9. The post edit page showing an example using the [wp-mcm category="fotos"] shortcode.
10. The post page showing the results of the example using the [wp-mcm category="fotos"] shortcode.
11. User List page view showing additional user settings for managing media.
12. User Profile page view showing additional user settings for managing media.
13. The post edit page showing an example using the WPMCM Gallery block filtering on MCM Categories Fotos.
14. The post page showing the results of the example using the WPMCM Gallery block filtering on MCM Categories Fotos.
15. The admin page showing the options for Import - Export using this plugin.

== Changelog ==

= 2.1.1 =
* Fixed issue when searching media on front page
* Improved language translation strings

= 2.1.0 =
* Fixed issue with wp_get_list_item_separator to support pre-6.0.0 WordPress versions
* Only flush rewrite rules when updating WP MCM Options
* Improved handling WP MCM Category archives
* Added new block WPMCM Categories to show a list of WP MCM Categories [Premium only]
* Added size and link to attributes to WPMCM Gallery block [Premium only]
* Added new template page to optionally use for WP MCM Category archive pages [Premium only]
* Updated Freemius sdk to v2.5.8

= 2.0.2 =
* Added remove_menu_page for Media Menu for preventing disallowed users to manage any media
* Fixed issue with error_log message showing when not appropriate
* Added some premium features to the details section above [Premium only]

= 2.0.1 =
* Fixed create roles for update

= 2.0.0 =
* Tested for WP 6.2.
* Refactored complete plugin base
* Reworked to support Freemius premium functionality
* Reworked debugMP functionality

= 1.9.9.1 =
* Tested for WP 6.1.

= 1.9.9 =
* Tested for WP 6.0.
* Reworked debugMP functionality
* Fixed handling wp_mcm_flush_rewrite_rules to take less performance

= 1.9.8(.1) =
* Tested for WP 5.8.2.
* Fixed CSS bug preventing showing selector when inserting images

= 1.x.y =
* Previous upates...

== Upgrade Notice ==

* See change log.
