<?php

namespace WP_CLI\Doctor\Check;

use WP_CLI;
use WP_CLI\Doctor\Check;

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

	public function run( $verbose ) {

		//add checks for no plugins.

		if ( $verbose ) {
			WP_CLI::log( "Checking whether the plugins deactivated percentage is greater than {$this->threshold_percentage}..." );
		}

		$plugins = self::get_plugins();

		$active   = 0;
		$inactive = 0;
		foreach ( self::get_plugins() as $plugin ) {
			if ( 'active' === $plugin['status'] || 'active-network' === $plugin['status'] ) {
				++$active;
			} elseif ( 'inactive' === $plugin['status'] ) {
				++$inactive;
			}
		}

		$threshold = (int) $this->threshold_percentage;

		if ( $active === 0 && $inactive === 0 ) {
			if ( $verbose ) {
				WP_CLI::log( "No plugins found." );
			}

			$this->set_status( 'success' );
			$this->set_message( "Less than {$threshold} percent of plugins are deactivated." );

			return;
		}


		$inactive_percentage = $inactive / ( $inactive + $active );

		if ( $verbose ) {
			WP_CLI::log( sprintf( "%.2f percent of plugins are deactivated.",  $inactive_percentage * 100 ) );
		}

		if ( $inactive + $active > 0 && $inactive_percentage > ( $threshold / 100 ) ) {
			$this->set_status( 'warning' );
			$this->set_message( "Greater than {$threshold} percent of plugins are deactivated." );
		} else {
			$this->set_status( 'success' );
			$this->set_message( "Less than {$threshold} percent of plugins are deactivated." );
		}
	}
}
