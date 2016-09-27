<?php

namespace runcommand\Doctor\Checks;

use WP_CLI;

/**
 * Check whether there is an excess total number of cron jobs.
 */
class Cron_Count extends Cron {

	/**
	 * Warn when there are greater than this number of cron jobs.
	 *
	 * @var integer
	 */
	protected $threshold_count = 50;

	public function run() {
		$crons = self::get_crons();
		$job_count = 0;
		foreach( $crons as $timestamp => $jobs ) {
			// 'cron' option includes a 'version' key... ?!?!
			if ( 'version' === $timestamp ) {
				continue;
			}

			foreach( $jobs as $job => $data ) {
				$job_count++;
			}
		}

		if ( $job_count >= $this->threshold_count ) {
			$this->status = 'error';
			$this->message = 'Total number of cron jobs exceeds expected threshold.';
		} else {
			$this->status = 'success';
			$this->message = 'Total number of cron jobs is within normal operating expectations.';
		}
	}

}
