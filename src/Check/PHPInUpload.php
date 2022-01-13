<?php

namespace WP_CLI\Doctor\Check;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use WP_CLI;
use WP_CLI\Doctor\Check;

/**
 * Warns when a PHP file is present in the Uploads folder.
 */
class PHPInUpload extends Check {

	/**
	 * Array containing list of files found in the uploads folder
	 *
	 * @var array
	 */
	protected $php_files_array = array();

	public function run() {

		// Path to the uploads folder.
		$wp_content_dir = wp_upload_dir();
		$directory      = new RecursiveDirectoryIterator( $wp_content_dir['basedir'], RecursiveDirectoryIterator::SKIP_DOTS );
		$iterator       = new RecursiveIteratorIterator( $directory, RecursiveIteratorIterator::CHILD_FIRST );

		foreach ( $iterator as $file ) {
			if ( 'php' === $file->getExtension() ) {
				$this->php_files_array[] = $file;
			}
		}

		if ( ! empty( $this->php_files_array ) ) {
			$this->set_status( 'warning' );
			$this->set_message( 'PHP files detected in the Uploads folder.' );
		} else {
			$this->set_status( 'success' );
			$this->set_message( 'No PHP files found in the Uploads folder.' );
		}
	}
}
