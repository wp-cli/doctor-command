<?php

if ( ! class_exists( 'WP_CLI' ) ) {
	return;
}

$wpcli_doctor_autoloader = __DIR__ . '/vendor/autoload.php';

if ( file_exists( $wpcli_doctor_autoloader ) ) {
	require_once $wpcli_doctor_autoloader;
}

WP_CLI::add_command( 'doctor', 'WP_CLI\Doctor\Command' );
