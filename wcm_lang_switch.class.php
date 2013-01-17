<?php
! defined( 'ABSPATH' ) AND exit;
/*
Plugin Name:  User Language Switcher
Plugin URI:   https://github.com/wecodemore/wcm_lang_switch
Description:  Change the language per user, by the click of a button
Author:       Stephen Harris
Author URI:   https://plus.google.com/b/109907580576615571040/109907580576615571040/posts
Contributors: Franz Josef Kaiser
Version:      1.3
License:      GNU GPL 3
*/



# PUBLIC API #
/**
 * A function returns with returns the user's selected locale, if stored.
 *
 * @since  0.1
 *
 * @param  bool $locale
 * @return mixed string/bool $locale
 */
function wcm_get_user_locale( $locale = false )
{
	if ( $new_locale = get_user_meta(
		 get_current_user_id()
		,'user_language'
		,true
	) )
		 return $new_locale;

    return $locale;
}

add_action( 'plugins_loaded', array( 'WCMUserLangSelect', 'init' ) );

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
class WCMUserLangSelect
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
	static public $name = 'uls_pick_lang';

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
	 * @return void
	 */
	static public function init()
	{
		is_null( self :: $instance ) AND self :: $instance = new self;
		return self :: $instance;
	}


	/**
	 * Sets the current user and defines the WPLANG constant
	 * @since  0.4
	 * @static
	 * @param  string $mofile
	 * @return string $mofile
	 */
	static public function set_locale( $mofile )
	{
		global $current_user, $locale;

		// No need to constantly fire this one
		remove_filter( current_filter(), __FUNCTION__ );

		! isset( $current_user ) AND $current_user = wp_get_current_user();

		if ( ! $meta = get_user_meta( $current_user->ID, 'user_language', true ) )
		{
			return $mofile;
		}

		! defined( 'WPLANG' ) AND define( 'WPLANG', $meta );

		// Use the global $locale instead of get_locale() to prevent endless loops
		$mofile = str_replace( $locale, WPLANG, $mofile );

		return $mofile;
	}


	/**
	 * Hook the functions
	 * @since  0.1
	 * @return \WCMUserLangSelect
	 */
	public function __construct()
	{
		if ( isset( $_REQUEST[ self :: $name ] ) )
			add_action( 'locale', array( $this, 'update_user' ) );

		add_filter( 'locale', 'wcm_get_user_locale', 20 );
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
			 get_current_user_id()
			,'user_language'
			,$_REQUEST[ self :: $name ]
		);

		return wcm_get_user_locale( $locale );
	}


	/**
	 * The 'drop down' for the admin bar
	 *
	 * Based on Thomas "toscho" Scholz answer on the following WPSE question by Stephen Harris:
	 * @link http://wordpress.stackexchange.com/questions/57606/obtain-a-list-of-available-translations/57609
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

		$wp_admin_bar->add_menu( array(
			 'id'    => 'user_lang_pick'
			,'title' => $this->format_code_lang( $locale )
			,'href'  => '#'
		) );

		foreach ( $this->get_langs() as $lang )
		{
			$name = $this->format_code_lang( $lang );
			$link = add_query_arg(
				 self :: $name
				,$lang
			);

			$locale == $lang AND $name = sprintf(
				 '<strong> %s </strong>'
				,$name
			);

			$wp_admin_bar->add_menu( array(
				 'parent' => 'user_lang_pick'
				,'id'     => "user_lang_pick_lang_{$lang}"
				,'title'  => $name
				,'href'   => $link
			) );
		}
	}


	/**
	 * Get Languages
	 * @since  0.3
	 * @return void
	 */
	public function get_langs()
	{
		return apply_filters( 'uls_get_langs', array_merge(
			 get_available_languages()
			,array( 'en_US' )
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
	 * @return string The human readable language name, e.g. 'English' or the input on Error.
	*/
	public function format_code_lang( $code = '' )
	{

		$label_code = strtok( strtolower( $code ), "_" );
		if ( is_null( self :: $lang_codes ) )
			self :: $lang_codes =  json_decode( reset( file( plugin_dir_path( __FILE__ ).'/json/lang_codes.min.json' ) ), true );

		if ( 0 !== json_last_error() )
			return $code;

		$lang_codes = apply_filters( 'lang_codes', self :: $lang_codes, $code );
		$user_locale = wcm_get_user_locale( 'en_US' );

		if ( ! isset( $lang_codes[ $label_code ] ) )
			return $code;
		if( $user_locale == $code )
			return $lang_codes[ $label_code ][ 'native' ];
		else
			return $lang_codes[ $label_code ][ 'int' ];
	}

	public function dev_tools()
	{
		if (
			! is_admin()
			OR ! current_user_can( 'manage_options' )
		)
			return;

		include_once plugin_dir_path( __FILE__ ).'/dev_tools.class.php';
		new WCMUserLangSelectDevTools();
	}
}
