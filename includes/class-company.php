<?php
class Company_API_Handler {
	private static $instance = null;

	public static function get_instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new Company_API_Handler();
		}

		return self::$instance;
	}

	function __construct() {
		add_action( 'rest_api_init', [$this, 'api_route_register'] );
	}

	public function api_route_register() {
		register_rest_route( 'wp-erp-api', 'company(?:/(?P<id>\d+))?', [
			'methods' 	=> 'GET',
			'callback' 	=> [ $this, 'get_company' ]
		] );

		register_rest_route( 'wp-erp-api', 'company/(?P<id>\d+)', [
			'methods'	=> 'POST',
			'callback'	=> [ $this, 'post_company' ]
		] );

		register_rest_route( 'wp-erp-api', 'add-companies', [
			'methods'	=> 'POST',
			'callback'	=> [ $this, 'add_companies' ]
		] );
	}

	// /wp-erp-api/company/:id for 1 company
	// /wp-erp-api/company for all companies
	public function get_company( $request ) {
		$user = check_authentication();

		if ( empty( $request['id'] ) ) {
		    $args = $_GET;
		    $args['type'] = 'company';

			wp_send_json_success( erp_get_peoples( $args ) );
		}

		$company = new WeDevs\ERP\CRM\Contact( $request['id'] );

		if ( $company && in_array( 'company', $company->data->types ) ) {
			if ( $company->data->contact_owner == $user->ID )
				wp_send_json_success( $company->data );
			else
				wp_send_json_error( 'You\'re not owner of this company.' );
		}

		wp_send_json_success( [] );		
	}

	// /wp-erp-api/contact/:id for updating 1 contact
	// keys : first_name,last_name,company,email,phone,life_stage,date_of_birth,contact_age,mobile,website,fax,street_1,street_2,city,country,state,postal_code,source,other,notes,facebook,twitter,googleplus,linkedin,user_id,
	function post_company( $request ) {
		$user = check_authentication();

		$company = json_decode( $request->get_body(), true );

		$company['contact_owner'] = $user->ID;
		$company['id'] = $request['id'];
		$company['type'] = 'company';

		error_log( print_r( $company, true ) );

		$people_id = erp_insert_people( $company );

		if ( is_wp_error( $people_id ) ) {
			wp_send_json_error( 'Non company with the id = ' . $request['id'] );
		}

		wp_send_json_success( 'Updated successfully.' );
	}
}