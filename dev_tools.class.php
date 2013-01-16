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

		$n = 0;
		for ( $i = 0; $i < count( $response ); $i++ )
		{
			0 === $i %4 AND $n++;
			$result[ $n ][] = $response[ $i ];
		}

		foreach ( $result as $lang )
		{
			static $string = '';
			$string !== end( $lang ) AND $string = end( $lang );
			$lang = array_filter( $lang );
			unset( $lang[3] );
			foreach ( $lang as $l )
				$output[ $l ] = $string;
		}

		! empty( $output ) AND printf(
			'<textarea rows="5" cols="104">%s</textarea>'
			,json_encode( $output )
		);
	}
}