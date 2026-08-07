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
      does not exceed threshold (900kb)
      """

  Scenario: Autoloaded transients equal 900 kb
    Given a WP install
    And a wp-content/mu-plugins/exact-threshold-transients.php file:
      """
      <?php
      add_action(
        'wp_loaded',
        static function () {
          global $wpdb;

          $option_names = $wpdb->get_col( "SELECT option_name FROM {$wpdb->options}" );
          foreach ( $option_names as $option_name ) {
            if (
              0 === strpos( $option_name, '_transient_' )
              || 0 === strpos( $option_name, '_site_transient_' )
            ) {
              delete_option( $option_name );
            }
          }

          add_option( '_transient_doctor_exact_threshold', str_repeat( '9', 900 * 1024 ), '', true );
        },
        PHP_INT_MAX
      );
      """

    When I run `wp option list --transients --autoload=on --format=total_bytes`
    Then STDOUT should be:
      """
      921600
      """

    When I run `wp doctor check transients-size --fields=name,status`
    Then STDOUT should be a table containing rows:
      | name            | status  |
      | transients-size | success |

    When I run `wp doctor check transients-size --fields=message`
    Then STDOUT should contain:
      """
      Autoloaded transients size (900kb) does not exceed threshold (900kb).
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
      does not exceed threshold (800kb)
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
      does not exceed threshold (1024t)
      """
