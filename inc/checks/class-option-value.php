<?php

namespace runcommand\Doctor\Checks;

use WP_CLI;

/**
 * Check the value of an option in the database.
 */
class Option_Value extends Check {

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
	protected $expected_value;

	public function run() {

		$actual_value = get_option( $this->option );
		if ( $actual_value == $this->expected_value ) {
			$this->status = 'success';
			$this->message = "Option '{$this->option}' is '{$actual_value}' as expected.";
		} else {
			$this->status = 'error';
			$this->message = "Option '{$this->option}' is '{$actual_value}' but expected to be '{$this->expected_value}'.";
		}

		// Message translation for options
		switch ( $this->option ) {
			case 'blog_public':
				$public_actual = $actual_value ? 'public' : 'private';
				$public_expected = 'public' === $public_actual ? 'private' : 'public';
				if ( 'success' === $this->status ) {
					$this->message = "Site is {$public_actual} as expected.";
				} else {
					$this->message = "Site is {$public_actual} but expected to be {$public_expected}.";
				}
				break;

			default:
				# code...
				break;
		}

	}

}
