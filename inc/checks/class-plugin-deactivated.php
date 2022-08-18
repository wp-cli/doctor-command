<?php

namespace runcommand\Doctor\Checks;

use WP_CLI;

/**
 * Warns when greater than %threshold_percentage%% of plugins are deactivated.
 */
class Plugin_Deactivated extends Plugin {

	/**
	 * Threshold as a percentage.
	 *
	 * @var integer
	 */
	protected $threshold_percentage = 40;

	public function run() {
		$plugins = self::get_plugins();

		$active   = 0;
		$inactive = 0;
		foreach ( self::get_plugins() as $plugin ) {
			if ( 'active' === $plugin['status'] ) {
				$active++;
			} elseif ( 'inactive' === $plugin['status'] ) {
				$inactive++;
			}
		}

		$threshold = (int) $this->threshold_percentage;
		if ( $inactive + $active > 0 && ( $inactive / ( $inactive + $active ) ) > ( $threshold / 100 ) ) {
			$this->set_status( 'warning' );
			$this->set_message( "Greater than {$threshold} percent of plugins are deactivated." );
		} else {
			$this->set_status( 'success' );
			$this->set_message( "Less than {$threshold} percent of plugins are deactivated." );
		}

	}

}
