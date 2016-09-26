<?php

namespace runcommand\Doctor;

use WP_CLI;
use WP_CLI\Formatter;
use WP_CLI\Utils;

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
	 * [--fields=<fields>]
	 * : Display one or more fields.
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
		$checks = Checks::get_checks( array( 'name' => $args ) );
		foreach( $checks as $name => $check ) {
			WP_CLI::add_hook( $check->get_when(), function() use ( $name, $check, &$completed ) {
				$check->run();
				$completed[ $name ] = $check;
			});
		}

		if ( ! isset( WP_CLI::get_runner()->config['url'] ) ) {
			WP_CLI::add_wp_hook( 'muplugins_loaded', function(){
				WP_CLI::set_url( home_url( '/' ) );
			});
		}

		$this->load_wordpress_with_template();

		$results = array();
		foreach( $completed as $name => $check ) {
			$results[] = array_merge( $check->get_results(), array( 'name' => $name ) );
		}
		// @todo warn if a check provides invalid status

		$formatter = new Formatter( $assoc_args, array( 'name', 'status', 'message' ) );
		$formatter->display_items( $results );
	}

	/**
	 * List available checks to run.
	 *
	 * ## OPTIONS
	 *
	 * [--fields=<fields>]
	 * : Limit the output to specific fields. Defaults to all fields.
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
	 * @subcommand list
	 */
	public function list_( $args, $assoc_args ) {

		$assoc_args = array_merge( array(
			'fields'    => 'name,description',
		), $assoc_args );

		$items = array();
		foreach( Checks::get_checks() as $name => $class ) {
			$reflection = new \ReflectionClass( $class );
			$items[] = array(
				'name'        => $name,
				'description' => self::remove_decorations( $reflection->getDocComment() ),
			);
		}
		Utils\format_items( $assoc_args['format'], $items, explode( ',', $assoc_args['fields'] ) );
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
