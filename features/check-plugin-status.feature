Feature: Check the status of a plugin

  Scenario: Verify check description
    Given a WP install
    And a config.yml file:
      """
      plugin-akismet-active:
        class: WP_CLI\Doctor\Check\Plugin_Status
        options:
          name: akismet
          status: active
      """

    When I run `wp doctor list --fields=name,description --config=config.yml`
    Then STDOUT should be a table containing rows:
      | name                       | description                                                                    |
      | plugin-akismet-active      | Errors if plugin 'akismet' isn't in the expected state 'active'.               |

  Scenario: Basic usage
    Given a WP install
    And I run `wp plugin install https://github.com/wp-cli/sample-plugin/archive/refs/heads/master.zip`
    And a config.yml file:
      """
      plugin-akismet-active:
        class: WP_CLI\Doctor\Check\Plugin_Status
        options:
          name: akismet
          status: active
      plugin-sample-plugin-uninstalled:
        class: WP_CLI\Doctor\Check\Plugin_Status
        options:
          name: sample-plugin
          status: uninstalled
      """

    When I try `wp doctor check --all --config=config.yml`
    Then STDOUT should be a table containing rows:
      | name                             | status | message                                                                |
      | plugin-akismet-active            | error  | Plugin 'akismet' is 'inactive' but expected to be 'active'.            |
      | plugin-sample-plugin-uninstalled | error  | Plugin 'sample-plugin' is 'inactive' but expected to be 'uninstalled'. |
    And STDERR should contain:
      """
      Error: 2 checks report 'error'.
      """
    And the return code should be 1

    When I run `wp plugin activate akismet`
    Then STDOUT should not be empty

    When I try `wp doctor check --all --config=config.yml`
    Then STDOUT should be a table containing rows:
      | name                             | status  | message                                                                |
      | plugin-akismet-active            | success | Plugin 'akismet' is 'active' as expected.                              |
      | plugin-sample-plugin-uninstalled | error   | Plugin 'sample-plugin' is 'inactive' but expected to be 'uninstalled'. |
    And STDERR should contain:
      """
      Error: 1 check reports 'error'.
      """
    And the return code should be 1

    When I run `wp plugin delete sample-plugin`
    Then STDOUT should not be empty

    When I run `wp doctor check --all --config=config.yml`
    Then STDOUT should be a table containing rows:
      | name                             | status  | message                                              |
      | plugin-akismet-active            | success | Plugin 'akismet' is 'active' as expected.            |
      | plugin-sample-plugin-uninstalled | success | Plugin 'sample-plugin' is 'uninstalled' as expected. |

  Scenario: Invalid status registered
    Given a WP install
    And a config.yml file:
      """
      plugin-akismet-active-network:
        class: WP_CLI\Doctor\Check\Plugin_Status
        options:
          name: akismet
          status: active-network
      """

    When I try `wp doctor check plugin-akismet-active-network --config=config.yml`
    Then STDERR should be:
      """
      Error: Invalid plugin_status. Should be one of: uninstalled, installed, active.
      """
