<?php
defined( 'ABSPATH' ) OR exit;

/**
 * @package    WCM User Language Switcher
 * @subpackage Developer Tools
 * @author     Franz Josef Kaiser, Stephen Harris
 * @since      1.3
 */
class WCM_User_Lang_Switch_DevTools extends WCM_User_Lang_Switch
{
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
			$_GET['wcm_dev_tools'],
			"(WCM) ".str_replace( '_', ' ', $_GET['wcm_dev_tools'] ),
			array( $this, $_GET['wcm_dev_tools'] )
		);
	}

	public function compress_json()
	{
		printf(
			'<textarea rows="5" cols="104">%s</textarea>',
			json_encode( self :: $lang_codes )
		);
	}

	public function fetch_json()
	{
		if ( ! $response = $this->fetch_remote_json() )
			return;

		// Sum under lang ISO code
		$n = 0;
		for ( $i = 0; $i < count( $response ); $i++ )
		{
			0 === $i %4 AND $n++;
			$result[ $n ][] = $response[ $i ];
		}

		// Fetch native translation from local file...
		$native = file( plugin_dir_path( __FILE__ ).'/json/lang_native.json' );
		// ...convert to array
		$native = json_decode( implode( "", $native ), true );

		// Reduce (to speed up search task)
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
				$nn = ! $nn ? $string : ucwords( $native[ $nn ]['nativeName'] );
				// Build an array and get rid of white space
				// The used delimiters are: "," & ";"
				$int = array_map( 'trim', explode( ";", $string ) );

				# @TODO Not sure if we should do this, as Commas are separators
				# that should stay in the string. Needs checking.
				foreach( $int as $int_string )
				{
					strstr( $int_string, "," )
						AND $int = array_map( 'trim', explode( ",", $int_string ) );
				}
				$nat = array_map( 'trim', explode( ";", $nn ) );
				foreach( $nat as $nat_string )
				{
					strstr( $nat_string, "," )
						AND $nat = array_map( 'trim', explode( ",", $nat_string ) );
				}

				# @TODO Not sure if we should do this, as "languages" stands for "language groupes"
				// Fixing the cases where the second string has round brackets
				// This means that it's an ancient language and got a date attached.
				// Hopefully this assumption is true.
				foreach ( $int as $key => $int_string )
				{
					if ( strstr( $int_string, "(" ) )
					{
						if (
							0 !== $key
							AND ! strstr( $int[0], "(" )
							)
						{
							$int[0] = "{$int[0]} {$int_string}";
							unset( $int[ $key ] );
						}
					}
				}
				foreach ( $nat as $key => $nat_string )
				{
					if ( strstr( $nat_string, "(" ) )
					{
						if (
							0 !== $key
							AND ! strstr( $nat[0], "(" )
							)
						{
							$nat[0] = "{$nat[0]} {$nat_string}";
							unset( $nat[ $key ] );
						}
					}
				}
				sort( $int );
				sort( $nat );
				// Assign to output array and Uppercase letters for first chars
				$output[ $l ] = array(
					'int'    => array_map( 'ucwords', $int ),
					'native' => array_map( 'ucwords', $nat )
				);
			}
		}

		if ( empty( $output ) )
			return;

		# Output for a complete ISO 639-2 list
		/* printf(
			'<textarea rows="5" cols="104">%s</textarea>'
			,json_encode( $output )
		);*/

		// Only use the first string for WP UI
		foreach ( $output as $code => $out )
			foreach ( $out as $k => $o )
			{
				$output[ $code ][ $k ] = array_shift( $o );
			}

		printf ( '<p>%s</p>', 'Number of languages' );
		printf(
			'<input type="text" value="%s" />',
			count( $output )
		);
		printf ( '<p>%s</p>', 'Readable' );
		printf(
			'<textarea rows="5" cols="104">%s</textarea>',
			$this->beautify_json( $output )
		);
		printf ( '<p>%s</p>', 'Compressed' );
		printf(
			'<textarea rows="5" cols="104">%s</textarea>',
			json_encode( $output )
		);

		return;
		# Remove the above `return;` to get a DIFF of the changes
		$this->diff_json( $this->beautify_json( $output ) );
	}

	public function beautify_json( $json )
	{
		return str_replace(
			array( "{", ":{", "\":\"", "\"int\"", "\"native\"", "},\"", "}" ),
			array( "{\n\t", ":\n\t{", "\": \"", "\t\"int\"", "\n\t\t\"native\"", "},\n\t\"", "\n\t}" ),
			$json
		);
	}

	public function fetch_remote_json()
	{
		$response = wp_remote_request( 'http://loc.gov/standards/iso639-2/ISO-639-2_utf-8.txt' );
		if (
			is_wp_error( $response )
			OR 'OK' !== wp_remote_retrieve_response_message( $response )
			OR 200 !== wp_remote_retrieve_response_code( $response )
		)
			return false;

		$response = wp_remote_retrieve_body( $response );
		// Check (and in case remove) BOM for UTF-8
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

		return $response;
	}

	public function diff_json( $output_remote )
	{
		$output_current = file_get_contents( plugin_dir_path( __FILE__ ).'/json/lang_codes.json' );
		print wp_text_diff(
			var_export( $output_current, true ),
			var_export( $output_remote, true ),
			array(
				'title'       => 'Dev: Changes since last JSON file fetch',
				'title_left'  => 'Current data',
				'title_right' => 'New fetched data',
			)
		);
	}
}