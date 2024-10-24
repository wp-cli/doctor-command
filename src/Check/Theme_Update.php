<?php

namespace WP_CLI\Doctor\Check;

use WP_CLI;
use WP_CLI\Doctor\Check;

/**
 * Warns when there are theme updates available.
 */
class Theme_Update extends Check {

	public function run( $verbose ) {

		if ( $verbose ) {
			WP_CLI::log( "Checking for theme updates..." );
		}

		ob_start();
		WP_CLI::run_command( array( 'theme', 'list' ), array( 'format' => 'json' ) );
		$ret          = ob_get_clean();
		$themes       = ! empty( $ret ) ? json_decode( $ret, true ) : array();
		$update_count = 0;
		foreach ( $themes as $theme ) {
			if ( 'available' === $theme['update'] ) {
				++$update_count;
				if ( $verbose ) {
					WP_CLI::log( "- Update {$theme['name']} {$theme['version']} to {$theme['update_version']}");
				}
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
