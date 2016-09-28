Feature: Basic check usage

  Scenario: Use --spotlight to focus on warnings and errors
    Given a WP install
    And I run `wp plugin activate --all`

    When I run `wp doctor check --all --spotlight`
    Then STDOUT should be:
      """
      Success: All 10 checks report 'success'.
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

    When I try `wp doctor check invalid-check invalid-check2`
    Then STDERR should be:
      """
      Error: Invalid checks.
      """

    When I try `wp doctor check --all --config=config.yml`
    Then STDERR should be:
      """
      Error: No checks registered.
      """

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
