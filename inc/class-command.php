<?php

namespace runcommand\Doctor;

use WP_CLI;
use \WP_CLI\Utils;

/**
 * Diagnose what ails WordPress.
 */
class Command {

	/**
	 * Run a series of checks against WordPress to diagnose issues.
	 *
	 * ## OPTIONS
	 *
	 * A check is a routine run against some scope of WordPress that reports
	 * a 'status' and a 'message'. The status can be 'success', 'warning', or
	 * 'error'. The message should be a human-readable explanation of the
	 * status.
	 *
	 * [<checks>...]
	 * : Names of one or more checks to run.
	 *
	 * [--all]
	 * : Run all registered checks.
	 *
	 * [--format=<format>]
	 * : Render results in a particular format.
	 * ---
	 * default: table
	 * options:
	 *   - table
	 *   - json
	 *   - csv
	 *   - yaml
	 * ---
	 *
	 * ## EXAMPLES
	 *
	 *     $ wp doctor diagnose core-update
	 *     +-------------+---------+-----------------------------------------------------------+
	 *     | name        | status  | message                                                   |
	 *     +-------------+---------+-----------------------------------------------------------+
	 *     | core-update | warning | A new major version of WordPress is available for update. |
	 *     +-------------+---------+-----------------------------------------------------------+
	 *
	 * @when before_wp_load
	 */
	public function check( $args, $assoc_args ) {

		$all = ! Utils\get_flag_value( $assoc_args, 'all' );
		if ( empty( $args ) && $all ) {
			WP_CLI::error( 'Please specify one or more checks, or use --all.' );
		}

		$completed = array();
		$checks = Checks::get_checks();
		foreach( $checks as $name => $check ) {
			WP_CLI::add_hook( $check::$when, function() use ( $name, $check, &$completed ) {
				$check->run();
				$completed[ $name ] = $check;
			});
		}

		$this->load_wordpress_with_template();

		$results = array();
		foreach( $completed as $name => $check ) {
			$results[] = array_merge( $check->get_results(), array( 'name' => $name ) );
		}

		// @todo warn if a check provides invalid status
		Utils\format_items( $assoc_args['format'], $results, array( 'name', 'status', 'message' ) );
	}

	/**
	 * List available checks to run.
	 *
	 * ## OPTIONS
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
	 * ## EXAMPLES
	 *
	 *     $ wp doctor checks
	 *     +-------------+---------------------------------------------+
	 *     | name        | description                                 |
	 *     +-------------+---------------------------------------------+
	 *     | core-update | Check whether WordPress core is up to date. |
	 *     +-------------+---------------------------------------------+
	 *
	 * @when before_wp_load
	 */
	public function checks( $args, $assoc_args ) {

		$items = array();
		foreach( Checks::get_checks() as $name => $class ) {
			$reflection = new ReflectionClass( $class );
			$items[] = array(
				'name'        => $name,
				'description' => self::remove_decorations( $reflection->getDocComment() ),
			);
		}
		Utils\format_items( $assoc_args['format'], $items, array( 'name', 'description' ) );
	}

	/**
	 * Runs through the entirety of the WP bootstrap process
	 */
	private function load_wordpress_with_template() {
		global $wp_query;

		WP_CLI::get_runner()->load_wordpress();

		// Set up the main WordPress query.
		wp();

		$interpreted = array();
		foreach( $wp_query as $key => $value ) {
			if ( 0 === stripos( $key, 'is_' ) && $value ) {
				$interpreted[] = $key;
			}
		}
		WP_CLI::debug( 'Main WP_Query: ' . implode( ', ', $interpreted ), 'doctor' );

		define( 'WP_USE_THEMES', true );

		add_filter( 'template_include', function( $template ) {
			$display_template = str_replace( dirname( get_template_directory() ) . '/', '', $template );
			WP_CLI::debug( "Theme template: {$display_template}", 'doctor' );
			return $template;
		}, 999 );

		// Template is normally loaded in global scope, so we need to replicate
		foreach( $GLOBALS as $key => $value ) {
			global $$key;
		}

		// Load the theme template.
		ob_start();
		require_once( ABSPATH . WPINC . '/template-loader.php' );
		ob_get_clean();
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
