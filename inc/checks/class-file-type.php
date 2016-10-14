<?php

namespace runcommand\Doctor\Checks;

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
			if ( ! empty( $this->matches ) ) {
				$this->status = 'error';
				$count = count( $this->matches );
				$message = $count === 1 ? "1 '{$this->extension}' file" : "{$count} '{$this->extension}' files";
				$this->message = "{$message} failed assertion that symlink is '{$symlink}'.";
			} else {
				$this->status = 'success';
				$this->message = "All '{$this->extension}' files passed assertion that symlink is '{$symlink}'.";
			}
		}

	}

	public function check_file( SplFileInfo $file ) {
		if ( isset( $this->symlink ) ) {
			if ( 'link' === $file->getType() && false === $this->symlink ) {
				$this->matches[] = $file;
			} else if ( 'link' !== $file->getType() && true === $this->symlink ) {
				$this->matches[] = $file;
			}
		}
	}

}
