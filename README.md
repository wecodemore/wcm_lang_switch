# WCM User Language Switcher #
**Contributors:** stephenh1988, F J Kaiser
**Tags:** Language, switcher, localisation
**Tested up to:** 3.5
**Stable tag:** 1.5.1
**Requires at least:** 3.5
**License:** GPL3
**License URI:** http://www.gnu.org/licenses/gpl-3.0.html

Adds a button to the admin toolbar. This buttons allows users to seamlessly switch between available languages.

## Description ##

WCM Language Switcher adds a button to the admin toolbar that allows users to seamlessly switch between available languages.

WeCodeMore (WCM) is your label for high quality WordPress code from renowned authors.

If you want to get updates, just follow us on…

 * [our page on Google+](https://plus.google.com/b/109907580576615571040/109907580576615571040/posts)
 * [our GitHub repository](https://github.com/wecodemore)

## Installation ##

Extract the zip file and just drop the contents in the wp-content/plugins/ directory of your WordPress installation and then activate the Plugin from Plugins page.

It scans the language directory to build the list of available languages. To add a language, simple [download the appropriate mo file](http://codex.wordpress.org/WordPress\_in\_Your_Language\) and add to your WordPress language folder.

This plug-in was originally built as a means of plug-in and theme developers to test translations of their plug-ins or themes.

## Frequently Asked Questions ##

### Filters ###

If you want to extend the list of available languages, then please use the provided filter. Here's an example (mu)plugin:

<pre>
    <?php
**    /* Plugin Name:** (WCM) Add additional languages */
    add_filter( 'uls_get_langs', 'wcm_add_languages' );
    function wcm_add_languages( $languages )
    {
    	return array_merge( $languages, array(
    		 'de_DE' // German
    		,'es_ES' // Spanish
    		,'ja'    // Japanese
    	) );
    }
</pre>

## Screenshots ##

###1. The plugin in action in the admin bar.###
![The plugin in action in the admin bar.](http://s.wordpress.org/extend/plugins/wcm-user-language-switcher/screenshot-1.png)


## Changelog ##

### 1.5.1 ###

* Better file organisation. Moved JSON files to separate folder.

### 1.5 ###

* Added local/native JSON strings data file.
* Extended the dev tools parser to include the native data for the JSON files that are used for the UI.

### 1.4 ###

* Added remote location to fetch a complete list of ISO 639-x strings from.
* Added a parser to the dev tools.

### 1.3 ###

* Added dev tools

### 1.2 ###

*** Bug fix:** Now has right language string.

### 1.1 ###

* Initial Version in the official repo.
* Now works with the ISO 639-2, which adds support for nearly every language.

### 1.0 ###

* Bug fixes
* Speed improvements. Props Thomas "toscho" Scholz

### 0.9 ###

* Moved to JSON file. Works with compressed file. Has an uncompressed version for live sites.

### 0.9 ###

* Moved to JSON file. Works with compressed file. Has an uncompressed version for live sites.

### 0.9 ###

* Moved to JSON file. Works with compressed file. Has an uncompressed version for live sites.
* Switched license to GPL3

### 0.8 ###

* Reworked plugin code to a more readable code styling and maximum line length (GitHub page width).

### 0.7 ###

* Bug fixes

### 0.6. ###

* Moved from a GitHub Gist to a GitHub.
