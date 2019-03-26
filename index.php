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
	$headers = apache_request_headers();
    
    if ( !isset( $headers['Authorization'] ) || empty( $headers['Authorization'] ) )
    	wp_send_json_error( "Authentication is required." );

    $basic_auth = base64_decode( str_replace( 'Basic ', '', $headers['Authorization'] ) );

    $basic_auth = explode( ':', $basic_auth );

    if ( count( $basic_auth ) != 2)
    	wp_send_json_error( 'Authorization is invalid.' );

    $user = wp_signon( [
    	'user_login'	=> $basic_auth[0],
    	'user_password'	=> $basic_auth[1]
    ] );

    if ( is_wp_error( $user ) )
    	wp_send_json_error( 'Authorization is invalid' );

	return $user;
}




require_once 'includes/index.php';

function WEAH() {
	return WP_ERP_API_Handler::get_instance();
}

WEAH();