Feature: Check the values of defined constants

  Scenario: Verify check description
    Given an empty directory

    When I run `wp doctor list --fields=name,description`
    Then STDOUT should be a table containing rows:
      | name                       | description                                                                    |
      | constant-savequeries-falsy | Confirms expected state of the SAVEQUERIES constant.                           |
      | constant-wp-debug-falsy    | Confirms expected state of the WP_DEBUG constant.                              |

  Scenario: WP_DEBUG is defined to false
    Given a WP install
    And a wp-debug-true.php file:
      """
      <?php
      define( 'WP_DEBUG', true );
      """

    When I run `wp doctor check constant-wp-debug-falsy`
    Then STDOUT should be a table containing rows:
      | name                       | status  | message                                    |
      | constant-wp-debug-falsy    | success | Constant 'WP_DEBUG' is defined falsy.      |

    When I try `wp doctor check constant-wp-debug-falsy --require=wp-debug-true.php`
    Then STDOUT should be a table containing rows:
      | name                       | status  | message                                    |
      | constant-wp-debug-falsy    | error   | Constant 'WP_DEBUG' is defined 'true' but expected to be falsy.  |
    And the return code should be 1

  Scenario: SAVEQUERIES is defined to falsy
    Given a WP install
    And a savequeries-false.php file:
      """
      <?php
      define( 'SAVEQUERIES', false );
      """
    And a savequeries-true.php file:
      """
      <?php
      define( 'SAVEQUERIES', true );
      """

    When I run `wp doctor check constant-savequeries-falsy`
    Then STDOUT should be a table containing rows:
      | name                        | status  | message                                          |
      | constant-savequeries-falsy  | success | Constant 'SAVEQUERIES' is undefined.             |

    When I run `wp doctor check constant-savequeries-falsy --require=savequeries-false.php`
    Then STDOUT should be a table containing rows:
      | name                        | status  | message                                          |
      | constant-savequeries-falsy  | success | Constant 'SAVEQUERIES' is defined falsy.         |

    When I try `wp doctor check constant-savequeries-falsy --require=savequeries-true.php`
    Then STDOUT should be a table containing rows:
      | name                        | status  | message                                                    |
      | constant-savequeries-falsy  | error   | Constant 'SAVEQUERIES' is defined 'true' but expected to be falsy. |
    And the return code should be 1

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
          value: true
      """

    When I try `wp doctor check constant-foobar-true --config=config.yml`
    Then STDOUT should be a table containing rows:
      | name                 | status  | message                                                   |
      | constant-foobar-true | error   | Constant 'FOOBAR' is undefined but expected to be 'true'. |
    And the return code should be 1

  Scenario: Expected constant is defined as the correct value
    Given a WP install
    And a config.yml file:
      """
      constant-foobar-true:
        class: runcommand\Doctor\Checks\Constant_Definition
        options:
          constant: FOOBAR
          value: true
      """
    And a wp-content/mu-plugins/constant.php file:
      """
      <?php
      define( 'FOOBAR', true );
      """

    When I run `wp doctor check constant-foobar-true --config=config.yml`
    Then STDOUT should be a table containing rows:
      | name                 | status  | message                               |
      | constant-foobar-true | success | Constant 'FOOBAR' is defined 'true'.  |
