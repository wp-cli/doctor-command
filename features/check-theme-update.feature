Feature: Check whether themes are up to date

  Scenario: Verify check description
    Given an empty directory

    When I run `wp doctor list --fields=name,description`
    Then STDOUT should be a table containing rows:
      | name                       | description                                                                    |
      | theme-update               | Warns when there are theme updates available.                                  |

  Scenario: Themes are up to date
    Given a WP install
    And I run `wp theme update --all`

    When I run `wp doctor check theme-update`
    Then STDOUT should be a table containing rows:
      | name          | status  | message                                 |
      | theme-update  | success | Themes are up to date.                  |

  Scenario: One theme has an update available
    Given a WP install
    And I run `wp theme update --all`
    And I run `wp theme install moina --version=1.1.2`

    When I run `wp doctor check theme-update`
    Then STDOUT should be a table containing rows:
      | name          | status  | message                                 |
      | theme-update  | warning | 1 theme has an update available.       |
