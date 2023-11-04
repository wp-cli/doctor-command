<?php

namespace WP_CLI\Doctor\Check;

use SplFileInfo;

/**
 * Checks files on the filesystem to be of a certain type.
 */
class File_Type extends File {

	/**
	 * Assert the file type is or isn't a symlink.
	 *
	 * @var bool
	 */
	protected $symlink;

	public function run() {

		if ( isset( $this->symlink ) ) {
			$symlink = $this->symlink ? 'true' : 'false';
			if ( ! empty( $this->_matches ) ) {
				$this->set_status( 'error' );
				$count   = count( $this->_matches );
				$message = 1 === $count ? "1 '{$this->extension}' file" : "{$count} '{$this->extension}' files";
				$this->set_message( "{$message} failed assertion that symlink is '{$symlink}'." );
			} else {
				$this->set_status( 'success' );
				$this->set_message( "All '{$this->extension}' files passed assertion that symlink is '{$symlink}'." );
			}
		}
	}

	public function check_file( SplFileInfo $file ) {
		if ( isset( $this->symlink ) ) {
			if ( 'link' === $file->getType() && false === $this->symlink ) {
				$this->_matches[] = $file;
			} elseif ( 'link' !== $file->getType() && true === $this->symlink ) {
				$this->_matches[] = $file;
			}
		}
	}
}
