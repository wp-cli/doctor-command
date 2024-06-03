Feature: Check whether a high number of plugins are activated

  Scenario: Verify check description
    Given an empty directory

    When I run `wp doctor list --fields=name,description`
    Then STDOUT should be a table containing rows:
      | name                       | description                                                                    |
      | plugin-active-count        | Warns when there are greater than 80 plugins activated.                        |

  Scenario: Less than threshold plugins are active
    Given a WP install
    And I run `wp plugin activate --all`

    When I run `wp doctor check plugin-active-count`
    Then STDOUT should be a table containing rows:
      | name                | status  | message                                                   |
      | plugin-active-count | success | Number of active plugins (2) is less than threshold (80). |

  Scenario: Greater than threshold plugins are active
    Given a WP install
    And a config.yml file:
      """
      plugin-active-count:
        class: runcommand\Doctor\Checks\Plugin_Active_Count
        options:
          threshold_count: 3
      """
    And I run `wp plugin install debug-bar wp-author-widget`
    And I run `wp plugin activate --all`

    When I run `wp doctor check plugin-active-count --config=config.yml`
    Then STDOUT should be a table containing rows:
      | name                | status  | message                                             |
      | plugin-active-count | warning | Number of active plugins (4) exceeds threshold (3). |

  Scenario: Include network-enabled plugins in active plugin count
    Given a WP multisite installation
    And I run `wp plugin activate --network --all`

    When I run `wp doctor check plugin-active-count`
    Then STDOUT should be a table containing rows:
      | name                | status  | message                                                   |
      | plugin-active-count | success | Number of active plugins (2) is less than threshold (80). |
