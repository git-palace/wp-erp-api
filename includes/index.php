<?php
class WP_ERP_API_Handler {
	private static $instance = null;
	private $contact_api_handler = null;
	private $company_api_handler = null;

	public static function get_instance() {
		if ( is_null( self::$instance ) )
			self::$instance = new WP_ERP_API_Handler();

		return self::$instance;
	}

	function __construct() {
		require_once 'class-contact.php';
		require_once 'class-company.php';
		require_once 'class-circle.php';

		$this->contact_api_handler = Contact_API_Handler::get_instance();
		$this->company_api_handler = Company_API_Handler::get_instance();
		$this->circle_api_handler = Circle_API_Handler::get_instance();
	}
}