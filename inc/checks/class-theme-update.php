<?php

namespace runcommand\Doctor\Checks;

use WP_CLI;

/**
 * Warns when there are theme updates available.
 */
class Theme_Update extends Check {

	public function run() {
		ob_start();
		WP_CLI::run_command( array( 'theme', 'list' ), array( 'format' => 'json' ) );
		$ret = ob_get_clean();
		$themes = ! empty( $ret ) ? json_decode( $ret, true ) : array();
		$update_count = 0;
		foreach( $themes as $theme ) {
			if ( 'available' === $theme['update'] ) {
				$update_count++;
			}
		}

		if ( 1 === $update_count ) {
			$this->status = 'warning';
			$this->message = "1 theme has an update available.";
		} else if ( $update_count ) {
			$this->status = 'warning';
			$this->message = "{$update_count} themes have updates available.";
		} else {
			$this->status = 'success';
			$this->message = 'Themes are up to date.';
		}

	}

}
