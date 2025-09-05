<?php

namespace WP_CLI\Doctor\Check;

use WP_CLI;

/**
 * Errors when there's an excess of %threshold_count% duplicate cron jobs registered.
 */
class Cron_Duplicates extends Cron {

	/**
	 * Warn when there are greater than this number of duplicates.
	 *
	 * @var integer
	 */
	protected $threshold_count = 10;

	public function run( $verbose ) {

		if ( $verbose ) {
			WP_CLI::log( "Checking whether the number of duplicate cron job exceeds threshold of {$this->threshold_count}..." );
		}

		$crons             = self::get_crons();
		$job_counts        = array();
		$excess_duplicates = false;
		foreach ( $crons as $job ) {
			if ( ! isset( $job_counts[ $job['hook'] ] ) ) {
				$job_counts[ $job['hook'] ] = 0;
			}
			++$job_counts[ $job['hook'] ];
			if ( $job_counts[ $job['hook'] ] >= $this->threshold_count ) {
				$excess_duplicates = true;
				if ( $verbose ) {
					WP_CLI::log( "- {$job['hook']} has exceeded the threshold limit" );
				}
			}
		}
		if ( $excess_duplicates ) {
			$this->set_status( 'error' );
			$this->set_message( "Detected {$this->threshold_count} or more of the same cron job." );
		} else {
			$this->set_status( 'success' );
			$this->set_message( 'All cron job counts are within normal operating expectations.' );
		}
	}
}
