<?php

namespace runcommand\Doctor\Checks;

use WP_CLI;

/**
 * Greater than a certain percentage of deactivated plugins.
 */
class Plugin_Active_Count extends Plugin {

	/**
	 * Threshold as a total number of plugins.
	 *
	 * @var integer
	 */
	protected $threshold_count = 80;

	public function run() {
		$plugins = self::get_plugins();

		$active = 0;
		foreach( self::get_plugins() as $plugin ) {
			if ( 'active' === $plugin['status'] ) {
				$active++;
			}
		}

		$threshold = (int) $this->threshold_count;
		if ( $active > $threshold ) {
			$this->status = 'warning';
			$this->message = "More than {$threshold} plugins are active.";
		} else {
			$this->status = 'success';
			$this->message = "Using less than {$threshold} active plugins.";
		}

	}

}
