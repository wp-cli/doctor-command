<?php

namespace runcommand\Doctor;

use WP_CLI;
use WP_CLI\Utils;

class Checks {

	private static $instance;

	private $checks  = array();
	private $skipped = array();

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

		$check_data = \Mustangostang\Spyc::YAMLLoad( file_get_contents( $file ) );

		if ( ! empty( $check_data['_']['inherit'] ) ) {
			$inherited = $check_data['_']['inherit'];
			if ( 'default' === $inherited ) {
				$inherited = dirname( dirname( __FILE__ ) ) . '/doctor.yml';
			}
			$inherited = self::absolutize( $inherited, dirname( $file ) );
			if ( isset( $check_data['_']['skipped_checks'] ) ) {
				self::get_instance()->skipped_checks[ $inherited ] = $check_data['_']['skipped_checks'];
			}
			self::register_config( $inherited );
		}

		unset( $check_data['_'] );

		$skipped_checks = isset( self::get_instance()->skipped_checks[ $file ] ) ? self::get_instance()->skipped_checks[ $file ] : array();
		foreach ( $check_data as $check_name => $check_args ) {
			if ( ! empty( $check_args['require'] ) ) {
				$required_file = self::absolutize( $check_args['require'], dirname( $file ) );
				if ( ! file_exists( $required_file ) ) {
					$required_file = basename( $required_file );
					WP_CLI::error( "Required file '{$required_file}' doesn't exist (from '{$check_name}')." );
				}
				require_once $required_file;
			}

			if ( empty( $check_args['class'] ) && empty( $check_args['check'] ) ) {
				WP_CLI::error( "Check '{$check_name}' is missing 'class' or 'check'. Verify check registration." );
			}

			$class = ! empty( $check_args['check'] ) ? 'runcommand\Doctor\Checks\\' . $check_args['check'] : $check_args['class'];
			if ( ! class_exists( $class ) ) {
				WP_CLI::error( "Class '{$class}' for check '{$check_name}' doesn't exist. Verify check registration." );
			}
			if ( $skipped_checks && in_array( $check_name, $skipped_checks, true ) ) {
				continue;
			}
			$options = ! empty( $check_args['options'] ) ? $check_args['options'] : array();
			$obj     = new $class( $options );
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

		if ( ! is_object( $check ) ) {
			if ( ! class_exists( $check ) ) {
				WP_CLI::error( "Class '{$check}' for check '{$name}' doesn't exist. Verify check registration." );
			}
			$check = new $check;
		}
		if ( ! is_subclass_of( $check, 'runcommand\Doctor\Checks\Check' ) ) {
			$class = get_class( $check );
			WP_CLI::error( "Class '{$class}' for check '{$name}' needs to extend Check base class. Verify check registration." );
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
			$names  = is_array( $args['name'] ) ? $args['name'] : array( $args['name'] );
			foreach ( $names as $name ) {
				if ( isset( self::$instance->checks[ $name ] ) ) {
					$checks[ $name ] = self::$instance->checks[ $name ];
				}
			}
			return $checks;
		}
		return self::get_instance()->checks;
	}

	/**
	 * Make a path absolute.
	 *
	 * @param string $path Path to file.
	 * @param string $base Base path to prepend.
	 */
	private static function absolutize( $path, $base ) {
		if ( ! empty( $path ) && ! \WP_CLI\Utils\is_path_absolute( $path ) ) {
			$path = $base . DIRECTORY_SEPARATOR . $path;
		}
		return $path;
	}

}
