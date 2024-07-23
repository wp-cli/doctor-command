<?php

namespace WP_CLI\Doctor\Check;

use WP_CLI;
use WP_CLI\Doctor\Check;

/**
 * Errors if plugin '%name%' isn't in the expected state '%status%'.
 */
class Plugin_Status extends Plugin {

	/**
	 * Name of the plugin to check.
	 *
	 * @var string
	 */
	protected $name;

	/**
	 * Expected status for the plugin.
	 *
	 * * 'uninstalled' - Completely uninstalled from the system.
	 * * 'installed' = Present on the system. Could also be active or active-network.
	 * * 'active' = Present and activated on the system.
	 *
	 * @var string
	 */
	protected $status;

	public function __construct( $options = array() ) {
		$valid_statuses = array( 'uninstalled', 'installed', 'active' );
		if ( ! in_array( $options['status'], $valid_statuses, true ) ) {
			WP_CLI::error( 'Invalid plugin_status. Should be one of: ' . implode( ', ', $valid_statuses ) . '.' );
		}
		parent::__construct( $options );
	}

	public function run() {
		$plugins = self::get_plugins();

		$current_status = 'uninstalled';
		foreach ( self::get_plugins() as $plugin ) {
			if ( $plugin['name'] === $this->name ) {
				$current_status = $plugin['status'];
				break;
			}
		}

		$erred = false;
		if ( 'uninstalled' === $this->status
			&& $current_status !== $this->status ) {
			$erred = true;
		} elseif ( 'installed' === $this->status
			&& 'uninstalled' === $current_status ) {
			$erred = true;
		} elseif ( 'active' === $this->status
			&& in_array( $current_status, array( 'uninstalled', 'inactive' ), true ) ) {
			$erred = true;
		}

		if ( $erred ) {
			$this->set_status( 'error' );
			$this->set_message( "Plugin '{$this->name}' is '{$current_status}' but expected to be '{$this->status}'." );
		} else {
			$this->set_status( 'success' );
			$this->set_message( "Plugin '{$this->name}' is '{$current_status}' as expected." );
		}
	}
}
