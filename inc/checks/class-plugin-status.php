<?php

namespace runcommand\Doctor\Checks;

use WP_CLI;

/**
 * Errors if plugin '%plugin_name%' isn't in the expected state '%plugin_status%'.
 */
class Plugin_Status extends Plugin {

	/**
	 * Name of the plugin to check.
	 *
	 * @var string
	 */
	protected $plugin_name;

	/**
	 * Expected status for the plugin.
	 *
	 * * 'uninstalled' - Completely uninstalled from the system.
	 * * 'installed' = Present on the system. Could also be active or active-network.
	 * * 'active' = Present and activated on the system.
	 *
	 * @var string
	 */
	protected $plugin_status;

	public function __construct( $options = array() ) {
		$valid_statuses = array( 'uninstalled', 'installed', 'active' );
		if ( ! in_array( $options['plugin_status'], $valid_statuses, true ) ) {
			WP_CLI::error( 'Invalid plugin_status. Should be one of: ' . implode( ', ', $valid_statuses ) . '.' );
		}
		parent::__construct( $options );
	}

	public function run() {
		$plugins = self::get_plugins();

		$current_status = 'uninstalled';
		foreach( self::get_plugins() as $plugin ) {
			if ( $plugin['name'] === $this->plugin_name ) {
				$current_status = $plugin['status'];
				break;
			}
		}

		$erred = false;
		if ( 'uninstalled' === $this->plugin_status
			&& $current_status !== $this->plugin_status ) {
			$erred = true;
		} else if ( 'installed' === $this->plugin_status
			&& 'uninstalled' === $current_status ) {
			$erred = true;
		} else if ( 'active' === $this->plugin_status
			&& in_array( $current_status, array( 'uninstalled', 'inactive' ), true ) ) {
			$erred = true;
		}

		if ( $erred ) {
			$this->status = 'error';
			$this->message = "Plugin '{$this->plugin_name}' is '{$current_status}' but expected to be '{$this->plugin_status}'.";
		} else {
			$this->status = 'success';
			$this->message = "Plugin '{$this->plugin_name}' is '{$current_status}' as expected.";
		}
	}

}
