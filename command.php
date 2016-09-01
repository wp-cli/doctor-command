<?php

if ( ! class_exists( 'WP_CLI' ) ) {
	return;
}

spl_autoload_register( function( $class ) {
	$class = ltrim( $class, '\\' );
	if ( 0 !== stripos( $class, 'runcommand\\Doctor\\' ) ) {
		return;
	}

	$parts = explode( '\\', $class );
	array_shift( $parts ); // Don't need "runcommand\Doctor"
	array_shift( $parts );
	$last = array_pop( $parts ); // File should be 'class-[...].php'
	$last = 'class-' . $last . '.php';
	$parts[] = $last;
	$file = dirname( __FILE__ ) . '/inc/' . str_replace( '_', '-', strtolower( implode( $parts, '/' ) ) );
	if ( file_exists( $file ) ) {
		require $file;
	}
});

foreach( array(
	'runcommand\Doctor\Checks\Autoload_Options_Size',
	'runcommand\Doctor\Checks\Core_Update',
	'runcommand\Doctor\Checks\Core_Verify_Checksums',
	'runcommand\Doctor\Checks\Plugin_Update',
	'runcommand\Doctor\Checks\Theme_Update',
) as $class ) {
	$bits = explode( '\\', $class );
	$name = array_pop( $bits );
	$name = str_replace( '_', '-', strtolower( $name ) );
	$check = new $class;
	runcommand\Doctor\Checks::add_check( $name, $check );
}

WP_CLI::add_command( 'doctor', 'runcommand\Doctor\Command' );
