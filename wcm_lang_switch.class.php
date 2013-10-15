<?php
defined( 'ABSPATH' ) OR exit;
/*
Plugin Name:  User Language Switcher
Plugin URI:   https://github.com/wecodemore/wcm_lang_switch
Description:  Change the language per user, by the click of a button
Author:       Stephen Harris
Author URI:   https://plus.google.com/b/109907580576615571040/109907580576615571040/posts
Contributors: Franz Josef Kaiser, wecodemore
Version:      1.7.5
License:      GNU GPL 3
*/


# PUBLIC API #
/**
 * A function returns with returns the user's selected locale, if stored.
 *
 * @since  0.1
 * @param  bool $locale
 * @return mixed string/bool $locale|$locale_new
 */
function wcm_get_user_lang( $locale = false )
{
	if ( $locale_new = get_user_meta(
		get_current_user_id(),
		'user_language',
		true
	) )
		 return $locale_new;

    return $locale;
}

add_action( 'plugins_loaded', array( 'WCM_User_Lang_Switch', 'init' ), 5 );
/**
 * Allows the user to change the systems language.
 * Saves the preference as user meta data.
 *
 * @since      0.1
 *
 * @author     Stephen Harris, Franz Josef Kaiser
 * @link       https://github.com/wecodemore/wcm_lang_switch
 *
 * @package    WordPress
 * @subpackage User Language Change
 */
class WCM_User_Lang_Switch
{
	/**
	 * Instance
	 * @static
	 * @access protected
	 * @var object
	 */
	static protected $instance;

	/**
	 * A unique name for this plug-in
	 * @since  0.1
	 * @static
	 * @var    string
	 */
	static public $name = 'wcm_user_lang';

	/**
	 * Array of language names (in English & native language), indexed by language code
	 * @since  1.3
	 * @static
	 * @var    string
	 */
	static public $lang_codes;

	/**
	 * @internal Enable Dev Tools?
	 * @since 1.3
	 * @var   bool
	 */
	public $dev = true;

	/**
	 * Creates a new static instance
	 * @since  0.2
	 * @static
	 * @return object|\WCM_User_Lang_Switch $instance
	 */
	static public function init()
	{
		null === self::$instance AND self::$instance = new self;
		return self::$instance;
	}

	/**
	 * Hook the functions
	 * @since  0.1
	 * @return \WCM_User_Lang_Switch
	 */
	public function __construct()
	{
		if ( isset( $_REQUEST[ self::$name ] ) )
			add_action( 'locale', array( $this, 'update_user' ) );

		add_filter( 'locale', 'wcm_get_user_lang', 20 );
		add_action( 'wp_before_admin_bar_render', array( $this, 'admin_bar') );

		$this->dev AND add_action( 'wp_dashboard_setup', array( $this, 'dev_tools' ), 99 );
	}

	/**
	 * Update the user's option just in time!
	 * @since  0.1
	 * @param  string $locale
	 * @return string $locale
	 */
	public function update_user( $locale )
	{
		// The filter runs only once
		remove_filter( current_filter(), array( $this, __FUNCTION__ ) );

		update_user_meta(
			get_current_user_id(),
			'user_language',
			$_REQUEST[ self::$name ]
		);

		return wcm_get_user_lang( $locale );
	}

	/**
	 * The 'drop down' for the admin bar
	 *
	 * Based on Thomas "toscho" Scholz answer on the following WP.SE question by Stephen Harris:
	 * @link http://goo.gl/6oqug
	 *
	 * @since   0.1
	 * @uses    get_available_language()
	 * @uses    format_code_lang()
	 * @wp-hook wp_before_admin_bar_render
	 *
	 * @return void
	 */
	public function admin_bar()
	{
		global $wp_admin_bar;

		$locale  = get_locale();

		$current = $this->format_code_lang( $locale );
		$wp_admin_bar->add_node( array(
			'id'    => 'wcm_user_lang_pick',
			'title' => $current,
			'href'  => '#',
			'meta'  => array(
				'title' => $current,
			),
		) );

		foreach ( $this->get_langs() as $lang )
		{
			$name = $this->format_code_lang( $lang );
			$link = add_query_arg(
				self::$name,
				$lang
			);

			/*$locale == $lang AND $name = sprintf(
				 '<strong> %s </strong>'
				,$name
			);*/

			// Don't add the current language as menu item
			if ( $lang === get_locale() )
				continue;

			$wp_admin_bar->add_node( array(
				'parent' => 'wcm_user_lang_pick',
				'id'     => "wcm_user_lang_pick-{$lang}",
				'title'  => $name,
				'href'   => $link,
				'meta'   => array(
					'title' => sprintf(
					 	"%s (%s)",
					    $this->format_code_lang( $lang, 'int' ),
					    $lang
					),
					'class' => 'wcm_user_lang_item',
				),
			) );
		}
	}

	/**
	 * Get Languages
	 * @since  0.3
	 * @return array
	 */
	public function get_langs()
	{
		return apply_filters( 'wcm_get_langs', array_merge(
			get_available_languages(),
			array( 'en_US' )
		) );
	}

	/**
	 * Converts language code into 'human readable' form.
	 *
	 * Is an exact copy of the function format_code_lang()
	 * Including wp-admin/includes/ms.php in non-ms sites displays message
	 * prompting user to update network sites, hence we've just duplicated the function.
	 *
	 * @since  0.2
	 * @link   http://codex.wordpress.org/Function_Reference/format_code_lang
     *
	 * @param  string $code Language code, e.g. en_US or en
	 * @param  string $part Which part to return: International or native name?
	 * @return string The human readable language name, e.g. 'English', or the input on Error.
	*/
	public function format_code_lang( $code = '', $part = 'native' )
	{
		$label_code = strtok( strtolower( $code ), "_" );
		if ( null === self::$lang_codes )
		{
			$iso_639_2 = file( plugin_dir_path( __FILE__ ).'/json/lang_codes.min.json' );
			self::$lang_codes = json_decode( reset( $iso_639_2 ), true );
		}

		# PHP >= 5.3.0 only...
		# if ( 0 !== json_last_error() )
		if ( ! empty( self::$lang_codes['error'] ) )
			return $code;

		$lang_codes = apply_filters( 'wcm_lang_codes', self::$lang_codes, $code );

		if ( ! isset( $lang_codes[ $label_code ] ) )
			return $code;

		return $lang_codes[ $label_code ][ $part ];
	}

	public function dev_tools()
	{
		if (
			! is_admin()
			OR ! current_user_can( 'manage_options' )
		)
			return;

		include_once plugin_dir_path( __FILE__ ).'/dev_tools.class.php';
		new WCM_User_Lang_Switch_DevTools();
	}
}
