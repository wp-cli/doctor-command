<?php

namespace runcommand\Doctor\Checks;

use WP_CLI;

/**
 * Warns when there are plugin updates available.
 */
class Plugin_Update extends Plugin {

	public function run() {
		$plugins = self::get_plugins();
		$outdated_plugin_names = array();
		foreach( $plugins as $plugin ) {
			if ( 'available' === $plugin['update'] ) {
				$outdated_plugin_names[] = $plugin['name'];
			}
		}
		$update_count = count( $outdated_plugin_names );
		$outdated_plugin_names = implode( $outdated_plugin_names, ', ' );

		if ( 1 === $update_count ) {
			$this->set_status( 'warning' );
			$this->set_message( "1 plugin has an update available." );
			$this->set_recommendation( "Update the {$outdated_plugin_names} plugin." );
		} else if ( $update_count ) {
			$this->set_status( 'warning' );
			$this->set_message( "{$update_count} plugins have updates available." );
			$this->set_recommendation( "Update these plugins: {$outdated_plugin_names}" );
		} else {
			$this->set_status( 'success' );
			$this->set_message( 'Plugins are up to date.' );
		}

	}

}
