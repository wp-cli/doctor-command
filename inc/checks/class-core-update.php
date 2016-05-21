<?php

namespace Doctor\Checks;

use WP_CLI;

/**
 * Check whether WordPress core is up to date.
 */
class Core_Update extends Check {

	public static $require_wp_load = true;

	public function run() {
		ob_start();
		WP_CLI::run_command( array( 'core', 'check-update' ), array( 'format' => 'json' ) );
		$ret = ob_get_clean();
		$updates = json_decode( $ret, true );
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
			self::$status = 'error';
			self::$message = "Updating to WordPress' newest minor version is strongly recommended.";
		} else if ( $has_major ) {
			self::$status = 'warning';
			self::$message = 'A new major version of WordPress is available for update.';
		} else {
			self::$status = 'success';
			self::$message = 'WordPress is at the latest version.';
		}

	}

}
