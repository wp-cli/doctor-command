<?php

namespace runcommand\Doctor\Checks;

abstract class Check {

	/**
	 * WP-CLI hook to perform the check on.
	 *
	 * @var boolean
	 */
	public static $when;

	/**
	 * Status of this check after being run.
	 *
	 * Can be one of 'success', 'warning', 'error'.
	 *
	 * @var string
	 */
	public static $status;

	/**
	 * Message of this check after being run.
	 *
	 * @var integer
	 */
	public static $message;

	/**
	 * Run the check.
	 *
	 * Because each check checks for something different, this method must be
	 * subclassed. Method is expected to set $status_code and $status_message.
	 */
	abstract public function run();

	/**
	 * Get results of the check.
	 *
	 * @return array
	 */
	public function get_results() {
		return array(
			'status'    => self::$status,
			'message'   => self::$message,
		);
	}

}
