Feature: Check whether WordPress is up to date

  Scenario: WordPress is up to date
    Given a WP install

    When I run `wp doctor check core-update`
    Then STDOUT should be a table containing rows:
      | name          | status  | message                                   |
      | core-update   | success | WordPress is at the latest version.       |

  Scenario: WordPress has a new minor version but no new major version
    Given a WP install
    And I run `wp core download --version=4.5.1 --force`

    When I run `wp doctor check core-update`
    Then STDOUT should be a table containing rows:
      | name          | status  | message                                   |
      | core-update   | error   | Updating to WordPress' newest minor version is strongly recommended. |

  Scenario: WordPress has a new major version but no new minor version
    Given a WP install
    And I run `wp core download --version=4.4.4 --force`

    When I run `wp doctor check core-update`
    Then STDOUT should be a table containing rows:
      | name          | status  | message                                             |
      | core-update   | warning | A new major version of WordPress is available for update. |
