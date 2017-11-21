Feature: Check whether WordPress is up to date

  Scenario: Verify check description
    Given an empty directory

    When I run `wp doctor list --fields=name,description`
    Then STDOUT should be a table containing rows:
      | name                       | description                                                                    |
      | core-update                | Errors when new WordPress minor release is available; warns for major release. |

  Scenario: WordPress is up to date
    Given a WP install

    When I run `wp doctor check core-update`
    Then STDOUT should be a table containing rows:
      | name          | status  | message                                   |
      | core-update   | success | WordPress is at the latest version.       |

  Scenario: WordPress has a new minor version but no new major version
    Given a WP install
    And I run `wp core download --version=4.5.1 --force`
    And I run `wp theme activate twentyfifteen`

    When I try `wp doctor check core-update`
    Then STDOUT should be a table containing rows:
      | name          | status  | message                                   |
      | core-update   | error   | Updating to WordPress' newest minor version is strongly recommended. |
    And STDERR should be:
      """
      Error: 1 check reports 'error'.
      """
    And the return code should be 1

  Scenario: WordPress has a new major version but no new minor version
    Given a WP install
    And I run `wp core download --version=4.4.9 --force`
    And I run `wp theme activate twentyfifteen`

    When I try `wp doctor check core-update`
    Then STDOUT should be a table containing rows:
      | name          | status  | message                                             |
      | core-update   | error   | Updating to WordPress' newest minor version is strongly recommended. |
    And STDERR should be:
      """
      Error: 1 check reports 'error'.
      """
    And the return code should be 1
