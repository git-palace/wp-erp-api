<?php
class WP_ERP_API_Handler {
	private static $instance = null;
	private $contact_api_handler = null;

	public static function get_instance() {
		if ( is_null( self::$instance ) )
			self::$instance = new WP_ERP_API_Handler();

		return self::$instance;
	}

	function __construct() {
		require_once 'class-contact.php';

		$this->contact_api_handler = Contact_API_Handler::get_instance();
	}
}