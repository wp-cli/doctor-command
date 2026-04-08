<?php

namespace WP_CLI\Doctor\Check;

use WP_CLI;
use WP_CLI\Doctor\Check;

abstract class Plugin extends Check {

	/**
	 * @var array<array<string, mixed>>|null
	 */
	protected static $plugins;

	/**
	 * @return array<array<string, mixed>>
	 */
	protected static function get_plugins() {

		if ( isset( self::$plugins ) ) {
			return self::$plugins;
		}

		ob_start();
		WP_CLI::run_command( array( 'plugin', 'list' ), array( 'format' => 'json' ) );
		$ret     = ob_get_clean();
		$decoded = ! empty( $ret ) ? json_decode( $ret, true ) : array();
		if ( ! is_array( $decoded ) ) {
			$decoded = array();
		}
		/** @var array<array<string, mixed>> $decoded */
		self::$plugins = $decoded;
		return self::$plugins;
	}
}
