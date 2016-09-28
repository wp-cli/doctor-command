Feature: Check whether a high number of plugins are activated

  Scenario: Less than threshold plugins are active
    Given a WP install
    And I run `wp plugin activate --all`

    When I run `wp doctor check plugin-active-count`
    Then STDOUT should be a table containing rows:
      | name                | status  | message                              |
      | plugin-active-count | success | Using less than 80 active plugins.   |

  Scenario: Greater than threshold plugins are active
    Given a WP install
    And a config.yml file:
      """
      plugin-active-count:
        class: runcommand\Doctor\Checks\Plugin_Active_Count
        options:
          threshold_count: 3
      """
    And I run `wp plugin install user-switching rewrite-rules-inspector`
    And I run `wp plugin activate --all`

    When I run `wp doctor check plugin-active-count --config=config.yml`
    Then STDOUT should be a table containing rows:
      | name                | status  | message                            |
      | plugin-active-count | warning | More than 3 plugins are active.    |
