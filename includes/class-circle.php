<?php

class Circle_API_Handler {
	private static $instance = null;

	static function get_instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new Circle_API_Handler();
		}

		return self::$instance;
	}

	function __construct() {
		add_action( 'rest_api_init', [$this, 'api_route_register'] );
	}

	function api_route_register() {
		register_rest_route( 'wp-erp-api', 'circle(?:/(?P<id>\d+))?', [
			'methods' 	=> 'GET',
			'callback' 	=> [ $this, 'get_circle' ]
		] );

		register_rest_route( 'wp-erp-api', 'circle/(?P<id>\d+)', [
			'methods'	=> 'POST',
			'callback'	=> [ $this, 'post_circle' ]
		] );

		register_rest_route( 'wp-erp-api', 'add-circles', [
			'methods'	=> 'POST',
			'callback'	=> [ $this, 'add_circles' ]
		] );
	}

	// /wp-erp-api/circle/:id for 1 subscribers in the circle
	// /wp-erp-api/circle for all circles
	function get_circle( $request ) {
		$user = check_authentication();

	    $args = $_REQUEST;

		if ( empty( $request['id'] ) ) {
			wp_send_json_success( erp_crm_get_contact_groups( $args ) );
		}

		// get subscribers
		$args['group_id'] = $request['id'];
		wp_send_json_success( erp_crm_get_subscriber_contact( $args, 'subscribe' ) );
	}

	// update circle: name, description, private
	function post_circle( $request ) {
		$user = check_authentication();

		if ( !isset( $request['id'] ) || empty( $request['id'] ) )
			wp_send_json_error( 'Circle ID is not provided.' );

		$circle = json_decode( $request->get_body(), true );

		$data = [
            'id'          => $request['id'],
            'name'        => $circle['name'],
            'description' => $circle['description'],
            'private'     => erp_validate_boolean( $circle['private'] ) ? 1 : null,
            'created_by'  => $user->ID
        ];

        erp_crm_save_contact_group( $data );

        wp_send_json_success( 'Contact group save successfully' );
	}

	// add circles: array of  name, description, private
	function add_circles( $request ) {
		$user = check_authentication();

		$circles = json_decode( $request->get_body(), true );

		foreach ( $circles as $circle ) {
			$circle['created_by'] = $user->ID;
			$circle['private'] = erp_validate_boolean( $circle['private'] ) ? 1 : null;
			
        	erp_crm_save_contact_group( $circle );
		}

		wp_send_json_success( 'Added successfully.' );
	}
}