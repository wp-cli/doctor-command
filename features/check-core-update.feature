Feature: Check whether WordPress is up to date

  Scenario: Verify check description
    Given an empty directory

    When I run `wp doctor list --fields=name,description`
    Then STDOUT should be a table containing rows:
      | name                       | description                                                                    |
      | core-update                | Errors when new WordPress minor release is available; warns for major release. |

  Scenario: WordPress is up to date
    Given a WP install
    And a setup.php file:
      """
      <?php
      global $wp_version;

      $obj = new stdClass;
      $obj->updates = [];
      $obj->last_checked = strtotime( '1 January 2099' );
      $obj->version_checked = $wp_version;
      $obj->translations = [];
      set_site_transient( 'update_core', $obj );
      """
    And I run `wp eval-file setup.php`

    When I run `wp doctor check core-update`
    Then STDOUT should be a table containing rows:
      | name          | status  | message                                   |
      | core-update   | success | WordPress is at the latest version.       |

  @require-wp-6.0 @require-php-7.0
  Scenario: WordPress has a new minor version but no new major version
    Given a WP install
    And I run `wp theme delete --all --force`
    And I run `wp theme install twentytwelve --activate`

    When I run `(curl -s https://api.wordpress.org/core/version-check/1.7/ | jq -r ".offers[0].new_bundled" | cut -d '.' -f 1,2)`
    Then save STDOUT as {LATEST_WP_VERSION}

    When I run `wp core download --version={LATEST_WP_VERSION} --force`
    Then STDOUT should contain:
      """
      Success: WordPress downloaded.
      """

    When I try `wp doctor check core-update`
    Then STDOUT should be a table containing rows:
      | name          | status  | message                                                              |
      | core-update   | error   | Updating to WordPress' newest minor version is strongly recommended. |
    And STDERR should contain:
      """
      Error: 1 check reports 'error'.
      """
    And the return code should be 1

  @require-mysql
  Scenario: WordPress has a new major version but no new minor version
    Given a WP install
    And I run `wp theme delete --all --force`
    And I run `wp theme install twentytwelve --activate`
    And I run `wp core download --version=5.9.10 --force`

    When I run `wp core update --minor`
    Then STDOUT should contain:
      """
      Success: WordPress updated successfully.
      """
    And the return code should be 0

    When I try `wp doctor check core-update`
    Then STDOUT should be a table containing rows:
      | name          | status  | message                                                   |
      | core-update   | warning | A new major version of WordPress is available for update. |
    And the return code should be 0
