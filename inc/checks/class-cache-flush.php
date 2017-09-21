<?php

namespace runcommand\Doctor\Checks;

use WP_CLI;

/**
 * Detects the number of occurrences of the `wp_cache_flush()` function.
 */
class Cache_Flush extends Check {

	public function run() {
		$result = exec( 'grep -r "wp_cache_flush()" ' . WP_CONTENT_DIR . ' | wc -l' );

		$plural = ( '1' === $result ) ? '' : 's';

		if ( '0' === $result ) {
			$this->set_status( 'success' );
			$this->set_message( '0 occurrences of wp_cache_flush() detected.' );
		} else {
			$this->set_status( 'success' );
			$this->set_message( $result . ' occurrence' . $plural . ' of wp_cache_flush() detected.' );
		}
	}
}
