<?php

namespace runcommand\Doctor\Checks;

use WP_CLI;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use BrightNucleus\MimeTypes\MimeTypes;

/**
 * Warns when the extension of a file doesn't match the MIME type.
 */
class Validate_Mime extends Check {

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
			$file_path      = $file->getPathname();
			$file_extension = $file->getExtension();
			$file_mime_type = mime_content_type( $file_path );

			if ( 'directory' !== $file_mime_type ) {
				$mime_types = MimeTypes::getTypesForExtension( $file_extension );

				if ( is_array( $mime_types ) && ! in_array( $file_mime_type, $mime_types ) ) {
					$this->php_files_array[] = $file;
				}
			}
		}

		if ( ! empty( $this->php_files_array ) ) {
			$this->set_status( 'warning' );
			$this->set_message( 'Files detected with different MIME type.' );
			return;
		}

		$this->set_status( 'success' );
		$this->set_message( 'All files have valid MIMEs' );
	}
}
