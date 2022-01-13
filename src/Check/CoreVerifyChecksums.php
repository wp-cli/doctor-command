<?php

namespace WP_CLI\Doctor\Check;

use WP_CLI;
use WP_CLI\Doctor\Check;

/**
 * Verifies WordPress files against published checksums; errors on failure.
 */
class CoreVerifyChecksums extends Check {

	public function __construct( $options = array() ) {
		parent::__construct( $options );
		$this->set_when( 'before_wp_load' );
	}

	public function run() {
		$return_code = WP_CLI::runcommand(
			'core verify-checksums',
			array(
				'exit_error' => false,
				'return'     => 'return_code',
			)
		);
		if ( 0 === $return_code ) {
			$this->set_status( 'success' );
			$this->set_message( 'WordPress verifies against its checksums.' );
		} else {
			$this->set_status( 'error' );
			$this->set_message( "WordPress doesn't verify against its checksums." );
		}
	}

}
