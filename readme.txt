=== Sync My Rex ===
Contributors: svincoll4
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=QZUJY7DR3JYWS&lc=VN&item_name=Rex%20Sync%20WordPress%20Plugin&currency_code=USD&bn=PP%2dDonationsBF%3abtn_donateCC_LG%2egif%3aNonHosted
Tags: rex, rexsoftware, listing, realestate, property
Requires at least: 4.7
Tested up to: 6.5.5
Stable tag: 2.2.2
Requires PHP: 7.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Providing tool to sync all listings, listing agents from Rex Software to WordPress

== Description ==

**[IMPORTANT NOTE:] This plugin is developed by third party developer, all support requests relating to the plugin need to go to the plugin author.**

SIMPLE & POWERFUL TOOL TO SYNC LISTINGS FROM REX SOFTWARE

This plugin is providing you a tool to sync all listings, listing agents from Rex Software to WordPress.

There are no special requirements on this, you just need the username/password to login Rex Software dashboard.

FEATURES:

* Simple setup
* Manual sync all listings, listing agents by just one-click
* Automatic sync listings via the webhook which is setup from Rex Dashboard
* Ability to customize all custom fields
* Ability to download featured image
* Support Queues to track and view the sync progress
* Support filters/hooks for developers to extend functions

PREMIUM FEATURES at [Sync My Rex Pro edition](https://rex-sync.com)

* Ability to download gallery
* Ability to download agent profile image
* Templates for archive listings, single listing, agent
* Sub/child account login
* Search form
* Shortcodes for archive listings, single listing, agent, [see shortcodes](https://rex-sync.com/shortcodes-included/)
* Widgets for WPBakery page builder, [see widgets](https://rex-sync.com/wpbakery-page-builder-widgets/)
* Widgets for Elementor builder, [see widgets](https://rex-sync.com/elementor-widgets/)
* Premium support per subscription

[Upgrade to the Pro edition of Sync My Rex.](https://rex-sync.com)

PREMIUM SUPPORT

Support for the free version of Sync My Rex is handled through the WordPress.org community forums.

Support is not guaranteed and is based on ability. For premium support over email, purchase [Pro edition of Sync My Rex](https://rex-sync.com).

== Frequently Asked Questions ==

= Does this plugin download listing images to WordPress Media? =

Yes, this plugin supports to download featured image from Rex Software, for other images, it is worth to check Pro version at [Sync My Rex Pro](https://rex-sync.com).

= Does this plugin download listings automatic? =

Yes, this plugin supports to download listings automatic using the webhook, [see document here](https://rex-sync.com/how-to-setup-webhook/)

= How does this appear on the front end? =

Pro version supports basic templates, shortcodes and widgets for Elementor, WPBakery page builder. [Checkout Sync My Rex Pro](https://rex-sync.com).

= Cannot validate account? =

Firstly, make sure your login is correct and working good by logging in Rex Software. You must be a Rex user(customer).
Secondly, check the error log to see any error message, you may need a developer to do this or ask your server support guys.
All above things don't work? Post it to [forum](https://wordpress.org/support/plugin/rex-sync/) to get help from people.


== Screenshots ==

1. Settings
2. Manual sync by just one-click
3. Queues
4. Custom fields mapping

== Changelog ==

= 1.0 =
* Initial plugins

= 1.0.1 =
* Move schedule events out of the activation function
* Add assets

= 1.0.2 =
* Add header X-App-Identifier

= 1.0.3 =
* Update stable version, re-check on latest WordPress 6.2

= 2.0 =
* Add ability to validate account
* Add ability to download featured image
* Add ability to select image sizes while downloading image
* Remove cron job and cron interval
* Add progress bar to Manual Sync page
* Download listings proactive using AJAX
* Provide more error details on each AJAX actions

= 2.0.1 =
* Fix some modifiers

= 2.0.2 =
* Change token to lifetime token
* Introduce pro version on admin page
* Display error message on fail queues
* Stop process when no listings downloaded

= 2.1.0 =
* Change to use single token per section
* Support region UK
* Display error message on Logs file

= 2.1.1 =
* Fix issue which cause the auto syncing stopped

= 2.2.0 =
* Remove un-necessary methods and classes
* Change to use class PublishedListing
* Increase number of listings to download faster
* Fix link issue on page Queues

= 2.2.1 =
* Fix issue on categories

= 2.2.2 =
* Rename plugin name to avoid confusion the plugin author
* Add some description
* Test on WordPress 6.5.5

== Upgrade Notice ==
