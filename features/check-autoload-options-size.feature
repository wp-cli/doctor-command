Feature: Check the size of autoloaded options

  Scenario: Autoloaded options are less than 900 kb
    Given a WP install

    When I run `wp doctor check autoload-options-size --fields=name,status`
    Then STDOUT should be a table containing rows:
      | name                    | status  |
      | autoload-options-size   | success |

    When I run `wp doctor check autoload-options-size --fields=message`
    Then STDOUT should contain:
      """
      is less than threshold
      """

  Scenario: Autoloaded options are greater than 900 kb
    Given a WP install
    And a explode-options.php file:
      """
      <?php
      $value = str_pad( '9', 15000, '9' );
      for( $i = 0; $i < 75; $i++ ) {
        update_option( 'foobar' . $i, $value );
      }
      """
    And I run `wp eval-file explode-options.php`

    When I run `wp doctor check autoload-options-size --fields=name,status`
    Then STDOUT should be a table containing rows:
      | name                    | status  |
      | autoload-options-size   | warning |

    When I run `wp doctor check autoload-options-size --fields=message`
    Then STDOUT should contain:
      """
      exceeds threshold
      """
