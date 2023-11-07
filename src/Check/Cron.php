<?php

namespace WP_CLI\Doctor\Check;

use WP_CLI;
use WP_CLI\Doctor\Check;

abstract class Cron extends Check {

	protected static $crons;

	protected static function get_crons() {

		if ( isset( self::$crons ) ) {
			return self::$crons;
		}

		ob_start();
		WP_CLI::run_command( array( 'cron', 'event', 'list' ), array( 'format' => 'json' ) );
		$ret         = ob_get_clean();
		self::$crons = ! empty( $ret ) ? json_decode( $ret, true ) : array();
		return self::$crons;
	}
}
