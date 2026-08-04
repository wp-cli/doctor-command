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
}
