=== IndyPress ===
Contributors: boyska
Tags: indymedia, imc, open publishing
Requires at least: 3.0
Tested up to: 3.3
Stable tag: trunk

IndyPress will make WP an Indypendent Media Center: its main feature is
OpenPublishing, but it will do much more

== Description ==

### Features

* Extremely flexible open publishing support
* Rich interface for open publishing (see below)
* Posts and comments can be hidden (instead of deleted)
* Posts and comments can be signaled to admin
* Premoderation support: see its own section
* UI Internationalization
* Event support
* Widget for displaying next events

#### Open Publish form features

* TinyMCE interface for rich text editing, upload and media embed
* Form validation: if the user is missing some fields, JS warns it immediately
* HTML5 form attributes (newest browser will autovalidate some fields)

#### Non-features

Indypress does NOT have these features:

* Multi language articles: this is not even in our wishlist.

== Installation ==

The same old way: search "indypress" on your "add plugin" page, then install and activate it

== Upgrade notice ==

= 1.0 =

HUGE switch: wp 3.3, better configurability; BUT it requires everything to
be configured in a different way.  This is not very difficult, but has to be
done.  Also, all the "event"-related functionalities has been moved to a
separate plugin, which is bundled together. 

== Frequently Asked Questions ==

= It is to difficult to configure! How can I just test it in a easy way =

Go to plugin page and activate the "Indypress Base Configuration": this will
enable a simple, "standard" configuration

= Indypress Base Configuration is fine, but TOO limited; a compromise? =

On https://code.autistici.org/trac/indypress/wiki/FormPresets you'll find lot of
"presets": settings that has been done, tested, sometimes actually used in
production site. Sometime they require a bit of customization for your needs

== Screenshots ==

These are just samples, but indypress is much more flexible than this!

1. Typical publish page (on twentyeleven)
2. Plugin status page, with summary informations
3. Make some forms accessible only by logged users with some permissions

== Changelog ==

= 2011.01 =
Huge rewrite	

= 0.5.1 =
i18n fixes

	boyska (2):
		  Fixes i18n problems (string quoting)
		  Fix: return of get_indy_publish_permalink

= 0.5 =
Introduces i18n

	boyska (4):
		  Some publish-related fix about categories
		  The events page works with terms, too
		  Add functions for publish page URL
		  Bump to 0.5
	capzioso (1):
		  Aggiunto supporto internazionalizzazione

