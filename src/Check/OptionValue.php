<?php

namespace WP_CLI\Doctor\Check;

use WP_CLI;
use WP_CLI\Doctor\Check;

/**
 * Confirms the expected value of the '%option%' option.
 */
class OptionValue extends Check {

	/**
	 * Name of the option.
	 *
	 * @var string
	 */
	protected $option;

	/**
	 * Expected value of the option.
	 *
	 * @var mixed
	 */
	protected $value;

	/**
	 * Value that the option is expected not to be.
	 *
	 * @var mixed
	 */
	protected $value_is_not;

	public function run() {

		if ( isset( $this->value ) && isset( $this->value_is_not ) ) {
			$this->set_status( 'error' );
			$this->set_message( 'You must use either "value" or "value_is_not".' );
			return;
		}

		$actual_value = get_option( $this->option );

		if ( isset( $this->value ) ) {
			if ( $actual_value == $this->value ) { // phpcs:ignore Universal.Operators.StrictComparisons -- Keep existing behavior.
				$status  = 'success';
				$message = "Option '{$this->option}' is '{$this->value}' as expected.";
			} else {
				$status  = 'error';
				$message = "Option '{$this->option}' is '{$actual_value}' but expected to be '{$this->value}'.";
			}
		} elseif ( isset( $this->value_is_not ) ) {
			if ( $actual_value == $this->value_is_not ) { // phpcs:ignore Universal.Operators.StrictComparisons -- Keep existing behavior.
				$status  = 'error';
				$message = "Option '{$this->option}' is '{$actual_value}' and expected not to be.";
			} else {
				$status  = 'success';
				$message = "Option '{$this->option}' is not '{$this->value_is_not}' as expected.";
			}
		}

		$this->set_status( $status );
		$this->set_message( $message );

		// Message translation for options
		switch ( $this->option ) {
			case 'blog_public':
				$public_actual   = $actual_value ? 'public' : 'private';
				$public_expected = 'public' === $public_actual ? 'private' : 'public';
				if ( 'success' === $status ) {
					$this->set_message( "Site is {$public_actual} as expected." );
				} else {
					$this->set_message( "Site is {$public_actual} but expected to be {$public_expected}." );
				}
				break;

			default:
				# code...
				break;
		}
	}
}
