<?php

namespace WP_CLI\Doctor\Check;

use WP_CLI;
use WP_CLI\Doctor\Check;

/**
 * Warns when there are theme updates available.
 */
class Theme_Update extends Check {

	/**
	 * @return void
	 */
	public function run() {
		ob_start();
		WP_CLI::run_command( array( 'theme', 'list' ), array( 'format' => 'json' ) );
		$ret    = ob_get_clean();
		$themes = ! empty( $ret ) ? json_decode( $ret, true ) : array();
		if ( ! is_array( $themes ) ) {
			$themes = array();
		}
		$update_count = 0;
		foreach ( $themes as $theme ) {
			if ( ! is_array( $theme ) || ! isset( $theme['update'] ) ) {
				continue;
			}
			if ( 'available' === $theme['update'] ) {
				++$update_count;
			}
		}

		if ( 1 === $update_count ) {
			$this->set_status( 'warning' );
			$this->set_message( '1 theme has an update available.' );
		} elseif ( $update_count ) {
			$this->set_status( 'warning' );
			$this->set_message( "{$update_count} themes have updates available." );
		} else {
			$this->set_status( 'success' );
			$this->set_message( 'Themes are up to date.' );
		}
	}
}
