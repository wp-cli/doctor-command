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
	protected $_when = false;

	/**
	 * File extension to check.
	 *
	 * Separate multiple file extensions with a '|'.
	 *
	 * @var string
	 */
	protected $extension = 'php';

	/**
	 * Check a specific file path.
	 *
	 * Value should be relative to ABSPATH (e.g. 'wp-content' or 'wp-config.php')
	 *
	 * @var string
	 */
	protected $path = '';

	/**
	 * Only check the wp-content directory.
	 *
	 * @var boolean
	 */
	protected $only_wp_content = false;

	/**
	 * Any files matching the check.
	 *
	 * @var array
	 */
	protected $_matches = array();

	/**
	 * Get the options for this check
	 *
	 * @return string
	 */
	public function get_options() {
		return array(
			'extension'       => $this->extension,
			'only_wp_content' => $this->only_wp_content,
			'path'            => $this->path,
		);
	}

}
