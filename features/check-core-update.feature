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

  # This test downgrades to WordPress 5.9.1, but the SQLite plugin requires 6.0+
  @requires-mysql
  Scenario: WordPress has a new minor version but no new major version
    Given a WP install
    And I run `wp core download --version=5.9.1 --force`
    And I run `wp theme activate twentytwenty`

    When I try `wp doctor check core-update`
    Then STDOUT should be a table containing rows:
      | name          | status  | message                                   |
      | core-update   | error   | Updating to WordPress' newest minor version is strongly recommended. |
    And STDERR should contain:
      """
      Error: 1 check reports 'error'.
      """
    And the return code should be 1

  # This test downgrades to WordPress 5.9.1, but the SQLite plugin requires 6.0+
  @requires-mysql
  Scenario: WordPress has a new major version but no new minor version
    Given a WP install
    And I run `wp core download --version=5.9.1 --force`
    And I run `wp theme activate twentytwenty`

    When I try `wp doctor check core-update`
    Then STDOUT should be a table containing rows:
      | name          | status  | message                                             |
      | core-update   | error   | Updating to WordPress' newest minor version is strongly recommended. |
    And STDERR should contain:
      """
      Error: 1 check reports 'error'.
      """
    And the return code should be 1
