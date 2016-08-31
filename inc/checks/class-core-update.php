<?php

namespace runcommand\Doctor\Checks;

use WP_CLI;

/**
 * Check whether WordPress core is up to date.
 */
class Core_Update extends Check {

	public static $when = 'after_wp_load';

	public function run() {
		ob_start();
		WP_CLI::run_command( array( 'core', 'check-update' ), array( 'format' => 'json' ) );
		$ret = ob_get_clean();
		$updates = ! empty( $ret ) ? json_decode( $ret, true ) : array();
		$has_minor = $has_major = false;
		foreach( $updates as $update ) {
			switch ( $update['update_type'] ) {
				case 'minor':
					$has_minor = true;
					break;
				case 'major':
					$has_major = true;
					break;
			}
		}

		if ( $has_minor ) {
			$this->status = 'error';
			$this->message = "Updating to WordPress' newest minor version is strongly recommended.";
		} else if ( $has_major ) {
			$this->status = 'warning';
			$this->message = 'A new major version of WordPress is available for update.';
		} else {
			$this->status = 'success';
			$this->message = 'WordPress is at the latest version.';
		}

	}

}
