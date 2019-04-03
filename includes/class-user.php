<?php

class User_API_Handler {
	private static $instance = null;

	static function get_instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new User_API_Handler();
		}

		return self::$instance;
	}

	function __construct() {
		add_action( 'rest_api_init', [$this, 'api_route_register'] );
	}

	function api_route_register() {
		register_rest_route( 'wp-erp-api', 'broker(?:/(?P<id>\d+))?', [
			'methods' 	=> 'GET',
			'callback' 	=> [ $this, 'get_broker' ]
		] );

		register_rest_route( 'wp-erp-api', 'agent(?:/(?P<id>\d+))?', [
			'methods' 	=> 'GET',
			'callback' 	=> [ $this, 'get_agent' ]
		] );

		register_rest_route( 'wp-erp-api', 'staff(?:/(?P<id>\d+))?', [
			'methods' 	=> 'GET',
			'callback' 	=> [ $this, 'get_staff' ]
		] );

		register_rest_route( 'wp-erp-api', 'team(?:/(?P<id>\d+))?', [
			'methods' 	=> 'GET',
			'callback' 	=> [ $this, 'get_team' ]
		] );
	}

	// get broker
	function get_broker( $request ) {
		$user = check_authentication();

		if ( !current_user_can( 'administrator' ) ) {
	        $owner_id = get_user_meta( get_current_user_id(), 'created_by', true );

	        if ( !user_can( $owner_id, 'administrator' ) )
	        	wp_send_json_success( [] );

			wp_send_json_success( ( new WeDevs\ERP\HRM\Employee( get_current_user_id() ) )->to_Array() );
		}

	    $args = $_REQUEST;

		if ( isset( $request['id'] ) || !empty( $request['id'] ) ) {
			$employee    = new WeDevs\ERP\HRM\Employee( $request['id'] );

			if ( ! $employee->is_employee() )
	            wp_send_json_error( 'Broker does not exists.' );
	        
	        $is_staff_or_team_user = get_user_meta( $request['id'], 'is_staff_or_team_user', true );
	        $owner_id = get_user_meta( $request['id'], 'created_by', true );

	        if ( $is_staff_or_team_user == "off" && user_can( $owner_id, 'administrator' ) )
				wp_send_json_success( $employee->to_Array() );
			else
				wp_send_json_error( 'Broker does not exists.' );
		}

		$employees = erp_hr_get_employees( $args );
		$employee_list = array();

		foreach ( $employees as $idx => $employee ) {
			$is_staff_or_team_user = get_user_meta( $employee->user_id, 'is_staff_or_team_user', true );
	        $owner_id = get_user_meta( $employee->user_id, 'created_by', true );

			if ( $is_staff_or_team_user == "off" && user_can( $owner_id, 'administrator' ) ) {
				array_push( $employee_list, ( new WeDevs\ERP\HRM\Employee( $employee->user_id ) )->to_Array() );
			}
		}

		wp_send_json_success( $employee_list );
	}

	// get staff
	function get_staff( $request ) {
		$user = check_authentication();

		if ( isset( $request['id'] ) || !empty( $request['id'] ) ) {
			$employee    = new WeDevs\ERP\HRM\Employee( $request['id'] );

			if ( ! $employee->is_employee() )
	            wp_send_json_error( 'Staff does not exists.' );
	        
	        $is_staff_or_team_user = get_user_meta( $request['id'], 'is_staff_or_team_user', true );
	        $owner_id = get_user_meta( $request['id'], 'created_by', true );

	        if ( $is_staff_or_team_user == "on" && $owner_id == get_current_user_id() )
				wp_send_json_success( $employee->to_Array() );
			else
				wp_send_json_error( 'Staff does not exists or you\'re not allowed to access this user.' );
		}

	    $args = $_REQUEST;

		$employees = erp_hr_get_employees( $args );
		$employee_list = array();

		foreach ( $employees as $idx => $employee ) {
			$is_staff_or_team_user = get_user_meta( $employee->user_id, 'is_staff_or_team_user', true );
	        $owner_id = get_user_meta( $employee->user_id, 'created_by', true );

			if ( $is_staff_or_team_user == "on" && $owner_id == get_current_user_id() ) {
				array_push( $employee_list, ( new WeDevs\ERP\HRM\Employee( $employee->user_id ) )->to_Array() );
			}
		}

		wp_send_json_success( $employee_list );
	}

	// get agent
	function get_agent( $request ) {
		$user = check_authentication();

		if ( !current_user_can( 'administrator') && current_user_can( 'erp_crm_agent' ) ) {
			$is_staff_or_team_user = get_user_meta( get_current_user_id(), 'is_staff_or_team_user', true );

			if ( $is_staff_or_team_user == "off" )
				wp_send_json_success( ( new WeDevs\ERP\HRM\Employee( get_current_user_id() ) )->to_Array() );

			wp_send_json_success( [] );
		}

		if ( isset( $request['id'] ) || !empty( $request['id'] ) ) {
			$employee    = new WeDevs\ERP\HRM\Employee( $request['id'] );

			if ( ! $employee->is_employee() )
	            wp_send_json_error( 'Agent does not exists.' );
	        
	        $is_staff_or_team_user = get_user_meta( $request['id'], 'is_staff_or_team_user', true );
	        $owner_id = get_user_meta( $request['id'], 'created_by', true );

	        if ( $is_staff_or_team_user == "off" && $owner_id == get_current_user_id() )
				wp_send_json_success( $employee->to_Array() );
			else
				wp_send_json_error( 'Agent does not exists or you\'re not allowed to access this user.' );
		}

	    $args = $_REQUEST;

		$employees = erp_hr_get_employees( $args );
		$employee_list = array();

		foreach ( $employees as $idx => $employee ) {
			$is_staff_or_team_user = get_user_meta( $employee->user_id, 'is_staff_or_team_user', true );
	        $owner_id = get_user_meta( $employee->user_id, 'created_by', true );

			if ( $is_staff_or_team_user == "off" && $owner_id == get_current_user_id() ) {
				array_push( $employee_list, ( new WeDevs\ERP\HRM\Employee( $employee->user_id ) )->to_Array() );
			}
		}

		wp_send_json_success( $employee_list );
	}

	// get team
	function get_team( $request ) {
		$user = check_authentication();

		if ( isset( $request['id'] ) || !empty( $request['id'] ) ) {
			$employee    = new WeDevs\ERP\HRM\Employee( $request['id'] );

			if ( ! $employee->is_employee() )
	            wp_send_json_error( 'Agent does not exists.' );
	        
	        $is_staff_or_team_user = get_user_meta( $request['id'], 'is_staff_or_team_user', true );
	        $owner_id = get_user_meta( $request['id'], 'created_by', true );

	        if ( $is_staff_or_team_user == "on" && $owner_id == get_current_user_id() )
				wp_send_json_success( $employee->to_Array() );
			else
				wp_send_json_error( 'Agent does not exists or you\'re not allowed to access this user.' );
		}

	    $args = $_REQUEST;

		$employees = erp_hr_get_employees( $args );
		$employee_list = array();

		foreach ( $employees as $idx => $employee ) {
			$is_staff_or_team_user = get_user_meta( $employee->user_id, 'is_staff_or_team_user', true );
	        $owner_id = get_user_meta( $employee->user_id, 'created_by', true );

			if ( $is_staff_or_team_user == "on" && $owner_id == get_current_user_id() ) {
				array_push( $employee_list, ( new WeDevs\ERP\HRM\Employee( $employee->user_id ) )->to_Array() );
			}
		}

		wp_send_json_success( $employee_list );
	}
}
?>