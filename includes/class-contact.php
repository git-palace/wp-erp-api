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

	public function api_route_register() {
		register_rest_route( 'wp-erp-api', 'contact(?:/(?P<id>\d+))?', [
			'methods' 	=> 'GET',
        	'callback' 	=> [ $this, 'get_contact' ]
		] );
	}

	function get_contact( $request ) {
		$user = check_authentication();

		if ( empty( $request['id'] ) )
			wp_send_json_success( erp_get_peoples( $_GET ) );

		$contact = new WeDevs\ERP\CRM\Contact( $request['id'] );
		
		if ( $contact )
			wp_send_json_success( $contact->data );

		wp_send_json_success( [] );
	}
}