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
	protected $status;

	/**
	 * Message of this check after being run.
	 *
	 * @var integer
	 */
	protected $message;

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
			'status'    => $this->status,
			'message'   => $this->message,
		);
	}

}
