<?php

namespace WP_CLI\Doctor\Check;

use WP_CLI;
use WP_CLI\Doctor\Check;

/**
 * Warns when there are plugin updates available.
 */
class Plugin_Update extends Plugin {

	public function run( $verbose ) {

		if ( $verbose ) {
			WP_CLI::log( "Checking for plugin updates..." );
		}

		$plugins      = self::get_plugins();
		$update_count = 0;
		foreach ( $plugins as $plugin ) {
			if ( 'available' === $plugin['update'] ) {
				++$update_count;
				if ( $verbose ) {
					WP_CLI::log( "- Update {$plugin['name']} {$plugin['version']} to {$plugin['update_version']}");
				}
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
