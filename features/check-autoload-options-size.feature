Feature: Check the size of autoloaded options

  Scenario: Verify check description
    Given an empty directory

    When I run `wp doctor list --fields=name,description`
    Then STDOUT should be a table containing rows:
      | name                       | description                                                                    |
      | autoload-options-size      | Warns when autoloaded options size exceeds threshold of 900 kb.                |

  Scenario: Autoloaded options are less than 900 kb
    Given a WP install

    When I run `wp doctor check autoload-options-size --fields=name,status`
    Then STDOUT should be a table containing rows:
      | name                    | status  |
      | autoload-options-size   | success |

    When I run `wp doctor check autoload-options-size --fields=message`
    Then STDOUT should contain:
      """
      is less than threshold (900kb)
      """

  @less-than-wp-6.5
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
      exceeds threshold (900kb)
      """

  Scenario: Custom configuration
    Given a WP install
    And a custom.yml file:
      """
      autoload-options-size:
        class: WP_CLI\Doctor\Check\Autoload_Options_Size
        options:
          threshold_kb: 800
      """

    When I run `wp doctor check autoload-options-size --fields=message --config=custom.yml`
    Then STDOUT should contain:
      """
      is less than threshold (800kb)
      """
