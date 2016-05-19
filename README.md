runcommand/doctor
=================

Run a series of checks against WordPress to diagnose issues.

[![Build Status](https://travis-ci.org/runcommand/doctor.svg?branch=master)](https://travis-ci.org/runcommand/doctor)

Quick links: [Using](#using) | [Installing](#installing) | [Contributing](#contributing)

## Using

This package implements the following commands:

### wp doctor diagnose

Run a series of checks against WordPress to diagnose issues.

~~~
wp doctor diagnose <checks>...
~~~

A check is a routine run against some scope of WordPress that reports
a 'status' and a 'message'. The status can be 'success', 'warning', or
'error'. The message should be a human-readable explanation of the
status.

	<checks>...
		One or more checks to run.



### wp doctor checks

List available checks to run.

~~~
wp doctor checks [--format=<format>]
~~~

	[--format=<format>]
		Render output in a specific format.
		---
		default: table
		options:
		  - table
		  - json
		  - csv
		---



## Installing

Installing this package requires WP-CLI v0.23.0 or greater. Update to the latest stable release with `wp cli update`.

Once you've done so, you can install this package with `wp package install runcommand/doctor`.

## Contributing

Code and ideas are more than welcome.

Please [open an issue](https://github.com/runcommand/doctor/issues) with questions, feedback, and violent dissent. Pull requests are expected to include test coverage.
