<?php
/**
* Plugin Name: WP ERP - API Endpoints
* Description: This is the API service to provide WP ERP data
* Version: 1.0
**/

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

if ( !is_plugin_active( 'wp-erp/wp-erp.php' ) )
    return;

function check_authentication() {

}




require_once 'includes/index.php';

function WEAH() {
	return WP_ERP_API_Handler::get_instance();
}

WEAH();