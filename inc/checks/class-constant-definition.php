<?php

namespace runcommand\Doctor\Checks;

use WP_CLI;

/**
 * Confirms expected state of the %constant% constant.
 */
class Constant_Definition extends Check {

	/**
	 * Name of the constant.
	 *
	 * @var string
	 */
	protected $constant;

	/**
	 * Whether or not the constant is expected to be defined.
	 *
	 * @var bool
	 */
	protected $defined;

	/**
	 * Whether or not the constant is expected to be a falsy value.
	 *
	 * @var bool
	 */
	protected $falsy;

	/**
	 * Expected value of the constant.
	 *
	 * @var mixed
	 */
	protected $value;

	/**
	 * Initialize the constant check
	 */
	public function __construct( $options = array() ) {
		parent::__construct( $options );
		if ( isset( $this->value ) ) {
			$this->defined = true;
		}
	}

	public function run() {

		if ( isset( $this->falsy ) ) {
			if ( ! defined( $this->constant ) ) {
				if ( $this->falsy ) {
					$this->set_status( 'success' );
					$this->set_message( "Constant '{$this->constant}' is undefined." );
				} else {
					$this->set_status( 'error' );
					$this->set_message( "Constant '{$this->constant}' is undefined but expected to not be falsy." );
				}
			} else {
				$actual_value = constant( $this->constant );
				$human_actual = self::human_value( $actual_value );
				if ( ! $actual_value ) {
					if ( $this->falsy ) {
						$this->set_status( 'success' );
						$this->set_message( "Constant '{$this->constant}' is defined falsy." );
					} else {
						$this->set_status( 'error' );
						$this->set_message( "Constant '{$this->constant}' is defined '{$human_actual}' but expected to not be falsy." );
					}
				} else {
					if ( ! $this->falsy ) {
						$this->set_status( 'success' );
						$this->set_message( "Constant '{$this->constant}' is not defined falsy." );
					} else {
						$this->set_status( 'error' );
						$this->set_message( "Constant '{$this->constant}' is defined '{$human_actual}' but expected to be falsy." );
					}
				}
			}
			return;
		}

		if ( ! defined( $this->constant ) ) {
			if ( $this->defined ) {
				$this->set_status( 'error' );
				if ( isset( $this->value ) ) {
					$human_expected = self::human_value( $this->value );
					$this->set_message( "Constant '{$this->constant}' is undefined but expected to be '{$human_expected}'." );
				} else {
					$this->set_message( "Constant '{$this->constant}' is undefined but expected to be." );
				}
			}
			return;
		}

		if ( ! $this->defined ) {
			$this->set_status( 'error' );
			$this->set_message( "Constant '{$this->constant}' is defined but expected not to be." );
			return;
		}

		if ( $this->defined && ! isset( $this->value ) ) {
			$this->set_status( 'success' );
			$this->set_message( "Constant '{$this->constant}' is defined." );
			return;
		}

		$actual_value = constant( $this->constant );
		$human_actual = self::human_value( $actual_value );

		if ( $actual_value === $this->value ) {
			$this->set_status( 'success' );
			$this->set_message( "Constant '{$this->constant}' is defined '{$human_actual}'." );
		} else {
			$this->set_status( 'error' );
			$human_expected = self::human_value( $this->value );
			$this->set_message( "Constant '{$this->constant}' is defined '{$human_actual}' but expected to be '{$human_expected}'." );
		}

	}

	private static function human_value( $value ) {
		if ( true === $value ) {
			$value = 'true';
		} elseif ( false === $value ) {
			$value = 'false';
		}
		return $value;
	}

}
