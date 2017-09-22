<?php

namespace runcommand\Doctor\Checks;

use WP_CLI;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

/**
 * Detects the number of occurrences of the `wp_cache_flush()` function.
 */
class Cache_Flush extends File_Contents {

	public function run() {
		$wp_content_dir = defined( 'WP_CONTENT_DIR' ) ? WP_CONTENT_DIR : ABSPATH . 'wp-content';
		$directory = new RecursiveDirectoryIterator( $wp_content_dir, RecursiveDirectoryIterator::SKIP_DOTS );
		$iterator = new RecursiveIteratorIterator( $directory, RecursiveIteratorIterator::CHILD_FIRST );

		$file_content = new File_Contents();
		$file_content->regex = 'wp_cache_flush()';

		foreach ( $iterator as $file ) {
			$file_content->check_file( $file );
		}

		if ( ! empty( $file_content->_matches ) ) {
			$this->set_status( 'success' );
			$this->set_message( 'Use of wp_cache_flush() detected.' );
		} else {
			$this->set_status( 'success' );
			$this->set_message( 'Use of wp_cache_flush() not found.' );
		}
	}
}
