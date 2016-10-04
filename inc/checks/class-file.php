<?php

namespace runcommand\Doctor\Checks;

use SplFileInfo;

/**
 * Check files on the filesystem.
 */
abstract class File extends Check {

	/**
	 * File checks are run as a group
	 */
	protected $when = false;

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

	/**
	 * Initialize the check.
	 */
	public function __construct( $options = array() ) {
		if ( isset( $options['matches'] ) ) {
			unset( $options['matches'] );
		}
		parent::__construct( $options );
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
