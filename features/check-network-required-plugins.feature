Feature: Check required network plugins

  Scenario: Verify check description
    Given an empty directory
    And a config.yml file:
      """
      network-required-plugins:
        check: Network_Required_Plugins
        options:
          plugins:
            - akismet
      """

    When I try `wp doctor list --fields=name,description --config=config.yml`
    Then STDOUT should be a table containing rows:
      | name                     | description                                                  |
      | network-required-plugins | Errors when required plugins are not network-activated.      |

  Scenario: Required plugin is not network-activated
    Given a WP multisite installation
    And a config.yml file:
      """
      network-required-plugins:
        check: Network_Required_Plugins
        options:
          plugins:
            - akismet
      """

    When I try `wp doctor check network-required-plugins --config=config.yml`
    Then STDOUT should be a table containing rows:
      | name                     | status | message                                                                                |
      | network-required-plugins | error  | Required network plugin check failed. Not network-activated: akismet (inactive).      |
    And STDERR should contain:
      """
      Error: 1 check reports 'error'.
      """
    And the return code should be 1

  Scenario: Required plugin is network-activated
    Given a WP multisite installation
    And a config.yml file:
      """
      network-required-plugins:
        check: Network_Required_Plugins
        options:
          plugins:
            - akismet
      """
    And I run `wp plugin activate akismet --network`

    When I run `wp doctor check network-required-plugins --config=config.yml`
    Then STDOUT should be a table containing rows:
      | name                     | status  | message                                            |
      | network-required-plugins | success | All required plugins are network-activated.        |
