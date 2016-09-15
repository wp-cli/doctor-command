Feature: Check whether a high percentage of plugins are deactivated

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
