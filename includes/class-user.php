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

		register_rest_route( 'wp-erp-api', 'staff(?:/(?P<id>\d+))?', [
			'methods' 	=> 'GET',
			'callback' 	=> [ $this, 'get_staff' ]
		] );

		register_rest_route( 'wp-erp-api', 'staff/(?P<id>\d+)', [
			'methods'	=> 'POST',
			'callback'	=> [ $this, 'post_staff' ]
		] );

		register_rest_route( 'wp-erp-api', 'add-staffs', [
			'methods'	=> 'POST',
			'callback'	=> [ $this, 'add_staffs' ]
		] );

		register_rest_route( 'wp-erp-api', 'agent(?:/(?P<id>\d+))?', [
			'methods' 	=> 'GET',
			'callback' 	=> [ $this, 'get_agent' ]
		] );

		register_rest_route( 'wp-erp-api', 'agent/(?P<id>\d+)', [
			'methods'	=> 'POST',
			'callback'	=> [ $this, 'post_agent' ]
		] );

		register_rest_route( 'wp-erp-api', 'add-agents', [
			'methods'	=> 'POST',
			'callback'	=> [ $this, 'add_agents' ]
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

	function post_staff( $request ) {
		$user = check_authentication();

		if ( !current_user_can( 'erp_hr_manager' ) || current_user_can( 'erp_crm_agent' ) ) {
			wp_send_json_error( 'You\'re not allowed this action.' );
		}

		$staff = new WeDevs\ERP\HRM\Employee( $request['id'] );
		$posted = json_decode( $request->get_body(), true );

		$result = $staff->create_employee( $posted );

		if ( is_wp_error( $result ) ) {
			wp_send_json_error( $result->get_error_message() );
		}

        if ( !$staff->is_employee() ) {
            wp_send_json_error( 'Could not create employee. Please try again.' );
        }

        wp_send_json_success( $staff->to_Array() );
	}

	function add_staffs( $request ) {
		$user = check_authentication();

		if ( !current_user_can( 'erp_hr_manager' ) || current_user_can( 'erp_crm_agent' ) ) {
			wp_send_json_error( 'You\'re not allowed this action.' );
		}

		$staffs = json_decode( $request->get_body(), true );

		foreach ( $staffs as $posted ) {
			$posted['is_staff_or_team_user'] = "on";
			
			$staff = new WeDevs\ERP\HRM\Employee();
			$result = $staff->create_employee( $posted );

			if ( is_wp_error( $result ) ) {
				wp_send_json_error( $result->get_error_message() );
			}

	        if ( !$staff->is_employee() ) {
	            wp_send_json_error( 'Could not create employee. Please try again.' );
	        }
		}

		wp_send_json_success( 'Added successfully.' );
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

	function post_agent( $request ) {
		$user = check_authentication();

		if ( !current_user_can( 'erp_hr_manager' ) || current_user_can( 'erp_crm_agent' ) ) {
			wp_send_json_error( 'You\'re not allowed this action.' );
		}

		$agent = new WeDevs\ERP\HRM\Employee( $request['id'] );
		$posted = json_decode( $request->get_body(), true );

		$result = $agent->create_employee( $posted );

		if ( is_wp_error( $result ) ) {
			wp_send_json_error( $result->get_error_message() );
		}

        if ( !$agent->is_employee() ) {
            wp_send_json_error( 'Could not create employee. Please try again.' );
        }

        wp_send_json_success( $agent->to_Array() );
	}

	function add_agents( $request ) {
		$user = check_authentication();

		if ( !current_user_can( 'erp_hr_manager' ) || current_user_can( 'erp_crm_agent' ) ) {
			wp_send_json_error( 'You\'re not allowed this action.' );
		}

		$agents = json_decode( $request->get_body(), true );

		foreach ( $agents as $posted ) {
			$posted['is_staff_or_team_user'] = "off";
			
			$agent = new WeDevs\ERP\HRM\Employee();
			$result = $agent->create_employee( $posted );

			if ( is_wp_error( $result ) ) {
				wp_send_json_error( $result->get_error_message() );
			}

	        if ( !$agent->is_employee() ) {
	            wp_send_json_error( 'Could not create employee. Please try again.' );
	        }
		}

		wp_send_json_success( 'Added successfully.' );
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