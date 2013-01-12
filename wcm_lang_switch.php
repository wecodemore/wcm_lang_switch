<?php
! defined( 'ABSPATH' ) AND exit;
/*
Plugin Name:  User Language Switcher
Plugin URI:   http://example.com
Description:  Change the language per user, by the click of a button
Author:       Stephen Harris
Author URI:   http://example.com
Contributors: Franz Josef Kaiser
Version:      0.7.2
License:      GNU GPL 2
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

add_action( 'plugins_loaded', array( 'UserLangSelect', 'init' ) );

/**
 * Allows the user to change the systems language.
 * Saves the preference as user meta data.
 *
 * @since      0.1
 *
 * @author     Stephen Harris
 * @link       http://wordpress.stackexchange.com/questions/35622/change-language-by-clicking-a-button/57503
 *
 * @package    WordPress
 * @subpackage User Language Change
 */
class UserLangSelect
{
	/**
	 * Instance
	 * @access protected
	 * @var object
	 */
	static protected $instance;


	/**
	 * A unique name for this plug-in
	 * @since  0.1
	 * @var    string
	 */
	static public $name = 'uls_pick_lang';


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
	 * @return \UserLangSelect
	 */
	public function __construct()
	{
		if ( isset( $_REQUEST[ self :: $name ] ) )
			add_action( 'locale', array( $this, 'update_user' ) );

		add_filter( 'locale', 'wcm_get_user_locale', 20 );
		add_action( 'wp_before_admin_bar_render', array( $this, 'admin_bar') );
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
	 * @return string The human readable language name, e.g. 'English'
	*/
	public function format_code_lang( $code = '' )
	{
		$code = strtolower( substr( $code, 0, 2 ) );
		$lang_codes = array(
			'aa' => 'Afar', 'ab' => 'Abkhazian', 'af' => 'Afrikaans', 'ak' => 'Akan',
			'sq' => 'Albanian', 'am' => 'Amharic', 'ar' => 'Arabic', 'an' => 'Aragonese',
			'hy' => 'Armenian', 'as' => 'Assamese', 'av' => 'Avaric', 'ae' => 'Avestan',
			'ay' => 'Aymara', 'az' => 'Azerbaijani', 'ba' => 'Bashkir', 'bm' => 'Bambara',
			'eu' => 'Basque', 'be' => 'Belarusian', 'bn' => 'Bengali', 'bh' => 'Bihari',
			'bi' => 'Bislama', 'bs' => 'Bosnian', 'br' => 'Breton', 'bg' => 'Bulgarian',
			'my' => 'Burmese', 'ca' => 'Catalan; Valencian', 'ch' => 'Chamorro',
			'ce' => 'Chechen', 'zh' => 'Chinese',
			'cu' => 'Church Slavic; Old Slavonic; Church Slavonic; Old Bulgarian; Old Church Slavonic',
			'cv' => 'Chuvash', 'kw' => 'Cornish', 'co' => 'Corsican', 'cr' => 'Cree',
			'cs' => 'Czech', 'da' => 'Danish', 'dv' => 'Divehi; Dhivehi; Maldivian',
			'nl' => 'Dutch; Flemish', 'dz' => 'Dzongkha', 'en' => 'English', 'eo' => 'Esperanto',
			'et' => 'Estonian', 'ee' => 'Ewe', 'fo' => 'Faroese', 'fj' => 'Fijjian',
			'fi' => 'Finnish', 'fr' => 'French', 'fy' => 'Western Frisian', 'ff' => 'Fulah',
			'ka' => 'Georgian', 'de' => 'German', 'gd' => 'Gaelic; Scottish Gaelic',
			'ga' => 'Irish', 'gl' => 'Galician', 'gv' => 'Manx', 'el' => 'Greek, Modern',
			'gn' => 'Guarani', 'gu' => 'Gujarati', 'ht' => 'Haitian; Haitian Creole',
			'ha' => 'Hausa', 'he' => 'Hebrew', 'hz' => 'Herero', 'hi' => 'Hindi',
			'ho' => 'Hiri Motu', 'hu' => 'Hungarian', 'ig' => 'Igbo', 'is' => 'Icelandic',
			'io' => 'Ido', 'ii' => 'Sichuan Yi', 'iu' => 'Inuktitut', 'ie' => 'Interlingue',
			'ia' => 'Interlingua (International Auxiliary Language Association)',
			'id' => 'Indonesian', 'ik' => 'Inupiaq', 'it' => 'Italian', 'jv' => 'Javanese',
			'ja' => 'Japanese', 'kl' => 'Kalaallisut; Greenlandic', 'kn' => 'Kannada',
			'ks' => 'Kashmiri', 'kr' => 'Kanuri', 'kk' => 'Kazakh', 'km' => 'Central Khmer',
			'ki' => 'Kikuyu; Gikuyu', 'rw' => 'Kinyarwanda', 'ky' => 'Kirghiz; Kyrgyz',
			'kv' => 'Komi', 'kg' => 'Kongo', 'ko' => 'Korean', 'kj' => 'Kuanyama; Kwanyama',
			'ku' => 'Kurdish', 'lo' => 'Lao', 'la' => 'Latin', 'lv' => 'Latvian',
			'li' => 'Limburgan; Limburger; Limburgish', 'ln' => 'Lingala', 'lt' => 'Lithuanian',
			'lb' => 'Luxembourgish; Lëtzebuergesch', 'lu' => 'Luba-Katanga', 'lg' => 'Ganda',
			'mk' => 'Macedonian', 'mh' => 'Marshallese', 'ml' => 'Malayalam',
			'mi' => 'Maori', 'mr' => 'Marathi', 'ms' => 'Malay', 'mg' => 'Malagasy',
			'mt' => 'Maltese', 'mo' => 'Moldavian', 'mn' => 'Mongolian', 'na' => 'Nauru',
			'nv' => 'Navajo; Navaho', 'nr' => 'Ndebele, South; South Ndebele',
			'nd' => 'Ndebele, North; North Ndebele', 'ng' => 'Ndonga', 'ne' => 'Nepali',
			'nn' => 'Norwegian Nynorsk; Nynorsk, Norwegian',
			'nb' => 'Bokmål, Norwegian, Norwegian Bokmål',
			'no' => 'Norwegian', 'ny' => 'Chichewa; Chewa; Nyanja', 'oc' => 'Occitan, Provencal',
			'oj' => 'Ojibwa', 'or' => 'Oriya', 'om' => 'Oromo', 'os' => 'Ossetian; Ossetic',
			'pa' => 'Panjabi; Punjabi', 'fa' => 'Persian', 'pi' => 'Pali', 'pl' => 'Polish',
			'pt' => 'Portuguese', 'ps' => 'Pushto', 'qu' => 'Quechua', 'rm' => 'Romansh',
			'ro' => 'Romanian', 'rn' => 'Rundi', 'ru' => 'Russian', 'sg' => 'Sango',
			'sa' => 'Sanskrit', 'sr' => 'Serbian', 'hr' => 'Croatian',
			'si' => 'Sinhala; Sinhalese', 'sk' => 'Slovak', 'sl' => 'Slovenian',
			'se' => 'Northern Sami', 'sm' => 'Samoan', 'sn' => 'Shona', 'sd' => 'Sindhi',
			'so' => 'Somali', 'st' => 'Sotho, Southern', 'es' => 'Spanish; Castilian',
			'sc' => 'Sardinian', 'ss' => 'Swati', 'su' => 'Sundanese', 'sw' => 'Swahili',
			'sv' => 'Swedish', 'ty' => 'Tahitian', 'ta' => 'Tamil', 'tt' => 'Tatar',
			'te' => 'Telugu', 'tg' => 'Tajik', 'tl' => 'Tagalog', 'th' => 'Thai',
			'bo' => 'Tibetan', 'ti' => 'Tigrinya', 'to' => 'Tonga (Tonga Islands)',
			'tn' => 'Tswana', 'ts' => 'Tsonga', 'tk' => 'Turkmen', 'tr' => 'Turkish',
			'tw' => 'Twi', 'ug' => 'Uighur; Uyghur', 'uk' => 'Ukrainian', 'ur' => 'Urdu',
			'uz' => 'Uzbek', 've' => 'Venda', 'vi' => 'Vietnamese', 'vo' => 'Volapük',
			'cy' => 'Welsh','wa' => 'Walloon','wo' => 'Wolof', 'xh' => 'Xhosa',
			'yi' => 'Yiddish', 'yo' => 'Yoruba', 'za' => 'Zhuang; Chuang', 'zu' => 'Zulu'
		);
		$lang_codes = apply_filters( 'lang_codes', $lang_codes, $code );
		return strtr( $code, $lang_codes );
	}

} // END Class UserLangSelect
