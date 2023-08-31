<?php

if ( ! class_exists( 'WP_CLI' ) ) {
	return;
}

spl_autoload_register(
	function ( $class_name ) {
			$class_name = ltrim( $class_name, '\\' );
		if ( 0 !== stripos( $class_name, 'runcommand\\Doctor\\' ) ) {
			return;
		}

			$parts = explode( '\\', $class_name );
			array_shift( $parts ); // Don't need "runcommand\Doctor"
			array_shift( $parts );
			$last    = array_pop( $parts ); // File should be 'class-[...].php'
			$last    = 'class-' . $last . '.php';
			$parts[] = $last;
			$file    = __DIR__ . '/inc/' . str_replace( '_', '-', strtolower( implode( '/', $parts ) ) );
		if ( file_exists( $file ) ) {
			require $file;
		}
	}
);

WP_CLI::add_command( 'doctor', 'runcommand\Doctor\Command' );
