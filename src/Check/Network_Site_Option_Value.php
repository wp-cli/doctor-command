<?php

namespace WP_CLI\Doctor\Check;

use WP_CLI\Doctor\Check;

/**
 * Confirms the expected value of the network option '%option%'.
 */
class Network_Site_Option_Value extends Check {

	/**
	 * Name of the network option.
	 *
	 * @var string
	 */
	protected $option;

	/**
	 * Expected value of the network option.
	 *
	 * @var mixed
	 */
	protected $value;

	/**
	 * Value that the network option is expected not to be.
	 *
	 * @var mixed
	 */
	protected $value_is_not;

	public function run() {
		if ( ! is_multisite() ) {
			$this->set_status( 'success' );
			$this->set_message( 'WordPress is not a multisite installation; network option check skipped.' );
			return;
		}

		if ( isset( $this->value ) && isset( $this->value_is_not ) ) {
			$this->set_status( 'error' );
			$this->set_message( 'You must use either "value" or "value_is_not".' );
			return;
		}

		if ( ! isset( $this->value ) && ! isset( $this->value_is_not ) ) {
			$this->set_status( 'error' );
			$this->set_message( 'You must specify "value" or "value_is_not".' );
			return;
		}

		$actual_value = get_site_option( $this->option );

		if ( isset( $this->value ) ) {
			if ( $actual_value == $this->value ) { // phpcs:ignore Universal.Operators.StrictComparisons -- Keep existing behavior.
				$this->set_status( 'success' );
				$this->set_message( "Network option '{$this->option}' is '{$this->value}' as expected." );
			} else {
				$this->set_status( 'error' );
				$this->set_message( "Network option '{$this->option}' is '{$actual_value}' but expected to be '{$this->value}'." );
			}
			return;
		}

		if ( $actual_value == $this->value_is_not ) { // phpcs:ignore Universal.Operators.StrictComparisons -- Keep existing behavior.
			$this->set_status( 'error' );
			$this->set_message( "Network option '{$this->option}' is '{$actual_value}' and expected not to be." );
		} else {
			$this->set_status( 'success' );
			$this->set_message( "Network option '{$this->option}' is not '{$this->value_is_not}' as expected." );
		}
	}
}
