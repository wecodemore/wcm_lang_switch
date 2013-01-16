<?php
defined( 'ABSPATH' ) OR exit;

/**
 * @package    WCM User Language Switcher
 * @subpackage Developer Tools
 * @author     Franz Josef Kaiser, Stephen Harris
 * @since      1.3
 */
class WCMUserLangSelectDevTools extends WCMUserLangSelect
{
	/**
	 *
	 */
	public function __construct()
	{
		if (
			! isset( $_GET['wcm_dev_tools'] )
			OR ( isset( $_GET['wcm_dev_tools'] ) AND ! method_exists( $this, $_GET['wcm_dev_tools'] ) )
			OR ( defined( 'DOING_AJAX' ) AND DOING_AJAX )
			OR ( defined( 'DOING_CRON' ) AND DOING_CRON )
		)
			return;

		wp_add_dashboard_widget(
			 $_GET['wcm_dev_tools']
			,"(WCM) ".str_replace( '_', ' ', $_GET['wcm_dev_tools'] )
			,array( $this, $_GET['wcm_dev_tools'] )
		);
	}

	/**
	 *
	 */
	public function compress_json()
	{
		printf(
			 '<textarea rows="5" cols="104">%s</textarea>'
			,self :: $json_data
		);
	}

	/**
	 *
	 */
	public function fetch_json()
	{
		$response = wp_remote_request( 'http://loc.gov/standards/iso639-2/ISO-639-2_utf-8.txt' );
		if (
			is_wp_error( $response )
			OR 'OK' !== wp_remote_retrieve_response_message( $response )
			OR 200 !== wp_remote_retrieve_response_code( $response )
		)
			return;

		$response = wp_remote_retrieve_body( $response );
		// Check (and in case remove) for UTF-8 *with* BOM
		// props <Gerjoo@gmail.com> @php.net
		if ( substr( $response, 0, 3 ) == pack( 'CCC', 239, 187, 191 ) )
			$response = substr( $response, 3 );
		// Build array
		$response = explode( "|", str_replace( "\n", "|", $response ) );
		// Get rid of french(/every 5th) strings
		foreach ( range( 4, count( $response ), 5 ) as $key )
			unset( $response[ $key ] );
		// Reindex array
		$response = array_merge( $response );

		// Sum under lang ISO code
		$n = 0;
		for ( $i = 0; $i < count( $response ); $i++ )
		{
			0 === $i %4 AND $n++;
			$result[ $n ][] = $response[ $i ];
		}

		// Fetch native translation from local file...
		$native = file( plugin_dir_path( __FILE__ ).'/lang_native.json' );
		// ...convert to array
		$native = json_decode( implode( "", $native ), true );
		// Reduce (to speed up search task)ative[ $code ]['nativeName'];
		$native_int = wp_list_pluck( $native, 'name' );

		foreach ( $result as $lang )
		{
			static $string = '';
			$string !== end( $lang ) AND $string = end( $lang );
			// Remove empty parts
			$lang = array_filter( $lang );
			// Remove full name
			unset( $lang[3] );
			// Build final output array
			foreach ( $lang as $l )
			{
				// Search in international list for a lang ISO code
				$nn = array_search( $string, $native_int );
				// If we found one, assign it to the array, else empty
				$nn = ! $nn ? '' : $native[ $nn ]['nativeName'];
				$output[ $l ] = array(
					 'int'    => $string
					,'native' => $nn
				);
			}
		}

		if ( empty( $output ) )
			return;

		printf ( '<p>%s</p>', 'Readable' );
		printf(
			'<textarea rows="5" cols="104">%s</textarea>'
			,str_replace(
				 array( "{", "}", "," )
				,array( "{\n\t", "\n}", "\n\t" )
				,json_encode( $output )
			)
		);
		printf ( '<p>%s</p>', 'Compressed' );
		printf(
			 '<textarea rows="5" cols="104">%s</textarea>'
			,json_encode( $output )
		);
	}
}