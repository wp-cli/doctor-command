`wp doctor` lets you easily run a series of configurable checks to diagnose what's ailing with WordPress.

Save hours identifying the health of your WordPress installs by codifying diagnosis procedures as a series of checks to run with WP-CLI. `wp doctor` comes with tens of checks out of the box, with more being added all of the time. You can also create your own `doctor.yml` file to define the checks that are most important to you.

At its core, each check includes a name, status (either "success", "warning", or "error"), and a human-readable message:

```
$ wp doctor check cron-count
+------------+---------+--------------------------------------------------------------------+
| name       | status  | message                                                            |
+------------+---------+--------------------------------------------------------------------+
| cron-count | success | Total number of cron jobs is within normal operating expectations. |
+------------+---------+--------------------------------------------------------------------+
```

Run together, `wp doctor` checks give you a high-level overview to the health of your WordPress installs.
