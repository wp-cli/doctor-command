`wp doctor` lets you easily run a series of configurable checks to diagnose what's ailing with WordPress.

Without `wp doctor`, your team has to rely on their memory to manually debug problems. With `wp doctor`, your team saves hours identifying the health of your WordPress installs by codifying diagnosis procedures as a series of checks to run with WP-CLI. `wp doctor` [comes with dozens of checks out of the box](https://make.wordpress.org/cli/handbook/guides/doctor/doctor-default-checks/), and [supports customized `doctor.yml` files](https://make.wordpress.org/cli/handbook/guides/doctor/doctor-customize-config/) to define the checks that are most important to you.

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
