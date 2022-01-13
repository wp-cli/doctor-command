<?php

namespace WP_CLI\Doctor\Check;

use WP_CLI;
use WP_CLI\Doctor\Check;

/**
 * Warns when there are language updates available.
 */
class LanguageUpdate extends Check {

	public function run() {

		// Runs the `wp language core list --format=json` command and returns the output in JSON format.
		$languages = WP_CLI::runcommand(
			'language core list --format=json',
			array(
				'return' => true,
				'parse'  => 'json',
				'launch' => false,
			)
		);

		// Returns the count of each value that the key 'update' is mapped to.
		$counts = array_count_values( array_column( $languages, 'update' ) );

		// Returns the count of 'update' of type 'available'.
		$update_count = array_key_exists( 'available', $counts ) ? $counts['available'] : 0;

		// If there are no updates available.
		if ( ! $update_count ) {
			$this->set_status( 'success' );
			$this->set_message( 'Languages are up to date.' );
			return;
		}

		// Singular/Plural message depending on $update_count.
		$message = ( 1 === $update_count )
		? '1 language has an update available.'
		: "{$update_count} languages have updates available.";

		$this->set_status( 'warning' );
		$this->set_message( $message );
	}
}
