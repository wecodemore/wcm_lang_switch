<?php
defined( 'ABSPATH' ) OR exit;

class WCMUserLangSelectDevTools extends WCMUserLangSelect
{
	public function __construct()
	{
		if ( ! isset( $_GET['wcm_dev_tools'] ) )
			return;

		method_exists( $this, $_GET['wcm_dev_tools'] ) AND wp_add_dashboard_widget(
			 $_GET['wcm_dev_tools']
			,"(WCM) ".ucwords( str_replace( '_', ' ', $_GET['wcm_dev_tools'] ) )
			,array( $this, $_GET['wcm_dev_tools'] )
		);
	}

	public function compress_json()
	{
		printf(
			 '<textarea rows="5" cols="104">%s</textarea>'
			,self :: $json_data
		);
	}
}