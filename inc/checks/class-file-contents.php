<?php

namespace runcommand\Doctor\Checks;

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
	protected $exists = false;

	public function run() {

		if ( isset( $this->regex ) ) {
			if ( ! empty( $this->_matches ) ) {
				//if matches are found
				if ( $this->exists == true ) {
					//$exists set to true so we should report true if something is found
					$this->set_status( 'success' );
					$count = count( $this->_matches );
					$message = $count === 1 ? "1 '{$this->extension}' file" : "{$count} '{$this->extension}' files";
					$this->set_message( "{$message} passed check for '{$this->regex}'." );
				}else{
					//$exists is not set to true so we should report error if something is found
					$this->set_status( 'error' );
					$count = count( $this->_matches );
					$message = $count === 1 ? "1 '{$this->extension}' file" : "{$count} '{$this->extension}' files";
					$this->set_message( "{$message} failed check for '{$this->regex}'." );
				}
			} else {
				//No Matches Found
				if ( $this->exists == true ) {
					//$exists set to true so we should report error if regex is not found
					$this->set_status( 'error' );
					$count = count( $this->_matches );
					$message = $count === 1 ? "1 '{$this->extension}' file" : "{$count} '{$this->extension}' files";
					$this->set_message( "{$message} passed check for '{$this->regex}'." );
				}else{
					//$exists is not set to true so we should report success if regex is not found
					$this->set_status( 'success' );
					$this->set_message( "All '{$this->extension}' files passed check for '{$this->regex}'." );
				}

			}
		}

	}

	public function check_file( SplFileInfo $file ) {
		if ( isset( $this->regex ) ) {
			$contents = file_get_contents( $file->getPathname() );
			if ( preg_match( '#' . $this->regex . '#i', $contents ) ) {
				$this->_matches[] = $file;
			}
		}
	}

}
