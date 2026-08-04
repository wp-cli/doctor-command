Feature: Check the size of autoloaded transients

  Scenario: Verify check description
    Given an empty directory

    When I run `wp doctor list --fields=name,description`
    Then STDOUT should be a table containing rows:
      | name            | description                                                                      |
      | transients-size | Warns when autoloaded transients size exceeds threshold of 900 kb.               |

  Scenario: Autoloaded transients are less than 900 kb
    Given a WP install

    When I run `wp doctor check transients-size --fields=name,status`
    Then STDOUT should be a table containing rows:
      | name            | status  |
      | transients-size | success |

    When I run `wp doctor check transients-size --fields=message`
    Then STDOUT should contain:
      """
      is less than threshold (900kb)
      """

  Scenario: Autoloaded transients are greater than 900 kb
    Given a WP install
    And a create-large-transients.php file:
      """
      <?php
      $value = str_repeat( '9', 15000 );
      for ( $i = 0; $i < 75; $i++ ) {
        add_option( '_transient_doctor_big_' . $i, $value, '', true );
      }
      """
    And I run `wp eval-file create-large-transients.php`

    When I run `wp doctor check transients-size --fields=name,status`
    Then STDOUT should be a table containing rows:
      | name            | status  |
      | transients-size | warning |

    When I run `wp doctor check transients-size --fields=message`
    Then STDOUT should contain:
      """
      exceeds threshold (900kb)
      """

  Scenario: Autoloaded options and expiring transients are ignored
    Given a WP install
    And a create-large-nonmatching-data.php file:
      """
      <?php
      $value = str_repeat( '9', 15000 );
      for ( $i = 0; $i < 75; $i++ ) {
        update_option( 'doctor_big_option_' . $i, $value, true );
        add_option( '_transient_doctor_expiring_big_' . $i, $value, '', false );
        add_option( '_transient_timeout_doctor_expiring_big_' . $i, time() + HOUR_IN_SECONDS, '', false );
      }
      """
    And I run `wp eval-file create-large-nonmatching-data.php`

    When I run `wp doctor check transients-size --fields=name,status`
    Then STDOUT should be a table containing rows:
      | name            | status  |
      | transients-size | success |

  Scenario: Custom configuration
    Given a WP install
    And a custom.yml file:
      """
      transients-size:
        class: WP_CLI\Doctor\Check\Transients_Size
        options:
          threshold_kb: 800
      """

    When I run `wp doctor check transients-size --fields=message --config=custom.yml`
    Then STDOUT should contain:
      """
      is less than threshold (800kb)
      """

  Scenario: Zero threshold is formatted safely
    Given a WP install
    And a custom.yml file:
      """
      transients-size:
        class: WP_CLI\Doctor\Check\Transients_Size
        options:
          threshold_kb: 0
      """

    When I run `wp doctor check transients-size --fields=message --config=custom.yml`
    Then STDOUT should contain:
      """
      threshold (0)
      """

  Scenario: Very large thresholds are formatted safely
    Given a WP install
    And a custom.yml file:
      """
      transients-size:
        class: WP_CLI\Doctor\Check\Transients_Size
        options:
          threshold_kb: 1099511627776
      """

    When I run `wp doctor check transients-size --fields=message --config=custom.yml`
    Then STDOUT should contain:
      """
      is less than threshold (1024t)
      """
