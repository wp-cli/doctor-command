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
	'runcommand\Doctor\Checks\Core_Update',
) as $class ) {
	$bits = explode( '\\', $class );
	$name = array_pop( $bits );
	runcommand\Doctor\Checks::add_check( str_replace( '_', '-', strtolower( $name ) ), $class );
}

WP_CLI::add_command( 'doctor', 'runcommand\Doctor\Command' );
