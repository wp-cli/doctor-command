<?php

use \WP_CLI\Utils;

class Doctor {

	private static $instance;

	private $checks = array();

	/**
	 * Run a series of checks against WordPress to diagnose issues.
	 *
	 * A check is a routine run against some scope of WordPress that reports
	 * a 'status' and a 'message'. The status can be 'success', 'warning', or
	 * 'error'. The message should be a human-readable explanation of the
	 * status.
	 *
	 * <checks>...
	 * : One or more checks to run.
	 *
	 * @when before_wp_load
	 */
	public function diagnose( $args, $assoc_args ) {

		// @todo run all of the checks that don't require WP to be loaded

		// @todo load WordPress

		// @todo run all of the checks that require WP to be loaded

		// @todo display results; warn if a check provides invalid status

	}

	/**
	 * List available checks to run.
	 *
	 * [--format=<format>]
	 * : Render output in a specific format.
	 * ---
	 * default: table
	 * options:
	 *   - table
	 *   - json
	 *   - csv
	 * ---
	 *
	 * @when before_wp_load
	 */
	public function checks( $args, $assoc_args ) {

		$items = array();
		foreach( self::$instance->checks as $name => $class ) {
			$reflection = new ReflectionClass( $class );
			$items[] = array(
				'name'        => $name,
				'description' => self::remove_decorations( $reflection->getDocComment() ),
			);
		}
		Utils\format_items( $assoc_args['format'], $items, array( 'name', 'description' ) );
	}

	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new Doctor;
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
	 * Remove unused cruft from PHPdoc comment.
	 *
	 * @param string $comment PHPdoc comment.
	 * @return string
	 */
	private static function remove_decorations( $comment ) {
		$comment = preg_replace( '|^/\*\*[\r\n]+|', '', $comment );
		$comment = preg_replace( '|\n[\t ]*\*/$|', '', $comment );
		$comment = preg_replace( '|^[\t ]*\* ?|m', '', $comment );
		return $comment;
	}

}
