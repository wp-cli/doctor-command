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
	 * @var array
	 */
	protected $plugins = array();

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

	public function run() {
		if ( ! is_multisite() ) {
			$this->set_status( 'success' );
			$this->set_message( 'WordPress is not a multisite installation; required network plugin check skipped.' );
			return;
		}

		$plugins = array();
		ob_start();
		WP_CLI::run_command( array( 'plugin', 'list' ), array( 'format' => 'json' ) );
		$ret = ob_get_clean();
		if ( ! empty( $ret ) ) {
			$plugins = json_decode( $ret, true );
		}

		if ( ! is_array( $plugins ) ) {
			$this->set_status( 'error' );
			$this->set_message( 'Unable to parse plugin list output.' );
			return;
		}

		$plugin_statuses = array();
		foreach ( $plugins as $plugin ) {
			$plugin_statuses[ $plugin['name'] ] = $plugin['status'];
		}

		$missing            = array();
		$not_network_active = array();
		foreach ( $this->plugins as $plugin_name ) {
			if ( ! isset( $plugin_statuses[ $plugin_name ] ) ) {
				$missing[] = $plugin_name;
				continue;
			}
			if ( 'active-network' !== $plugin_statuses[ $plugin_name ] ) {
				$not_network_active[] = "{$plugin_name} ({$plugin_statuses[$plugin_name]})";
			}
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
}
