<?php

if ( ! class_exists( 'WP_CLI' ) ) {
	return;
}

require_once dirname( __FILE__ ) . '/inc/class-doctor.php';

spl_autoload_register( function( $class ) {
	$class = ltrim( $class, '\\' );
	if ( 0 !== stripos( $class, 'Doctor\\Checks\\' ) ) {
		return;
	}

	$parts = explode( '\\', $class );
	array_shift( $parts ); // Don't need "Doctor"
	$last = array_pop( $parts ); // File should be 'class-[...].php'
	$last = 'class-' . $last . '.php';
	$parts[] = $last;
	$file = dirname( __FILE__ ) . '/inc/' . str_replace( '_', '-', strtolower( implode( $parts, '/' ) ) );
	if ( file_exists( $file ) ) {
		require $file;
	}
});

foreach( array(
	'Doctor\Checks\Core_Update',
) as $class ) {
	$bits = explode( '\\', $class );
	$name = array_pop( $bits );
	Doctor::add_check( str_replace( '_', '-', strtolower( $name ) ), $class );
}

WP_CLI::add_command( 'doctor diagnose', array( 'Doctor', 'diagnose' ) );
WP_CLI::add_command( 'doctor checks', array( 'Doctor', 'checks' ) );
