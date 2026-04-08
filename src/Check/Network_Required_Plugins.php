<?php

namespace WP_CLI\Doctor\Check;

use WP_CLI;
use WP_CLI\Doctor\Check;

/**
 * Errors when required plugins are not network-activated.
 */
class Network_Required_Plugins extends Check {

	/**
	 * List of required plugin slugs.
	 *
	 * @var array<string>
	 */
	protected $plugins = array();

	/**
	 * @param array<string, mixed> $options
	 */
	public function __construct( $options = array() ) {
		parent::__construct( $options );

		if ( is_string( $this->plugins ) ) {
			$this->plugins = explode( ',', $this->plugins );
		}
		if ( ! is_array( $this->plugins ) ) {
			WP_CLI::error( 'Invalid plugins option. Provide an array or comma-delimited string.' );
		}
		$this->plugins = array_values( array_filter( array_map( 'trim', $this->plugins ) ) );
		if ( empty( $this->plugins ) ) {
			WP_CLI::error( 'At least one plugin slug is required.' );
		}
	}

	/**
	 * @return void
	 */
	public function run() {
		if ( ! is_multisite() ) {
			$this->set_status( 'success' );
			$this->set_message( 'WordPress is not a multisite installation; required network plugin check skipped.' );
			return;
		}

		if ( ! function_exists( 'get_plugins' ) || ! function_exists( 'is_plugin_active' ) || ! function_exists( 'is_plugin_active_for_network' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$installed_plugins = get_plugins();

		$missing            = array();
		$not_network_active = array();
		foreach ( $this->plugins as $plugin_slug ) {
			$plugin_file = $this->get_plugin_file( $plugin_slug, $installed_plugins );
			if ( null === $plugin_file ) {
				$missing[] = $plugin_slug;
				continue;
			}

			if ( is_plugin_active_for_network( $plugin_file ) ) {
				continue;
			}

			$status               = is_plugin_active( $plugin_file ) ? 'active' : 'inactive';
			$not_network_active[] = "{$plugin_slug} ({$status})";
		}

		if ( empty( $missing ) && empty( $not_network_active ) ) {
			$this->set_status( 'success' );
			$this->set_message( 'All required plugins are network-activated.' );
			return;
		}

		$parts = array();
		if ( ! empty( $missing ) ) {
			$parts[] = 'Missing plugins: ' . implode( ', ', $missing ) . '.';
		}
		if ( ! empty( $not_network_active ) ) {
			$parts[] = 'Not network-activated: ' . implode( ', ', $not_network_active ) . '.';
		}
		$this->set_status( 'error' );
		$this->set_message( 'Required network plugin check failed. ' . implode( ' ', $parts ) );
	}

	/**
	 * @param string               $plugin_slug Requested plugin slug/file.
	 * @param array<string, mixed> $installed_plugins Installed plugins keyed by plugin file.
	 * @return string|null
	 */
	private function get_plugin_file( $plugin_slug, $installed_plugins ) {
		if ( isset( $installed_plugins[ $plugin_slug ] ) ) {
			return $plugin_slug;
		}

		foreach ( array_keys( $installed_plugins ) as $plugin_file ) {
			$directory_slug = dirname( $plugin_file );
			if ( '.' !== $directory_slug && $directory_slug === $plugin_slug ) {
				return $plugin_file;
			}

			$file_slug = basename( $plugin_file, '.php' );
			if ( $file_slug === $plugin_slug ) {
				return $plugin_file;
			}
		}

		return null;
	}
}
