=== WCM User Language Switcher ===
Contributors: stephenharris, F J Kaiser
Tags: Language, switcher, localisation
Tested up to: 3.6.1
Stable tag: 1.7.5
Requires at least: 3.6.1
License: GPL3
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Adds a button to the admin toolbar. This buttons allows users to seamlessly switch between available languages.

== Description ==

WCM Language Switcher adds a button to the admin toolbar that allows users to seamlessly switch between available languages.

WeCodeMore (WCM) is your label for high quality WordPress code from renowned authors.

If you want to get updates, just follow us on…

 * [our page on Google+](https://plus.google.com/b/109907580576615571040/109907580576615571040/posts)
 * [our GitHub repository](https://github.com/wecodemore)

== Installation ==

Extract the zip file and just drop the contents in the <code>~/wp-content/plugins/</code> directory of your WordPress installation and then activate the Plugin from Plugins page.

It scans the language directory to build the list of available languages. To add a language, simple [download the appropriate mo file](http://codex.wordpress.org/WordPress_in_Your_Language "WordPress .mo file download") and add to your WordPress language folder.

This plugin was originally built as a means of plugin and theme developers to test translations of their plugins or themes.

== Frequently Asked Questions ==

= Filters =

If you want to extend the list of available languages, then please use the provided filter. Here's an example (mu)plugin:

<pre>
    <?php
    /* Plugin Name: (WCM) Add additional languages */
    add_filter( 'wcm_get_langs', 'wcm_add_languages' );
    function wcm_add_languages( $languages )
    {
    	return array_merge( $languages, array(
    		'de_DE', // German
    		'es_ES', // Spanish
    		'ja',    // Japanese
    	) );
    }
</pre>

== Screenshots ==

1. The plugin in action in the admin bar.

== Changelog ==


= 1.7.5 =

* Removed legacy method.
* Removed empty lines to shorten file.

= 1.7.4 =

* Fixed wrong assumption about default language.

= 1.7.3 =

* Added support for composer.

= 1.7.2 =

* Removed deprecated <code>wcm_get_user_locale()</code> from public API.

= 1.7.1 =
PHP 5.2 work-around for json_last_error()

= 1.7 =

* Changes to feat. request/enhancement #18: Now shows the native string in the toolbar menu
* Removes the current language as menu item
* Adds the international/English language string + ISEO 639-2 code as HTML title attr. to the items.
* Switched from <code>$wp_admin_bar->add_menu()</code> to the newer API method <code>add_node()</code>.

= 1.6.6 =

* Improved dev tools. Now also counts the number of available ISO 639-2 language codes.

= 1.6.5 =

* Deprecated <code>wcm_get_user_locale()</code> (replaced with <code>wcm_get_user_lang()</code> and tell users about it.
* Improved code readability on <code>format_code_lang()</code>

= 1.6.4 =

* Consistent naming according to GitHub issue #21
* <strong>Devlopers:</strong> The filter names changed as well as the public API function.

= 1.6.3 =

* Start earlier on <code>plugins_loaded</code> hook to let other plugins jump in with the default priority.

= 1.6.2 =

* phpDocBlock fixes
* fixed references to static values so PhpStorm can handle them
* Slightly faster checks against NULL
* fixes <code>E_STRICT</code> error in <code>reset()</code> inside <code>format_lang_code()</code>. Props toscho.

= 1.6 =

* Dev Tools extended and running stable. Now updating from the remote source works perfectly.
* Better file organisation. Moved JSON files to separate folder.
* Fixed (due to refactoring) broken JSON compress dev tools.

= 1.5 =

* Added local/native JSON strings data file.
* Extended the dev tools parser to include the native data for the JSON files that are used for the UI.

= 1.4 =

* Added remote location to fetch a complete list of ISO 639-x strings from.
* Added a parser to the dev tools.

= 1.3 =

* Added dev tools

= 1.2 =

* Bug fix: Now has right language string.

= 1.1 =

* Initial Version in the official repo.
* Now works with the ISO 639-2, which adds support for nearly every language.

= 1.0 =

* Bug fixes
* Speed improvements. Props Thomas "toscho" Scholz

= 0.9 =

* Moved to JSON file. Works with compressed file. Has an uncompressed version for live sites.

= 0.9 =

* Moved to JSON file. Works with compressed file. Has an uncompressed version for live sites.

= 0.9 =

* Moved to JSON file. Works with compressed file. Has an uncompressed version for live sites.
* Switched license to GPL3

= 0.8 =

* Reworked plugin code to a more readable code styling and maximum line length (GitHub page width).

= 0.7 =

* Bug fixes

= 0.6. =

* Moved from a GitHub Gist to a GitHub.
