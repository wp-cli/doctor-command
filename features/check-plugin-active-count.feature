Feature: Check whether a high number of plugins are activated

  Scenario: Verify check description
    Given an empty directory

    When I run `wp doctor list --fields=name,description`
    Then STDOUT should be a table containing rows:
      | name                       | description                                                                    |
      | plugin-active-count        | Warns when there are greater than 80 plugins activated.                        |

  Scenario: Less than threshold plugins are active
    Given a WP install
    # Uses "try" because the SQLite plugin attempts to do a redirect.
    # See https://github.com/WordPress/sqlite-database-integration/issues/49
    And I try `wp plugin activate --all`

    When I run `wp plugin list --format=count`
    Then save STDOUT as {PLUGIN_COUNT}

    When I run `wp doctor check plugin-active-count`
    Then STDOUT should be a table containing rows:
      | name                | status  | message                                                   |
      | plugin-active-count | success | Number of active plugins ({PLUGIN_COUNT}) is less than threshold (80). |

  Scenario: Greater than threshold plugins are active
    Given a WP install
    And a config.yml file:
      """
      plugin-active-count:
        class: WP_CLI\Doctor\Check\PluginActiveCount
        options:
          threshold_count: 3
      """
    And I run `wp plugin install user-switching rewrite-rules-inspector`
    # Uses "try" because the SQLite plugin attempts to do a redirect.
    # See https://github.com/WordPress/sqlite-database-integration/issues/49
    And I try `wp plugin activate --all`

    When I run `wp plugin list --format=count`
    Then save STDOUT as {PLUGIN_COUNT}

    When I run `wp doctor check plugin-active-count --config=config.yml`
    Then STDOUT should be a table containing rows:
      | name                | status  | message                                             |
      | plugin-active-count | warning | Number of active plugins ({PLUGIN_COUNT}) exceeds threshold (3). |

  Scenario: Include network-enabled plugins in active plugin count
    Given a WP multisite installation
    # Uses "try" because the SQLite plugin attempts to do a redirect.
    # See https://github.com/WordPress/sqlite-database-integration/issues/49
    And I try `wp plugin activate --network --all`

    When I run `wp doctor check plugin-active-count`
    Then STDOUT should be a table containing rows:
      | name                | status  | message                                                   |
      | plugin-active-count | success | Number of active plugins (2) is less than threshold (80). |
