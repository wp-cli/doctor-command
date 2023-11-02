<?php

namespace WP_CLI\Doctor\Check;

use WP_CLI;

/**
 * Warns when there are plugin updates available.
 */
class PluginUpdate extends Plugin {

	public function run() {
		$plugins      = self::get_plugins();
		$update_count = 0;
		foreach ( $plugins as $plugin ) {
			if ( 'available' === $plugin['update'] ) {
				++$update_count;
			}
		}

		if ( 1 === $update_count ) {
			$this->set_status( 'warning' );
			$this->set_message( '1 plugin has an update available.' );
		} elseif ( $update_count ) {
			$this->set_status( 'warning' );
			$this->set_message( "{$update_count} plugins have updates available." );
		} else {
			$this->set_status( 'success' );
			$this->set_message( 'Plugins are up to date.' );
		}
	}
}
