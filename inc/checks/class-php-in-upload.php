<?php

namespace runcommand\Doctor\Checks;

use WP_CLI;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

/**
 * Errors when new WordPress minor release is available; warns for major release.
 */
class PHP_In_Upload extends Check {

	/**
	 * Array containing list of files found in the uploads folder
	 *
	 * @var array
	 */
	protected $php_files_array = array();

	public function run() {

		// Path to the uploads folder.
		$wp_content_dir = defined( 'WP_CONTENT_DIR' ) ? WP_CONTENT_DIR . '/uploads/' : ABSPATH . 'wp-content/uploads/';
		$directory = new RecursiveDirectoryIterator( $wp_content_dir, RecursiveDirectoryIterator::SKIP_DOTS );
		$iterator = new RecursiveIteratorIterator( $directory, RecursiveIteratorIterator::CHILD_FIRST );

		foreach ( $iterator as $file ) {
			if ( 'php' === $file->getExtension() ) {
				$this->php_files_array[] = $file;
			}
		}

		if ( ! empty( $this->php_files_array ) ) {
			$this->set_status( 'success' );
			$this->set_message( 'PHP files detected.' );
		} else {
			$this->set_status( 'success' );
			$this->set_message( 'No PHP files found.' );
		}
	}
}
