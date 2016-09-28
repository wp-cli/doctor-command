runcommand/doctor
=================

Run a series of checks against WordPress to diagnose issues.

[![CircleCI](https://circleci.com/gh/runcommand/doctor.svg?style=svg&circle-token=383527fb616ce6acb8e7da293c0dfac1cc2a9a10)](https://circleci.com/gh/runcommand/doctor)

Quick links: [Using](#using) | [Installing](#installing) | [Support](#support)

## Using

This package implements the following commands:

### wp doctor check

Run a series of checks against WordPress to diagnose issues.

~~~
wp doctor check [<checks>...] [--all] [--spotlight] [--config=<file>] [--fields=<fields>] [--format=<format>]
~~~

**OPTIONS**

A check is a routine run against some scope of WordPress that reports
a 'status' and a 'message'. The status can be 'success', 'warning', or
'error'. The message should be a human-readable explanation of the
status.

	[<checks>...]
		Names of one or more checks to run.

	[--all]
		Run all registered checks.

	[--spotlight]
		Focus on warnings and errors; ignore any successful checks.

	[--config=<file>]
		Use checks registered in a specific configuration file.

	[--fields=<fields>]
		Display one or more fields.

	[--format=<format>]
		Render results in a particular format.
		---
		default: table
		options:
		  - table
		  - json
		  - csv
		  - yaml
		---

**EXAMPLES**

    $ wp doctor diagnose core-update
    +-------------+---------+-----------------------------------------------------------+
    | name        | status  | message                                                   |
    +-------------+---------+-----------------------------------------------------------+
    | core-update | warning | A new major version of WordPress is available for update. |
    +-------------+---------+-----------------------------------------------------------+



### wp doctor list

List available checks to run.

~~~
wp doctor list [--config=<file>] [--fields=<fields>] [--format=<format>]
~~~

**OPTIONS**

	[--config=<file>]
		Use checks registered in a specific configuration file.

	[--fields=<fields>]
		Limit the output to specific fields. Defaults to all fields.

	[--format=<format>]
		Render output in a specific format.
		---
		default: table
		options:
		  - table
		  - json
		  - csv
		---

**EXAMPLES**

    $ wp doctor checks
    +-------------+---------------------------------------------+
    | name        | description                                 |
    +-------------+---------------------------------------------+
    | core-update | Check whether WordPress core is up to date. |
    +-------------+---------------------------------------------+

## Installing

Installing this package requires WP-CLI v0.23.0 or greater. Update to the latest stable release with `wp cli update`.

Once you've done so, you can install this package with `wp package install runcommand/doctor`.

## Support

Support is available to paying [runcommand](https://runcommand.io/) customers.

Have access to [Sparks](https://github.com/runcommand/sparks/), the runcommand issue tracker? Feel free to [open a new issue](https://github.com/runcommand/sparks/issues/new).

Think you’ve found a bug? Before you create a new issue, you should [search existing issues](https://github.com/runcommand/sparks/issues?q=label%3Abug%20) to see if there’s an existing resolution to it, or if it’s already been fixed in a newer version. Once you’ve done a bit of searching and discovered there isn’t an open or fixed issue for your bug, please [create a new issue](https://github.com/runcommand/sparks/issues/new) with description of what you were doing, what you saw, and what you expected to see.

Want to contribute a new feature? Please first [open a new issue](https://github.com/runcommand/sparks/issues/new) to discuss whether the feature is a good fit for the project. Once you've decided to work on a pull request, please include [functional tests](https://wp-cli.org/docs/pull-requests/#functional-tests) and follow the [WordPress Coding Standards](http://make.wordpress.org/core/handbook/coding-standards/).

Don't have access to Sparks? You can also email [support@runcommand.io](mailto:support@runcommand.io) with general questions, bug reports, and feature suggestions.


