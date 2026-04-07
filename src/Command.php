<?php

namespace WP_CLI\Doctor;

use Exception;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use WP_CLI;
use WP_CLI\Formatter;
use WP_CLI\Utils;

/**
 * Diagnose what ails WordPress.
 *
 * ## EXAMPLES
 *
 *     # Verify WordPress core is up to date.
 *     $ wp doctor check core-update
 *     +-------------+---------+-----------------------------------------------------------+
 *     | name        | status  | message                                                   |
 *     +-------------+---------+-----------------------------------------------------------+
 *     | core-update | warning | A new major version of WordPress is available for update. |
 *     +-------------+---------+-----------------------------------------------------------+
 *
 *     # List checks to run.
 *     $ wp doctor list
 *     +----------------------------+--------------------------------------------------------------------------------+
 *     | name                       | description                                                                    |
 *     +----------------------------+--------------------------------------------------------------------------------+
 *     | autoload-options-size      | Warns when autoloaded options size exceeds threshold of 900 kb.                |
 *     | constant-savequeries-falsy | Confirms expected state of the SAVEQUERIES constant.                           |
 *     | constant-wp-debug-falsy    | Confirms expected state of the WP_DEBUG constant.                              |
 *     | core-update                | Errors when new WordPress minor release is available; warns for major release. |
 *
 * @package wp-cli
 */
class Command {

	/**
	 * Number of files to scan before showing progress in debug mode.
	 */
	const DEBUG_FILE_SCAN_INTERVAL = 1000;

