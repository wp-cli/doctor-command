<?php

namespace WP_CLI\Doctor\Check;

use WP_CLI;
use WP_CLI\Doctor\Check;
use SplFileInfo;

/**
 * Checks files on the filesystem for regex pattern `%regex%`.
 */
class File_Contents extends File {

	/**
	 * Regex pattern to check against each file’s contents.
	 *
	 * @var string
	 */
	protected $regex;

	/**
	 * Assert existence or absence of the regex pattern.
	 *
	 * Asserting existence of regex pattern requires match to be found. Asserting absense requires
	 * match to not be found.
	 *
	 * @var bool
	 */
	protected $exists = false;

	public function run( $verbose ) {

		if ( isset( $this->regex ) ) {

			if ( $verbose ) {
				WP_CLI::log( "Checking files on the filesystem for regex pattern {$this->regex}..." );
			}

			if ( ! empty( $this->_matches ) ) {
				//if matches are found
				if ( $this->exists ) {
					//$exists set to true so we should report true if something is found
					$this->set_status( 'success' );
					$count   = count( $this->_matches );
					$message = 1 === $count ? "1 '{$this->extension}' file" : "{$count} '{$this->extension}' files";
					$this->set_message( "{$message} passed check for '{$this->regex}'." );
				} else {
					//$exists is not set to true so we should report error if something is found
					$this->set_status( 'error' );
					$count   = count( $this->_matches );
					$message = 1 === $count ? "1 '{$this->extension}' file" : "{$count} '{$this->extension}' files";
					$this->set_message( "{$message} failed check for '{$this->regex}'." );
				}
			} elseif ( $this->exists ) {
				//$exists set to true so we should report error if regex is not found
				$this->set_status( 'error' );
				$this->set_message( "0 '{$this->extension}' files passed check for '{$this->regex}'." );
			} else {
				//$exists is not set to true so we should report success if regex is not found
				$this->set_status( 'success' );
				$this->set_message( "All '{$this->extension}' files passed check for '{$this->regex}'." );
			}
		}
	}

	public function check_file( SplFileInfo $file ) {
		if ( $file->isDir() || ! isset( $this->regex ) ) {
			return;
		}

		$contents = file_get_contents( $file->getPathname() );

		if ( preg_match( '#' . $this->regex . '#i', $contents ) ) {
			$this->_matches[] = $file;
		}
	}
}
