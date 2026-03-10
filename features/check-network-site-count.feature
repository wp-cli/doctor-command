Feature: Check multisite network site count

  Scenario: Verify check description
    Given an empty directory
    And a config.yml file:
      """
      network-site-count:
        check: Network_Site_Count
      """

    When I try `wp doctor list --fields=name,description --config=config.yml`
    Then STDOUT should be a table containing rows:
      | name               | description                                                                  |
      | network-site-count | Warns when multisite network site count is outside the range 1-500.         |

  Scenario: Site count is within expected range
    Given a WP multisite installation
    And a config.yml file:
      """
      network-site-count:
        check: Network_Site_Count
        options:
          minimum: 1
          maximum: 10
      """

    When I run `wp doctor check network-site-count --config=config.yml`
    Then STDOUT should be a table containing rows:
      | name               | status  | message                                         |
      | network-site-count | success | Network has 1 sites; expected between 1 and 10. |

  Scenario: Site count is outside expected range
    Given a WP multisite installation
    And a config.yml file:
      """
      network-site-count:
        check: Network_Site_Count
        options:
          minimum: 2
          maximum: 10
      """

    When I run `wp doctor check network-site-count --config=config.yml`
    Then STDOUT should be a table containing rows:
      | name               | status  | message                                         |
      | network-site-count | warning | Network has 1 sites; expected between 2 and 10. |