	/**
	 * Run a series of checks against WordPress to diagnose issues.
	 *
	 * A check is a routine run against some scope of WordPress that reports
	 * a 'status' and a 'message'. The status can be 'success', 'warning', or
	 * 'error'. The message is a human-readable explanation of the status. If
	 * any of the checks fail, then the command will exit with the code `1`.
	 *
	 * ## OPTIONS
	 *
	 * [<checks>...]
	 * : Names of one or more checks to run.
	 *
	 * [--all]
	 * : Run all registered checks.
	 *
	 * [--spotlight]
	 * : Focus on warnings and errors; ignore any successful checks.
	 *
	 * [--config=<file>]
	 * : Use checks registered in a specific configuration file.
	 *
	 * [--fields=<fields>]
	 * : Limit the output to specific fields.
	 *
	 * [--<field>=<value>]
	 * : Filter results by key=value pairs.
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
	 *   - count
	 * ---
	 *
	 * ## AVAILABLE FIELDS
	 *
	 * These fields will be displayed by default for each check:
	 *
	 * * name
	 * * status
	 * * message
	 *
	 * ## EXAMPLES
	 *
	 *     # Verify WordPress core is up to date.
	 *     $ wp doctor check core-update
	 *     +-------------+---------+-----------------------------------------------------------+
	 *     | name        | status  | message                                                   |
	 *     +-------------+---------+-----------------------------------------------------------+
	 *     | core-update | warning | A new major version of WordPress is available for update. |
	 *     +-------------+---------+-----------------------------------------------------------+
	 *
	 *     # Verify the site is public as expected.
	 *     $ wp doctor check option-blog-public
	 *     +--------------------+--------+--------------------------------------------+
	 *     | name               | status | message                                    |
	 *     +--------------------+--------+--------------------------------------------+
	 *     | option-blog-public | error  | Site is private but expected to be public. |
	 *     +--------------------+--------+--------------------------------------------+
	 *
	 * @when before_wp_load
	 *
	 * @param array<string>        $args       Names of one or more checks to run.
	 * @param array<string, bool|string> $assoc_args Command options.
	 * @return void
	 */
	public function check( $args, $assoc_args ) {

		$config = (string) Utils\get_flag_value( $assoc_args, 'config', self::get_default_config() );
		Checks::register_config( $config );

		$all = Utils\get_flag_value( $assoc_args, 'all' );
		if ( empty( $args ) && ! $all ) {
			WP_CLI::error( 'Please specify one or more checks, or use --all.' );
		}

		$completed = array();
		$checks    = Checks::get_checks( array( 'name' => $args ) );
		if ( empty( $checks ) ) {
			if ( $args ) {
				WP_CLI::error( count( $args ) > 1 ? 'Invalid checks.' : 'Invalid check.' );
			} else {
				WP_CLI::error( 'No checks registered.' );
			}
		}
		$file_checks = array();
		$progress    = false;
		if ( $all && 'table' === $assoc_args['format'] ) {
			$progress = Utils\make_progress_bar( 'Running checks', count( $checks ) );
		}
		foreach ( $checks as $name => $check ) {
			$when = $check->get_when();
			if ( $when ) {
				WP_CLI::add_hook(
					$when,
					static function () use ( $name, $check, &$completed, &$progress ) {
						WP_CLI::debug( "Running check: {$name}", 'doctor' );
						$check->run();
						$completed[ $name ] = $check;
						if ( $progress ) {
							$progress->tick();
						}
						$results = $check->get_results();
						WP_CLI::debug( "  Status: {$results['status']}", 'doctor' );
					}
				);
			} else {
				$file_check = 'WP_CLI\Doctor\Check\File';
				if ( is_a( $check, $file_check ) || is_subclass_of( $check, $file_check ) ) {
					$file_checks[ $name ] = $check;
				}
			}
		}
		if ( ! empty( $file_checks ) ) {
			WP_CLI::add_hook(
				'after_wp_config_load',
				static function () use ( $file_checks, &$completed, &$progress ) {
					WP_CLI::debug( 'Scanning filesystem for file checks...', 'doctor' );
					try {
						$directory      = new RecursiveDirectoryIterator( ABSPATH, RecursiveDirectoryIterator::SKIP_DOTS );
						$iterator       = new RecursiveIteratorIterator( $directory, RecursiveIteratorIterator::CHILD_FIRST );
						$wp_content_dir = defined( 'WP_CONTENT_DIR' ) ? WP_CONTENT_DIR : ABSPATH . 'wp-content';
						$item_count     = 0;
						foreach ( $iterator as $file ) {
							if ( ! $file instanceof \SplFileInfo ) {
								continue;
							}
							++$item_count;
							if ( 0 === $item_count % self::DEBUG_FILE_SCAN_INTERVAL ) {
								WP_CLI::debug( "  Visited {$item_count} items...", 'doctor' );
							}
							foreach ( $file_checks as $name => $check ) {
								$options = $check->get_options();
								if ( ! empty( $options['only_wp_content'] )
								&& 0 !== stripos( $file->getPath(), $wp_content_dir ) ) {
									continue;
								}
								if ( ! empty( $options['path'] )
								&& 0 !== stripos( $file->getPathname(), ABSPATH . $options['path'] ) ) {
									continue;
								}
								$ext_option = isset( $options['extension'] ) && is_string( $options['extension'] ) ? $options['extension'] : '';
								$extension  = explode( '|', $ext_option );
								if ( ! in_array( $file->getExtension(), $extension, true ) ) {
									continue;
								}
								/** @var Check\File $check */
								$check->check_file( $file );
							}
						}
						WP_CLI::debug( "  Total items visited: {$item_count}", 'doctor' );
					} catch ( Exception $e ) {
						WP_CLI::warning( $e->getMessage() );
					}
					foreach ( $file_checks as $name => $check ) {
						WP_CLI::debug( "Running check: {$name}", 'doctor' );
						$check->run();
						$completed[ $name ] = $check;
						if ( $progress ) {
							$progress->tick();
						}
						$results = $check->get_results();
						WP_CLI::debug( "  Status: {$results['status']}", 'doctor' );
					}
				}
			);
		}

		if ( ! isset( WP_CLI::get_runner()->config['url'] ) ) {
			WP_CLI::add_wp_hook(
				'muplugins_loaded',
				static function () {
					WP_CLI::set_url( home_url( '/' ) );
				}
			);
		}

		try {
			$this->load_wordpress_with_template();
		} catch ( Exception $e ) {
			WP_CLI::warning( $e->getMessage() );
		}

		$results = array();
		foreach ( $completed as $name => $check ) {
			$results[] = array_merge( $check->get_results(), array( 'name' => $name ) );
		}

		if ( $progress ) {
			$progress->finish();
		}

		// @todo warn if a check provides invalid status

		if ( Utils\get_flag_value( $assoc_args, 'spotlight' ) ) {
			$check_count = count( $results );
			$results     = array_filter(
				$results,
				function ( $check ) {
					return in_array( $check['status'], array( 'warning', 'error' ), true );
				}
			);
			if ( empty( $results ) && 'table' === $assoc_args['format'] ) {
				if ( 1 === $check_count ) {
					$message = "The check reports 'success'.";
				} else {
					$message = "All {$check_count} checks report 'success'.";
				}
				WP_CLI::success( $message );
				return;
			}
		}

		$results_with_error = array_filter(
			$results,
			function ( $check ) {
				return 'error' === $check['status'];
			}
		);
		$should_error       = ! empty( $results_with_error );
		if ( $should_error && 'table' === $assoc_args['format'] ) {
			$check_count   = count( $results_with_error );
			$error_message = 1 === $check_count ? "1 check reports 'error'." : sprintf( "%d checks report 'error'.", $check_count );
		} else {
			$error_message = null;
		}

		$default_fields = array( 'name', 'status', 'message' );

		foreach ( $results as $key => $item ) {
			foreach ( $default_fields as $field ) {
				if ( ! empty( $assoc_args[ $field ] ) && $item[ $field ] !== $assoc_args[ $field ] ) {
					unset( $results[ $key ] );
					break;
				}
			}
		}

		$formatter = new Formatter( $assoc_args, $default_fields );

		$formatter->display_items( $results );

		if ( $should_error ) {
			if ( $error_message ) {
				WP_CLI::error( $error_message );
			} else {
				WP_CLI::halt( 1 );
			}
		}
	}

