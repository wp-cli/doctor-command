runcommand/doctor
=================

Diagnose problems within WordPress by running a series of checks for symptoms.

[![CircleCI](https://circleci.com/gh/runcommand/doctor.svg?style=svg&circle-token=383527fb616ce6acb8e7da293c0dfac1cc2a9a10)](https://circleci.com/gh/runcommand/doctor)

Quick links: [Overview](#overview) | [Using](#using) | [Installing](#installing) | [Support](#support)

## Overview

`wp doctor` lets you easily run a series of configurable checks to diagnose what's ailing with WordPress.

Save hours identifying the health of your WordPress installs by codifying diagnosis procedures as a series of checks to run with WP-CLI. `wp doctor` comes with tens of checks out of the box, with more being added all of the time. You can also create your own `doctor.yml` file to define the checks that are most important to you.

Each check includes a name, status (either "success", "warning", or "error"), and a human-readable message. For example, `cron-count` is a check to ensure WP Cron hasn't exploded with jobs:

```
$ wp doctor check cron-count
+------------+---------+--------------------------------------------------------------------+
| name       | status  | message                                                            |
+------------+---------+--------------------------------------------------------------------+
| cron-count | success | Total number of cron jobs is within normal operating expectations. |
+------------+---------+--------------------------------------------------------------------+
```

`wp doctor` is designed for extensibility. Create a custom `doctor.yml` file to define additional checks you deem necessary for your system:

```
plugin-w3-total-cache:
  check: Plugin_Status
  options:
    name: w3-total-cache
    status: uninstalled
```

Then, run the custom `doctor.yml` file using the `--config=<file>` parameter:

```
$ wp doctor check --fields=name,status --all --config=doctor.yml
+-----------------------+--------+
| name                  | status |
+-----------------------+--------+
| plugin-w3-total-cache | error  |
+-----------------------+--------+
```

Running all checks together, `wp doctor` is the fastest way to get a high-level overview to the health of your WordPress installs.

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
'error'. The message is a human-readable explanation of the status.

	[<checks>...]
		Names of one or more checks to run.

	[--all]
		Run all registered checks.

	[--spotlight]
		Focus on warnings and errors; ignore any successful checks.

	[--config=<file>]
		Use checks registered in a specific configuration file.

	[--fields=<fields>]
		Limit the output to specific fields. Default is name,status,message.

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

    # Verify WordPress core is up to date.
    $ wp doctor check core-update
    +-------------+---------+-----------------------------------------------------------+
    | name        | status  | message                                                   |
    +-------------+---------+-----------------------------------------------------------+
    | core-update | warning | A new major version of WordPress is available for update. |
    +-------------+---------+-----------------------------------------------------------+

    # Verify the site is public as expected.
    $ wp doctor check option-blog-public
    +--------------------+--------+--------------------------------------------+
    | name               | status | message                                    |
    +--------------------+--------+--------------------------------------------+
    | option-blog-public | error  | Site is private but expected to be public. |
    +--------------------+--------+--------------------------------------------+



### wp doctor list

List all available checks to run.

~~~
wp doctor list [--config=<file>] [--fields=<fields>] [--format=<format>]
~~~

**OPTIONS**

	[--config=<file>]
		Use checks registered in a specific configuration file.

	[--fields=<fields>]
		Limit the output to specific fields. Defaults to name,description.

	[--format=<format>]
		Render output in a specific format.
		---
		default: table
		options:
		  - table
		  - json
		  - csv
		  - count
		---

**EXAMPLES**

    $ wp doctor list
    +-------------+---------------------------------------------+
    | name        | description                                 |
    +-------------+---------------------------------------------+
    | core-update | Check whether WordPress core is up to date. |
    +-------------+---------------------------------------------+

## Installing

[Sign up to receive an email when `wp doctor` is available](https://runcommand.io/newsletter-signup/).

## Support

Support is available to paying [runcommand](https://runcommand.io/) customers.

Have access to [Sparks](https://github.com/runcommand/sparks/), the runcommand issue tracker? Feel free to [open a new issue](https://github.com/runcommand/sparks/issues/new).

Think you’ve found a bug? Before you create a new issue, you should [search existing issues](https://github.com/runcommand/sparks/issues?q=label%3Abug%20) to see if there’s an existing resolution to it, or if it’s already been fixed in a newer version. Once you’ve done a bit of searching and discovered there isn’t an open or fixed issue for your bug, please [create a new issue](https://github.com/runcommand/sparks/issues/new) with description of what you were doing, what you saw, and what you expected to see.

Want to contribute a new feature? Please first [open a new issue](https://github.com/runcommand/sparks/issues/new) to discuss whether the feature is a good fit for the project. Once you've decided to work on a pull request, please include [functional tests](https://wp-cli.org/docs/pull-requests/#functional-tests) and follow the [WordPress Coding Standards](http://make.wordpress.org/core/handbook/coding-standards/).

Don't have access to Sparks? You can also email [support@runcommand.io](mailto:support@runcommand.io) with general questions, bug reports, and feature suggestions.


