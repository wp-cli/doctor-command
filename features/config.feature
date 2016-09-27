Feature: Configure the Doctor

  Scenario: Error when config file doesn't exist
    Given a WP install

    When I try `wp doctor list --config=foo.yml`
    Then STDERR should be:
      """
      Error: Invalid configuration file.
      """
