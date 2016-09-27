<?php

namespace runcommand\Doctor\Checks;

use WP_CLI;

abstract class Cron extends Check {

	protected static $crons;

	protected static function get_crons() {

		if ( isset( self::$crons ) ) {
			return self::$crons;
		}

		self::$crons = get_option('cron');;
		return self::$crons;
	}

}
