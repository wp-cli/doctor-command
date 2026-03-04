<?php

namespace WP_CLI\Doctor\Check;

use WP_CLI;
use WP_CLI\Doctor\Check;

/**
 * Warns when multisite network site count is outside the range %minimum%-%maximum%.
 */
class Network_Site_Count extends Check {

	/**
	 * Minimum number of sites expected in the network.
	 *
	 * @var integer
	 */
	protected $minimum = 1;

	/**
	 * Maximum number of sites expected in the network.
	 *
	 * @var integer
	 */
	protected $maximum = 500;

	public function __construct( $options = array() ) {
		parent::__construct( $options );

		$minimum = (int) $this->minimum;
		$maximum = (int) $this->maximum;
		if ( $minimum < 0 || $maximum < 0 || $maximum < $minimum ) {
			WP_CLI::error( 'Invalid thresholds. Ensure 0 <= minimum <= maximum.' );
		}
	}

	public function run() {
		if ( ! is_multisite() ) {
			$this->set_status( 'success' );
			$this->set_message( 'WordPress is not a multisite installation; network site count check skipped.' );
			return;
		}

		$count   = (int) get_sites( array( 'count' => true ) );
		$minimum = (int) $this->minimum;
		$maximum = (int) $this->maximum;

		if ( $count < $minimum || $count > $maximum ) {
			$this->set_status( 'warning' );
			$this->set_message( "Network has {$count} sites; expected between {$minimum} and {$maximum}." );
			return;
		}

		$this->set_status( 'success' );
		$this->set_message( "Network has {$count} sites; expected between {$minimum} and {$maximum}." );
	}
}
