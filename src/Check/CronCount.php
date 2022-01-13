<?php

namespace WP_CLI\Doctor\Check;

use WP_CLI;

/**
 * Errors when there's an excess of %threshold_count% total cron jobs registered.
 */
class CronCount extends Cron {

	/**
	 * Warn when there are greater than this number of cron jobs.
	 *
	 * @var integer
	 */
	protected $threshold_count = 50;

	public function run() {
		$crons = self::get_crons();
		if ( count( $crons ) >= $this->threshold_count ) {
			$this->set_status( 'error' );
			$this->set_message( 'Total number of cron jobs exceeds expected threshold.' );
		} else {
			$this->set_status( 'success' );
			$this->set_message( 'Total number of cron jobs is within normal operating expectations.' );
		}
	}

}