	/**
	 * List all available checks to run.
	 *
	 * ## OPTIONS
	 *
	 * [--config=<file>]
	 * : Use checks registered in a specific configuration file.
	 *
	 * [--fields=<fields>]
	 * : Limit the output to specific fields.
	 *
	 * [--format=<format>]
	 * : Render output in a specific format.
	 * ---
	 * default: table
	 * options:
	 *   - table
	 *   - json
	 *   - csv
	 *   - count
	 * ---
	 *
	 * ## AVAILABLE FIELDS
	 *
	 * These fields will be displayed by default for each check:
	 *
	 * * name
	 * * description
	 *
	 * ## EXAMPLES
	 *
	 *     # List checks to run.
	 *     $ wp doctor list
	 *     +----------------------------+--------------------------------------------------------------------------------+
	 *     | name                       | description                                                                    |
	 *     +----------------------------+--------------------------------------------------------------------------------+
	 *     | autoload-options-size      | Warns when autoloaded options size exceeds threshold of 900 kb.                |
	 *     | constant-savequeries-falsy | Confirms expected state of the SAVEQUERIES constant.                           |
	 *     | constant-wp-debug-falsy    | Confirms expected state of the WP_DEBUG constant.                              |
	 *     | core-update                | Errors when new WordPress minor release is available; warns for major release. |
	 *
	 * @when before_wp_load
	 * @subcommand list
	 *
	 * @param array<string>        $args       Command arguments.
	 * @param array<string, bool|string> $assoc_args Command options.
	 * @return void
	 */
	public function list_( $args, $assoc_args ) {

		$assoc_args = array_merge(
			array(
				'fields' => 'name,description',
			),
			$assoc_args
		);

		$config = (string) Utils\get_flag_value( $assoc_args, 'config', self::get_default_config() );
		Checks::register_config( $config );

		$items = array();
		foreach ( Checks::get_checks() as $check_name => $class ) {
			$reflection  = new \ReflectionClass( $class );
			$doc_comment = $reflection->getDocComment();
			$description = $doc_comment ? self::remove_decorations( $doc_comment ) : '';
			$tokens      = array();
			foreach ( $reflection->getProperties() as $prop ) {
				$prop_name = $prop->getName();
				if ( '_' === $prop_name[0] ) {
					continue;
				}
				if ( PHP_VERSION_ID < 80100 ) {
					$prop->setAccessible( true );
				}
				$value = $prop->getValue( $class );
				if ( is_array( $value ) ) {
					$value = json_encode( $value );
				}
				if ( is_scalar( $value ) ) {
					$tokens[ '%' . $prop_name . '%' ] = (string) $value;
				} else {
					$tokens[ '%' . $prop_name . '%' ] = gettype( $value );
				}
			}
			if ( ! empty( $tokens ) ) {
				$description = str_replace( array_keys( $tokens ), array_values( $tokens ), $description );
			}

			$items[] = array(
				'name'        => $check_name,
				'description' => $description,
			);
		}
		Utils\format_items( (string) $assoc_args['format'], $items, explode( ',', (string) $assoc_args['fields'] ) );
	}

