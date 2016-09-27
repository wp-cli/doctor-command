<?php

namespace runcommand\Doctor;

use WP_CLI;
use WP_CLI\Utils;

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
	 * Register checks from a config file
	 *
	 * @param string $file
	 */
	public static function register_config( $file ) {
		if ( ! is_file( $file ) ) {
			WP_CLI::error( 'Invalid configuration file.' );
		}
		$check_data = spyc_load_file( $file );
		foreach( $check_data as $check_name => $check_args ) {
			if ( empty( $check_args['class'] ) ) {
				continue;
			}
			$options = ! empty( $check_args['options'] ) ? $check_args['options'] : array();
			if ( ! class_exists( $check_args['class'] ) ) {
				WP_CLI::error( "Check class for '{$check_name}' doesn't exist. Verify check registration." );
			}
			$obj = new $check_args['class']( $options );
			self::add_check( $check_name, $obj );
		}
	}

	/**
	 * Register a check with the Doctor
	 *
	 * @param string $name Name for the check.
	 * @param string $class Check class name.
	 */
	public static function add_check( $name, $check ) {

		if ( ! preg_match( '#^[A-Za-z0-9-]+$#', $name ) ) {
			WP_CLI::error( "Check name '{$name}' is invalid. Verify check registration." );
		}

		// @todo check class must subclass Doctor\Check;

		if ( ! is_object( $check ) ) {
			if ( ! class_exists( $check ) ) {
				WP_CLI::error( "Check class for '{$name}' doesn't exist. Verify check registration." );
			}
			$check = new $check;
		}
		if ( ! is_subclass_of( $check, 'runcommand\Doctor\Checks\Check' ) ) {
			WP_CLI::error( "Check class for '{$name}' needs to extend Check base class. Verify check registration." );
		}
		self::$instance->checks[ $name ] = $check;
	}

	/**
	 * Get checks registred with the Doctor.
	 *
	 * @param array $args Filter checks based on some attribute.
	 */
	public static function get_checks( $args = array() ) {
		if ( ! empty( $args['name'] ) ) {
			$checks = array();
			$names = is_array( $args['name'] ) ? $args['name'] : array( $args['name'] );
			foreach( $names as $name ) {
				if ( isset( self::$instance->checks[ $name ] ) ) {
					$checks[ $name ] = self::$instance->checks[ $name ];
				}
			}
			return $checks;
		}
		return self::get_instance()->checks;
	}

}
