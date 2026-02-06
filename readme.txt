=== RationalCleanup ===
Contributors: rationalwp
Tags: cleanup, performance, security, optimization, disable xmlrpc
Requires at least: 5.0
Tested up to: 6.9
Requires PHP: 7.4
Stable tag: 1.1.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Clean up legacy WordPress bloat, improve security, and optimize performance with toggleable, opinionated defaults.

== Description ==

RationalCleanup removes unnecessary WordPress features, hardens security, and improves performance. All 24 options are toggleable with sensible defaults that balance security and compatibility.

= Features =

**Head Tags**
Remove unnecessary meta tags and links from the document head:

* Remove WordPress generator meta tag (hides version number)
* Remove WLW manifest link
* Remove RSD link
* Remove shortlink
* Remove REST API discovery link
* Remove RSS feed links

**Frontend Bloat**
Remove scripts and styles that most sites don't need:

* Remove emoji detection scripts and styles
* Remove jQuery Migrate from frontend
* Remove Gutenberg block library CSS
* Remove global styles and SVG filters

**Security**
Harden WordPress against common attack vectors:

* Disable XML-RPC completely (prevents brute force and DDoS attacks)
* Prevent user enumeration (blocks author archives and REST API user endpoints)
* Obfuscate login error messages (prevents username discovery)

**Performance**
Reduce unnecessary WordPress overhead:

* Disable self-pingbacks
* Throttle Heartbeat API (reduces server load)
* Extend autosave interval (reduces database writes)

**Features**
Disable major WordPress subsystems:

* Disable comments system completely
* Disable block editor (force classic editor)
* Disable REST API for non-authenticated users

**Admin Cleanup**
Declutter the WordPress admin dashboard:

* Remove WordPress Events and News widget
* Remove Quick Draft widget
* Remove At a Glance widget
* Remove Activity widget
* Remove Site Health Status widget

= Opinionated Defaults =

RationalCleanup uses sensible defaults:

* **Security options:** Enabled by default (XML-RPC disabled, user enumeration blocked)
* **Head cleanup:** Mostly enabled (safe, no compatibility issues)
* **Frontend cleanup:** Emoji and jQuery Migrate removal enabled
* **Breaking features:** Disabled by default (comments, block editor, REST API restrictions)
* **Admin widgets:** Disabled by default

= RationalWP Menu =

This plugin uses a shared parent menu for all RationalWP plugins. When activated, you'll see a **RationalWP** menu in your admin sidebar containing links to all installed RationalWP plugins.

== Installation ==

1. Upload the `rationalcleanup` folder to `/wp-content/plugins/`
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Configure settings under **RationalWP > Cleanup** in the admin menu

== Frequently Asked Questions ==

= What happens when I activate the plugin? =

The plugin applies its default settings immediately. Security options (XML-RPC disabled, user enumeration blocked, login error obfuscation) and safe cleanup options (emoji removal, jQuery Migrate removal, head tag cleanup) are enabled by default.

= Will this break my site? =

The default settings are designed to be safe for most sites. Features that could cause compatibility issues (disabling comments, block editor, or REST API) are disabled by default. You can enable them if needed after reviewing their impact.

= Are settings preserved when I deactivate the plugin? =

Yes, your settings are preserved when you deactivate the plugin. They are only deleted if you completely uninstall (delete) the plugin.

= Does this work with caching plugins? =

Yes, RationalCleanup works well with caching plugins. After changing settings, you may need to clear your cache to see the effects on the frontend.

= Will disabling XML-RPC break anything? =

XML-RPC is a legacy API that most modern sites don't need. However, if you use the WordPress mobile app, Jetpack, or certain third-party services that rely on XML-RPC, you should leave it enabled.

= What does "Prevent user enumeration" do? =

It blocks two common methods attackers use to discover usernames: author archive URLs (/?author=1) and the REST API users endpoint (/wp-json/wp/v2/users). Logged-in administrators can still access these.

== Screenshots ==

1. Settings page overview showing all toggleable cleanup options
2. Security and Performance sections
3. Features and Admin cleanup sections

== Changelog ==

= 1.1.0 =
* Added third-party dashboard widget management — detect and disable widgets added by other plugins
* Added plugin banner and icon assets for WordPress.org listing

= 1.0.1 =
* Text domain fix

= 1.0.0 =
* Initial release
* 24 toggleable options across 6 categories
* Head tag cleanup (generator, WLW, RSD, shortlink, REST API link, RSS feeds)
* Frontend cleanup (emoji, jQuery Migrate, block CSS, global styles)
* Security hardening (XML-RPC, user enumeration, login errors)
* Performance optimization (self-pingbacks, heartbeat, autosave)
* Feature toggles (comments, block editor, REST API)
* Admin dashboard cleanup (5 widget removal options)

== Upgrade Notice ==

= 1.0.0 =
Initial release.
