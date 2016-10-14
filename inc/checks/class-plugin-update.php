<?php

namespace runcommand\Doctor\Checks;

use WP_CLI;

/**
 * Warns when there are plugin updates available.
 */
class Plugin_Update extends Plugin {

	public function run() {
		$plugins = self::get_plugins();
		$update_count = 0;
		foreach( $plugins as $plugin ) {
			if ( 'available' === $plugin['update'] ) {
				$update_count++;
			}
		}

		if ( 1 === $update_count ) {
			$this->status = 'warning';
			$this->message = "1 plugin has an update available.";
		} else if ( $update_count ) {
			$this->status = 'warning';
			$this->message = "{$update_count} plugins have updates available.";
		} else {
			$this->status = 'success';
			$this->message = 'Plugins are up to date.';
		}

	}

}
