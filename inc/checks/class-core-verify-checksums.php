<?php

namespace runcommand\Doctor\Checks;

use WP_CLI;

/**
 * Check whether WordPress core verifies against its checksums.
 */
class Core_Verify_Checksums extends Check {

	protected $when = 'before_wp_load';

	public function run() {
		$ret = WP_CLI::launch_self( 'core verify-checksums', array(), array(), false, true );
		if ( 0 === $ret->return_code ) {
			$this->status = 'success';
			$this->message = 'WordPress verifies against its checksums.';
		} else {
			$this->status = 'error';
			$this->message = "WordPress doesn't verify against its checksums.";
		}
	}

}
