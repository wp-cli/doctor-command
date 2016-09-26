Feature: Basic check usage

  Scenario: Error when no checks nor --all are provided
    Given a WP install

    When I try `wp doctor check`
    Then STDERR should be:
      """
      Error: Please specify one or more checks, or use --all.
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