	/**
	 * Runs through the entirety of the WP bootstrap process
	 *
	 * @return void
	 */
	private function load_wordpress_with_template() {
		global $wp_query;

		WP_CLI::add_wp_hook(
			'wp_redirect',
			function ( $to ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter
				ob_start();
				debug_print_backtrace();
				$message = ob_get_clean();
				throw new Exception( 'Incomplete check execution. Some code is trying to do a URL redirect. Backtrace:' . PHP_EOL . $message );
			},
			1
		);

		WP_CLI::get_runner()->load_wordpress();

		// Set up the main WordPress query.
		wp();

		$interpreted = array();
		foreach ( $wp_query as $key => $value ) {
			if ( 0 === stripos( $key, 'is_' ) && $value ) {
				$interpreted[] = $key;
			}
		}
		WP_CLI::debug( 'Main WP_Query: ' . implode( ', ', $interpreted ), 'doctor' );

		// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedConstantFound -- WordPress Core constant.
		define( 'WP_USE_THEMES', true );

		add_filter(
			'template_include',
			static function ( $template ) {
				$display_template = str_replace( dirname( get_template_directory() ) . '/', '', $template );
				WP_CLI::debug( "Theme template: {$display_template}", 'doctor' );
				return $template;
			},
			999
		);

		// Template is normally loaded in global scope, so we need to replicate
		foreach ( $GLOBALS as $key => $value ) {
			global ${$key}; // phpcs:ignore PHPCompatibility.Variables.ForbiddenGlobalVariableVariable.NonBareVariableFound -- Syntax is updated to compatible with php 5 and 7.
		}

		// Load the theme template.
		ob_start();
		require_once ABSPATH . WPINC . '/template-loader.php';
		ob_get_clean();
	}

	/**
	 * Remove unused cruft from PHPdoc comment.
	 *
	 * @param string $comment PHPdoc comment.
	 * @return string
	 */
	private static function remove_decorations( $comment ) {
		$replaced = preg_replace( '|^/\*\*[\r\n]+|', '', $comment );
		$comment  = is_string( $replaced ) ? $replaced : '';

		$replaced = preg_replace( '|\n[\t ]*\*/$|', '', $comment );
		$comment  = is_string( $replaced ) ? $replaced : '';

		$replaced = preg_replace( '|^[\t ]*\* ?|m', '', $comment );
		$comment  = is_string( $replaced ) ? $replaced : '';

		return $comment;
	}

	/**
	 * Get the path to the default config file
	 *
	 * @return string
	 */
	private static function get_default_config() {
		return dirname( __DIR__ ) . '/doctor.yml';
	}
}
