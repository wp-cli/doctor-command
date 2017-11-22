Feature: Check whether themes are up to date

  Scenario: Verify check description
    Given an empty directory

    When I run `wp doctor list --fields=name,description`
    Then STDOUT should be a table containing rows:
      | name                       | description                                                                    |
      | theme-update               | Warns when there are theme updates available.                                  |

  Scenario: Themes are up to date
    Given a WP install

    When I run `wp doctor check theme-update`
    Then STDOUT should be a table containing rows:
      | name          | status  | message                                 |
      | theme-update  | success | Themes are up to date.                  |

  Scenario: One theme has an update available with recommendations
    Given a WP install
    And I run `wp theme install p2 --version=1.5.1`

    When I run `wp doctor check theme-update --fields=name,status,message,recommendation_message,recommendation_command`
    Then STDOUT should be a table containing rows:
      | name          | status  | message                                | recommendation_message | recommendation_command |
      | theme-update  | warning | 1 theme has an update available.       | Update the p2 theme.   | wp theme update p2     |
