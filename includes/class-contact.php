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

		register_rest_route( 'wp-erp-api', 'contact/(?P<id>\d+)', [
			'methods'	=> 'POST',
			'callback'	=> [ $this, 'post_contact' ]
		] );

		register_rest_route( 'wp-erp-api', 'add-contacts', [
			'methods'	=> 'POST',
			'callback'	=> [ $this, 'add_contacts' ]
		] );
	}

	// /wp-erp-api/contact/:id for 1 contact
	// /wp-erp-api/contact for all contacts
	function get_contact( $request ) {
		$user = check_authentication();

		if ( empty( $request['id'] ) )
			wp_send_json_success( erp_get_peoples( $_GET ) );

		$contact = new WeDevs\ERP\CRM\Contact( $request['id'] );
		
		if ( $contact )
			wp_send_json_success( $contact->data );

		wp_send_json_success( [] );
	}

	// /wp-erp-api/contact/:id for updating 1 contact
	// keys : photo_id,first_name,last_name,email,phone,life_stage,contact_owner,date_of_birth,contact_age,mobile,website,fax,street_1,street_2,city,country,state,postal_code,source,other,notes,facebook,twitter,googleplus,linkedin,user_id,
	function post_contact( $request ) {
		$user = check_authentication();

		$contact = $_POST;

		$contact['id'] = $request['id'];
		$contact['type'] = 'contact';

		$people_id = erp_insert_people( $contact );

		if ( is_wp_error( $people_id ) ) {
			wp_send_json_error( 'Non contact with the id = ' . $request['id'] );
		}

		wp_send_json_success( 'Updated successfully.' );
	}

	// add contacts by posted data.
	// array of these keys : photo_id,first_name,last_name,email,phone,life_stage,contact_owner,date_of_birth,contact_age,mobile,website,fax,street_1,street_2,city,country,state,postal_code,source,other,notes,facebook,twitter,googleplus,linkedin,user_id,
	function add_contacts( $request ) {
		$user = check_authentication();

		$contacts = json_decode( $request->get_body(), true );

		foreach ( $contacts as $contact ) {
			$contact['type'] = 'contact';
			$people_id = erp_insert_people( $contact );

			if ( is_wp_error( $people_id ) ) {
				wp_send_json_error( 'Non contact with the id = ' . $request['id'] );
			}
		}

		wp_send_json_success( 'Added successfully.' );
	}
}