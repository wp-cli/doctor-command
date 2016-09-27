<?php

namespace runcommand\Doctor\Checks;

use WP_CLI;

/**
 * Check whether there are excess duplicate cron jobs.
 */
class Cron_Duplicates extends Cron {

	/**
	 * Warn when there are greater than this number of duplicates.
	 *
	 * @var integer
	 */
	protected $threshold_count = 10;

	public function run() {
		$crons = $this->get_crons();
		$job_counts = array();
		foreach( $crons as $timestamp => $jobs ) {
			// 'cron' option includes a 'version' key... ?!?!
			if ( 'version' === $timestamp ) {
				continue;
			}

			foreach( $jobs as $job => $data ) {
				$job_counts[ $job ]++;
			}
		}

		$excess_duplicates = false;
		foreach( $job_counts as $job => $count ) {
			if ( $count >= $this->threshold_count ) {
				$excess_duplicates = true;
			}
		}
		if ( $excess_duplicates ) {
			$this->status = 'error';
			$this->message = "Detected {$this->threshold_count} or more of the same cron job.";
		} else {
			$this->status = 'success';
			$this->message = "All cron job counts are within normal operating expectations.";
		}
	}

}
