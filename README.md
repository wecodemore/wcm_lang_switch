WCM Language Switcher
===============

## Authors

WeCodeMore (WCM) is your label for high quality WordPress code from renown authors.

If you want to get updates, just follow us on…

 * [our page on Google+](https://plus.google.com/b/109907580576615571040/109907580576615571040/posts)
 * [our GitHub repository](https://github.com/wecodemore)

## Descriptions

WordPress plugin that adds a button to the admin toolbar. This buttons allows uses to seamlessly switch languages of the installation.

## Extending the plugin

If you want to extend the list of available languages, then please use the provided filter. Here's an example (mu)plugin:

    <?php
    /* Plugin Name: (WCM) Add additional languages */
    add_filter( 'uls_get_langs', 'wcm_add_languages' );
    function wcm_add_languages( $languages )
    {
    	return array_merge( $languages, array(
    		 'de_DE' // German
    		,'es_ES' // Spanish
    		,'ja'    // Japanese
    	) );
    }