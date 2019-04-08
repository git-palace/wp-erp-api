<?php
/**
* Plugin Name: WP ERP - API Endpoints
* Description: This is the API service to provide WP ERP data
* Version: 1.0
**/

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

if ( !is_plugin_active( 'wp-erp/wp-erp.php' ) )
    return;

if( !function_exists('apache_request_headers') ) {
    function apache_request_headers() {
        $arh = array();
        $rx_http = '/\AHTTP_/';
        foreach($_SERVER as $key => $val) {
            if( preg_match($rx_http, $key) ) {
                $arh_key = preg_replace($rx_http, '', $key);
                $rx_matches = array();
                // do some nasty string manipulations to restore the original letter case
                // this should work in most cases
                $rx_matches = explode('_', strtolower($arh_key));
                if( count($rx_matches) > 0 and strlen($arh_key) > 2 ) {
                        foreach($rx_matches as $ak_key => $ak_val) $rx_matches[$ak_key] = ucfirst($ak_val);
                        $arh_key = implode('-', $rx_matches);
                }
                $arh[$arh_key] = $val;
            }
        }
        if(isset($_SERVER['CONTENT_TYPE'])) $arh['Content-Type'] = $_SERVER['CONTENT_TYPE'];
        if(isset($_SERVER['CONTENT_LENGTH'])) $arh['Content-Length'] = $_SERVER['CONTENT_LENGTH'];
        return( $arh );
    }
}

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

    wp_set_current_user( $user->ID );

	return $user;
}




require_once 'includes/index.php';

function WEAH() {
	return WP_ERP_API_Handler::get_instance();
}

WEAH();