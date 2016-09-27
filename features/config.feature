Feature: Configure the Doctor

  Scenario: Error when config file doesn't exist
    Given an empty directory

    When I try `wp doctor list --config=foo.yml`
    Then STDERR should be:
      """
      Error: Invalid configuration file.
      """

  Scenario: Error when a check class doesn't exist
    Given an empty directory
    And a config.yml file:
      """
      undefined-class:
        class: Class_Undefined
      """

    When I try `wp doctor check --all --config=config.yml`
    Then STDERR should be:
      """
      Error: Check class for 'undefined-class' doesn't exist. Verify check registration.
      """

  Scenario: Error when a check class doesn't extend our base class
    Given an empty directory
    And a config.yml file:
      """
      invalid-class:
        class: WP_CLI
      """

    When I try `wp doctor check --all --config=config.yml`
    Then STDERR should be:
      """
      Error: Check class for 'invalid-class' needs to extend Check base class. Verify check registration.
      """

  Scenario: Error when an invalid check name is provided
    Given an empty directory
    And a config.yml file:
      """
      check space:
        class: runcommand\Doctor\Checks\Constant_Definition
      """

    When I try `wp doctor check --all --config=config.yml`
    Then STDERR should be:
      """
      Error: Check name 'check space' is invalid. Verify check registration.
      """
