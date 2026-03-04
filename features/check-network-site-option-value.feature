Feature: Check the value of a network option

  Scenario: Verify check description
    Given an empty directory
    And a config.yml file:
      """
      network-registration:
        check: Network_Site_Option_Value
        options:
          option: registration
          value: none
      """

    When I try `wp doctor list --fields=name,description --config=config.yml`
    Then STDOUT should be a table containing rows:
      | name                 | description                                                     |
      | network-registration | Confirms the expected value of the network option 'registration'. |

  Scenario: Check the value of a network option
    Given a WP multisite installation
    And a config.yml file:
      """
      network-registration:
        check: Network_Site_Option_Value
        options:
          option: registration
          value: all
      """
    And I run `wp eval 'update_site_option( "registration", "none" );'`

    When I try `wp doctor check network-registration --config=config.yml`
    Then STDOUT should be a table containing rows:
      | name                 | status  | message                                                              |
      | network-registration | error   | Network option 'registration' is 'none' but expected to be 'all'.    |
    And STDERR should contain:
      """
      Error: 1 check reports 'error'.
      """
    And the return code should be 1

    When I run `wp eval 'update_site_option( "registration", "all" );'`
    Then STDOUT should be empty

    When I run `wp doctor check network-registration --config=config.yml`
    Then STDOUT should be a table containing rows:
      | name                 | status  | message                                                  |
      | network-registration | success | Network option 'registration' is 'all' as expected.      |

  Scenario: Check value_is_not for a network option
    Given a WP multisite installation
    And a config.yml file:
      """
      network-registration:
        check: Network_Site_Option_Value
        options:
          option: registration
          value_is_not: none
      """
    And I run `wp eval 'update_site_option( "registration", "none" );'`

    When I try `wp doctor check network-registration --config=config.yml`
    Then STDOUT should be a table containing rows:
      | name                 | status  | message                                                             |
      | network-registration | error   | Network option 'registration' is 'none' and expected not to be.     |
    And STDERR should contain:
      """
      Error: 1 check reports 'error'.
      """
    And the return code should be 1

    When I run `wp eval 'update_site_option( "registration", "all" );'`
    Then STDOUT should be empty

    When I run `wp doctor check network-registration --config=config.yml`
    Then STDOUT should be a table containing rows:
      | name                 | status  | message                                                        |
      | network-registration | success | Network option 'registration' is not 'none' as expected.      |
