wp-cli/doctor-command
=====================

Diagnose problems within WordPress by running a series of checks for symptoms.

[![Build Status](https://travis-ci.org/wp-cli/doctor-command.svg?branch=master)](https://travis-ci.org/wp-cli/doctor-command)

Quick links: [Overview](#overview) | [Using](#using) | [Installing](#installing) | [Contributing](#contributing)

## Overview

`wp doctor` lets you easily run a series of configurable checks to diagnose what's ailing with WordPress.

Without `wp doctor`, your team has to rely on their memory to manually debug problems. With `wp doctor`, your team saves hours identifying the health of your WordPress installs by codifying diagnosis procedures as a series of checks to run with WP-CLI. `wp doctor` [comes with dozens of checks out of the box](https://runcommand.io/to/doctor-default-checks/), and [supports customized `doctor.yml` files](https://runcommand.io/to/customize-doctor-config/) to define the checks that are most important to you.

Each check includes a name, status (either "success", "warning", or "error"), and a human-readable message. For example, `cron-count` is a check to ensure WP Cron hasn't exploded with jobs:

```
$ wp doctor check cron-count
+------------+---------+--------------------------------------------------------------------+
| name       | status  | message                                                            |
+------------+---------+--------------------------------------------------------------------+
| cron-count | success | Total number of cron jobs is within normal operating expectations. |
+------------+---------+--------------------------------------------------------------------+
```

Want to pipe the results into another system? Use `--format=json` or `--format=csv` to render checks in a machine-readable format.

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

A check is a routine run against some scope of WordPress that reports
a 'status' and a 'message'. The status can be 'success', 'warning', or
'error'. The message is a human-readable explanation of the status. If
any of the checks fail, then the command will exit with the code `1`.

**OPTIONS**

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
		  - count
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

Installing this package requires WP-CLI v2.1 or greater. Update to the latest stable release with `wp cli update`.

Once you've done so, you can install the latest stable version of this package with:

```bash
wp package install wp-cli/doctor-command:@stable
```

To install the latest development version of this package, use the following command instead:

```bash
wp package install wp-cli/doctor-command:dev-master
```

## Contributing

We appreciate you taking the initiative to contribute to this project.

Contributing isn’t limited to just code. We encourage you to contribute in the way that best fits your abilities, by writing tutorials, giving a demo at your local meetup, helping other users with their support questions, or revising our documentation.

For a more thorough introduction, [check out WP-CLI's guide to contributing](https://make.wordpress.org/cli/handbook/contributing/). This package follows those policy and guidelines.

### Reporting a bug

Think you’ve found a bug? We’d love for you to help us get it fixed.

Before you create a new issue, you should [search existing issues](https://github.com/wp-cli/doctor-command/issues?q=label%3Abug%20) to see if there’s an existing resolution to it, or if it’s already been fixed in a newer version.

Once you’ve done a bit of searching and discovered there isn’t an open or fixed issue for your bug, please [create a new issue](https://github.com/wp-cli/doctor-command/issues/new). Include as much detail as you can, and clear steps to reproduce if possible. For more guidance, [review our bug report documentation](https://make.wordpress.org/cli/handbook/bug-reports/).

### Creating a pull request

Want to contribute a new feature? Please first [open a new issue](https://github.com/wp-cli/doctor-command/issues/new) to discuss whether the feature is a good fit for the project.

Once you've decided to commit the time to seeing your pull request through, [please follow our guidelines for creating a pull request](https://make.wordpress.org/cli/handbook/pull-requests/) to make sure it's a pleasant experience. See "[Setting up](https://make.wordpress.org/cli/handbook/pull-requests/#setting-up)" for details specific to working on this package locally.


*This README.md is generated dynamically from the project's codebase using `wp scaffold package-readme` ([doc](https://github.com/wp-cli/scaffold-package-command#wp-scaffold-package-readme)). To suggest changes, please submit a pull request against the corresponding part of the codebase.*
