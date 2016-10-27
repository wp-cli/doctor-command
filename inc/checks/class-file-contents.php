<?php

namespace runcommand\Doctor\Checks;

use SplFileInfo;

/**
 * Checks files on the filesystem for regex pattern `%regex%`.
 */
class File_Contents extends File {

	/**
	 * Regex pattern to check.
	 *
	 * @var string
	 */
	protected $regex;

	public function run() {

		if ( isset( $this->regex ) ) {
			if ( ! empty( $this->matches ) ) {
				$this->set_status( 'error' );
				$count = count( $this->matches );
				$message = $count === 1 ? "1 '{$this->extension}' file" : "{$count} '{$this->extension}' files";
				$this->set_message( "{$message} failed check for '{$this->regex}'." );
			} else {
				$this->set_status( 'success' );
				$this->set_message( "All '{$this->extension}' files passed check for '{$this->regex}'." );
			}
		}

	}

	public function check_file( SplFileInfo $file ) {
		if ( isset( $this->regex ) ) {
			$contents = file_get_contents( $file->getPathname() );
			if ( preg_match( '#' . $this->regex . '#i', $contents ) ) {
				$this->matches[] = $file;
			}
		}
	}

}
