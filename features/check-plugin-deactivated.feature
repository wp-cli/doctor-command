Feature: Check whether a high percentage of plugins are deactivated

  Scenario: Verify check description
    Given an empty directory

    When I run `wp doctor list --fields=name,description`
    Then STDOUT should be a table containing rows:
      | name                       | description                                                                    |
      | plugin-deactivated         | Warns when greater than 40% of plugins are deactivated.                        |

  Scenario: All plugins are activated
    Given a WP install
    And I run `wp plugin install user-switching rewrite-rules-inspector`
    And I run `wp plugin activate --all`

    When I run `wp doctor check plugin-deactivated`
    Then STDOUT should be a table containing rows:
      | name               | status  | message                                          |
      | plugin-deactivated | success | Less than 40 percent of plugins are deactivated. |

  Scenario: Too many plugins are deactivated
    Given a WP install
    And I run `wp plugin install user-switching rewrite-rules-inspector`

    When I run `wp doctor check plugin-deactivated`
    Then STDOUT should be a table containing rows:
      | name               | status  | message                                          |
      | plugin-deactivated | warning | Greater than 40 percent of plugins are deactivated. |

  Scenario: Custom percentage of deactivated plugins
    Given a WP install
    And a custom.yml file:
      """
      plugin-deactivated:
        class: WP_CLI\Doctor\Check\PluginDeactivated
        options:
          threshold_percentage: 60
      """
    And I run `wp plugin install user-switching rewrite-rules-inspector`

    When I run `wp doctor check plugin-deactivated --config=custom.yml`
    Then STDOUT should be a table containing rows:
      | name               | status  | message                                          |
      | plugin-deactivated | warning | Greater than 60 percent of plugins are deactivated. |
