<?php

namespace WP_CLI\Doctor\Check;

use WP_CLI;
use WP_CLI\Doctor\Check;

/**
 * Warns when autoloaded transients size exceeds threshold of %threshold_kb% kb.
 */
class Transients_Size extends Check {

	/**
	 * Threshold in kilobytes.
	 *
	 * @var integer
	 */
	protected $threshold_kb = 900;

	/**
	 * @return void
	 */
	public function run() {
		ob_start();
		WP_CLI::run_command(
			array( 'option', 'list' ),
			array(
				'transients' => true,
				'autoload'   => 'on',
				'format'     => 'total_bytes',
			)
		);
		$total_bytes = (int) ob_get_clean();

		$threshold_bytes = $this->threshold_kb * 1024;
		$human_threshold = self::format_bytes( $threshold_bytes );
		$human_total     = self::format_bytes( $total_bytes );
		if ( $threshold_bytes < $total_bytes ) {
			$this->set_status( 'warning' );
			$this->set_message( "Autoloaded transients size ({$human_total}) exceeds threshold ({$human_threshold})" );
		} else {
			$this->set_status( 'success' );
			$this->set_message( "Autoloaded transients size ({$human_total}) is less than threshold ({$human_threshold})." );
		}
	}

	/**
	 * @param int|float $size Size in bytes.
	 * @param int       $precision Precision.
	 * @return string
	 */
	private static function format_bytes( $size, $precision = 2 ) {
		if ( 0 >= $size ) {
			return '0';
		}

		$base     = log( $size, 1024 );
		$suffixes = array( '', 'kb', 'mb', 'g', 't' );
		return round( pow( 1024, $base - floor( $base ) ), $precision ) . $suffixes[ (int) floor( $base ) ];
	}
}
