Feature: Basic check usage

  Scenario: Error when no checks nor --all are provided
    Given a WP install

    When I try `wp doctor check`
    Then STDERR should be:
      """
      Error: Please specify one or more checks, or use --all.
      """
