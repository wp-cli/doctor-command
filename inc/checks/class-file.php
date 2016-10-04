<?php

namespace runcommand\Doctor\Checks;

use SplFileInfo;

/**
 * Check files on the filesystem.
 */
class File extends Check {

	/**
	 * File checks are run as a group
	 */
	protected $when = false;

	/**
	 * Regex pattern to check.
	 *
	 * @var string
	 */
	protected $regex;

	/**
	 * File extension to check.
	 *
	 * Separate multiple file extensions with a '|'.
	 *
	 * @var string
	 */
	protected $extension = 'php';

	/**
	 * Any files matching the check.
	 *
	 * @var array
	 */
	protected $matches = array();

	public function run() {

		if ( isset( $this->regex ) ) {
			if ( ! empty( $this->matches ) ) {
				$this->status = 'error';
				$count = count( $this->matches );
				$message = $count === 1 ? "1 '{$this->extension}' file" : "{$count} '{$this->extension}' files";
				$this->message = "{$message} failed check for '{$this->regex}'.";
			} else {
				$this->status = 'success';
				$this->message = "All '{$this->extension}' files passed check for '{$this->regex}'.";
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

	/**
	 * Get the file extension for this check
	 *
	 * @return string
	 */
	public function get_extension() {
		return $this->extension;
	}

}
