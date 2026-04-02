<?php

namespace WP_CLI\Doctor\Check;

use WP_CLI;
use WP_CLI\Doctor\Check;

abstract class Cron extends Check {

	/**
	 * @var array<mixed>|null
	 */
	protected static $crons;

	/**
	 * @return array<mixed>
	 */
	protected static function get_crons() {

		if ( isset( self::$crons ) ) {
			return self::$crons;
		}

		ob_start();
		WP_CLI::run_command(
			array( 'cron', 'event', 'list' ),
			array(
				'format' => 'json',
				'fields' => 'hook,args',
			)
		);
		$ret         = ob_get_clean();
		self::$crons = ! empty( $ret ) ? json_decode( $ret, true ) : array();
		return self::$crons;
	}
}
