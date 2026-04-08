<?php

namespace WP_CLI\Doctor\Check;

use WP_CLI;
use WP_CLI\Doctor\Check;

abstract class Cron extends Check {

	/**
	 * @var array<array<string, mixed>>|null
	 */
	protected static $crons;

	/**
	 * @return array<array<string, mixed>>
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
		$ret     = ob_get_clean();
		$decoded = ! empty( $ret ) ? json_decode( $ret, true ) : array();
		if ( ! is_array( $decoded ) ) {
			$decoded = array();
		}
		/** @var array<array<string, mixed>> $decoded */
		self::$crons = $decoded;
		return self::$crons;
	}
}
