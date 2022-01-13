<?php

namespace WP_CLI\Doctor\Check;

use WP_CLI;
use WP_CLI\Doctor\Check;

/**
 * Errors when new WordPress minor release is available; warns for major release.
 */
class CoreUpdate extends Check {

	public function run() {
		ob_start();
		WP_CLI::run_command( array( 'core', 'check-update' ), array( 'format' => 'json' ) );
		$ret       = ob_get_clean();
		$updates   = ! empty( $ret ) ? json_decode( $ret, true ) : array();
		$has_minor = false;
		$has_major = false;
		foreach ( $updates as $update ) {
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
			$this->set_status( 'error' );
			$this->set_message( "Updating to WordPress' newest minor version is strongly recommended." );
		} elseif ( $has_major ) {
			$this->set_status( 'warning' );
			$this->set_message( 'A new major version of WordPress is available for update.' );
		} else {
			$this->set_status( 'success' );
			$this->set_message( 'WordPress is at the latest version.' );
		}

	}

}
