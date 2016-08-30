<?php

namespace runcommand\Doctor;

use \WP_CLI\Utils;

class Checks {

	private static $instance;

	private $checks = array();

	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new Checks;
		}
		return self::$instance;
	}

	/**
	 * Register a check with the Doctor
	 *
	 * @param string $name Name for the check.
	 * @param string $class Check class name.
	 */
	public static function add_check( $name, $class ) {

		// @todo check name must be A-Za-z0-9-
		// @todo check class must subclass Doctor\Check;

		$check = new $class;
		self::$instance->checks[ $name ] = $check;
	}

	/**
	 * Get checks registred with the Doctor.
	 *
	 * @param array $args Filter checks based on some attribute.
	 */
	public static function get_checks( $args = array() ) {
		return self::$instance->checks;
	}

}
