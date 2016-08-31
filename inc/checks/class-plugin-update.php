<?php

namespace runcommand\Doctor\Checks;

use WP_CLI;

/**
 * Check whether WordPress plugins are up to date
 */
class Plugin_Update extends Check {

	public function run() {
		ob_start();
		WP_CLI::run_command( array( 'plugin', 'list' ), array( 'format' => 'json' ) );
		$ret = ob_get_clean();
		$plugins = ! empty( $ret ) ? json_decode( $ret, true ) : array();
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
