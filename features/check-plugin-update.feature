Feature: Check whether plugins are up to date

  Scenario: Plugins are up to date
    Given a WP install

    When I run `wp doctor check plugin-update`
    Then STDOUT should be a table containing rows:
      | name          | status  | message                                 |
      | plugin-update | success | Plugins are up to date.                 |

  Scenario: One plugin has an update available
    Given a WP install
    And I run `wp plugin install akismet --version=3.1.10 --force`

    When I run `wp doctor check plugin-update`
    Then STDOUT should be a table containing rows:
      | name          | status  | message                                 |
      | plugin-update | warning | 1 plugin has an update available.       |
