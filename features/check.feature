Feature: Basic check usage

  Scenario: Use --spotlight to focus on warnings and errors
    Given a WP install
    And I run `wp plugin activate --all`
    And I run `wp plugin update --all`

    When I run `wp doctor list --format=count`
    Then save STDOUT as {CHECK_COUNT}

    When I run `wp doctor check --all --spotlight`
    Then STDOUT should be:
      """
      Success: All {CHECK_COUNT} checks report 'success'.
      """

    When I run `wp doctor check plugin-deactivated --spotlight`
    Then STDOUT should be:
      """
      Success: The check reports 'success'.
      """

    When I run `wp doctor check --all --spotlight --format=json`
    Then STDOUT should be:
      """
      []
      """

  Scenario: Error when no checks nor --all are provided
    Given a WP install

    When I try `wp doctor check`
    Then STDERR should be:
      """
      Error: Please specify one or more checks, or use --all.
      """
    And the return code should be 1

  Scenario: Error when an invalid check is provided.
    Given a WP install
    And a config.yml file:
      """
      """

    When I try `wp doctor check invalid-check`
    Then STDERR should be:
      """
      Error: Invalid check.
      """
    And the return code should be 1

    When I try `wp doctor check invalid-check invalid-check2`
    Then STDERR should be:
      """
      Error: Invalid checks.
      """
    And the return code should be 1

    When I try `wp doctor check --all --config=config.yml`
    Then STDERR should be:
      """
      Error: No checks registered.
      """
    And the return code should be 1

  Scenario: List all default checks
    Given a WP install

    When I run `wp doctor list --fields=name`
    Then STDOUT should be a table containing rows:
      | name                  |
      | autoload-options-size |
      | core-update           |
      | core-verify-checksums |
      | plugin-deactivated    |
      | plugin-update         |
      | theme-update          |

  Scenario: Discard redirects emitted by WordPress
    Given a WP install
    And a wp-content/mu-plugins/redirect.php file:
      """
      <?php
      add_action( 'template_redirect', function(){
        wp_redirect( 'http://google.com' );
        exit;
      });
      """

    When I run `wp doctor check autoload-options-size --fields=name,status`
    Then STDERR should contain:
      """
      Warning: Incomplete check execution. Some code is trying to do a URL redirect. Backtrace:
      """
    And STDOUT should be a table containing rows:
      | name                   | status             |
      | autoload-options-size  | success            |
