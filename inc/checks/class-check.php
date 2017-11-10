<?php

namespace runcommand\Doctor\Checks;

use WP_CLI;

abstract class Check {

	/**
	 * WP-CLI hook to perform the check on.
	 *
	 * @var string
	 */
	 protected $_when = 'after_wp_load';

	/**
	 * Status of this check after being run.
	 *
	 * Can be one of 'success', 'warning', 'error', 'incomplete'.
	 *
	 * @var string
	 */
	protected $_status = 'incomplete';

	/**
	 * A user-definable status to return if the check fails.
	 *
	 * Can be one of 'success', 'warning', 'error'.
	 *
	 * @var string
	 */
	protected $status_for_failure = 'error';

	/**
	 * Message of this check after being run.
	 *
	 * @var integer
	 */
	protected $_message = '';

	/**
	 * Initialize the check.
	 */
	public function __construct( $options = array() ) {

		foreach( $options as $k => $v ) {
			// Don't permit direct access to private class vars
			if ( '_' === $k[0] ) {
				continue;
			}
			if ( ! property_exists( $this, $k ) ) {
				WP_CLI::error( "Cannot set invalid property '{$k}'." );
			}
			$this->$k = $v;
		}
	}

	/**
	 * Get when the check is expected to run.
	 */
	public function get_when() {
		return $this->_when;
	}

	/**
	 * Set when the check is expected to run.
	 *
	 * @param string $when
	 */
	public function set_when( $when ) {
		$this->_when = $when;
	}

	/**
	 * Set the status of the check.
	 *
	 * @param string $status
	 */
	protected function set_status( $status ) {
		$this->_status = $status;
	}

	/**
	 * Set the status of the check for a failure.
	 */
	protected function set_status_for_failure() {
		$this->_status = $this->status_for_failure;
	}

	/**
	 * Set the message of the check.
	 *
	 * @param string $message
	 */
	protected function set_message( $message ) {
		$this->_message = $message;
	}

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
			'status'    => $this->_status,
			'message'   => $this->_message,
		);
	}

}
