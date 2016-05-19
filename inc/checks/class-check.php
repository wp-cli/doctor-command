<?php

namespace Doctor\Checks;

abstract class Check {

	/**
	 * Whether or not this check requires WP to be loaded.
	 *
	 * @var boolean
	 */
	public static $require_wp_load;

	/**
	 * Status of this check after being run.
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

}
