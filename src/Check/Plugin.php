<?php

namespace WP_CLI\Doctor\Check;

use WP_CLI;
use WP_CLI\Doctor\Check;

abstract class Plugin extends Check {

	protected static $plugins;

	protected static function get_plugins() {

		if ( isset( self::$plugins ) ) {
			return self::$plugins;
		}

		ob_start();
		WP_CLI::run_command( array( 'plugin', 'list' ), array( 'format' => 'json' ) );
		$ret           = ob_get_clean();
		self::$plugins = ! empty( $ret ) ? json_decode( $ret, true ) : array();
		return self::$plugins;
	}
}
