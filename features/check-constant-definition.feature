Feature: Check the values of defined constants

  Scenario: WP_DEBUG is defined to false
    Given a WP install
    And a wp-debug-true.php file:
      """
      <?php
      define( 'WP_DEBUG', true );
      """

    When I run `wp doctor check constant-wp-debug-false`
    Then STDOUT should be a table containing rows:
      | name                       | status  | message                                    |
      | constant-wp-debug-false    | success | Constant 'WP_DEBUG' is defined 'false'.    |

    When I run `wp doctor check constant-wp-debug-false --require=wp-debug-true.php`
    Then STDOUT should be a table containing rows:
      | name                       | status  | message                                    |
      | constant-wp-debug-false    | error   | Constant 'WP_DEBUG' is defined 'true' but expected to be 'false'.  |

  Scenario: Expected constant is defined
    Given a WP install
    And a config.yml file:
      """
      constant-db-host-defined:
        class: runcommand\Doctor\Checks\Constant_Definition
        options:
          constant: DB_HOST
          defined: true
      """

    When I run `wp doctor check constant-db-host-defined --config=config.yml`
    Then STDOUT should be a table containing rows:
      | name                     | status    | message                               |
      | constant-db-host-defined | success   | Constant 'DB_HOST' is defined.        |

  Scenario: Expected constant is missing
    Given a WP install
    And a config.yml file:
      """
      constant-foobar-true:
        class: runcommand\Doctor\Checks\Constant_Definition
        options:
          constant: FOOBAR
          expected_value: true
      """

    When I run `wp doctor check constant-foobar-true --config=config.yml`
    Then STDOUT should be a table containing rows:
      | name                 | status  | message                                                   |
      | constant-foobar-true | error   | Constant 'FOOBAR' is undefined but expected to be 'true'. |
