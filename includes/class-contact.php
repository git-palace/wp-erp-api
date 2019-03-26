<?php
class Contact_API_Handler {
	private static $instance = null;

	public static function get_instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new Contact_API_Handler();
		}

		return self::$instance;
	}

	function __construct() {
		add_action( 'rest_api_init', [$this, 'api_route_register'] );
	}

	private function api_route_register() {
		register_rest_route( 'wp-erp-api', 'contact(?:/(?P<id>\d+))?', [
			'methods' 	=> 'GET',
        	'callback' 	=> 'get_contact',
        	'args'		=> [ 'id' ]
		] );
	}

	private function get_contact( $id = null) {
		check_authentication();
	}
}