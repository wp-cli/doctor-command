<?php

namespace runcommand\Doctor\Checks;

use WP_CLI;

/**
 * Verifies WordPress files against published checksums; errors on failure.
 */
class Core_Verify_Checksums extends Check {

	public function __construct( $options = array() ) {
		parent::__construct( $options );
		$this->set_when( 'before_wp_load' );
	}

	public function run() {
		$ret = WP_CLI::launch_self( 'core verify-checksums', array(), array(), false, true );
		if ( 0 === $ret->return_code ) {
			$this->set_status( 'success' );
			$this->set_message( 'WordPress verifies against its checksums.' );
		} else {
			$this->set_status( 'error' );
			$this->set_message( "WordPress doesn't verify against its checksums." );
		}
	}

}
