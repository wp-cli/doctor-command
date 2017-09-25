<?php

namespace runcommand\Doctor\Checks;

use WP_CLI;

/**
 * Warns when there are language updates available.
 */
class Language_Update extends Check {

	public function run() {
		ob_start();
		WP_CLI::run_command( array( 'language', 'core', 'list' ), array( 'format' => 'json' ) );
		$ret = ob_get_clean();
		$themes = ! empty( $ret ) ? json_decode( $ret, true ) : array();
		$update_count = 0;
		foreach ( $themes as $theme ) {
			if ( 'available' === $theme['update'] ) {
				$update_count++;
			}
		}

		if ( 1 === $update_count ) {
			$this->set_status( 'warning' );
			$this->set_message( '1 language has an update available.' );
		} else if ( $update_count ) {
			$this->set_status( 'warning' );
			$this->set_message( "{$update_count} languages have updates available." );
		} else {
			$this->set_status( 'success' );
			$this->set_message( 'Languages are up to date.' );
		}
	}
}
