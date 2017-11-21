Feature: Check for excess duplicate cron entries

  Background:
    Given a WP install
    And a wp-debug.php file:
      """
      <?php
      define( 'WP_DEBUG', true );
      """
    And a wp-cli.yml file:
      """
      require:
        - wp-debug.php
      """

  Scenario: Verify check description
    When I run `wp doctor list --fields=name,description`
    Then STDOUT should be a table containing rows:
      | name                       | description                                                                    |
      | cron-duplicates            | Errors when there's an excess of 10 duplicate cron jobs registered.            |

  Scenario: Cron check is healthy against a normal WordPress install
    When I run `wp doctor check cron-duplicates`
    Then STDOUT should be a table containing rows:
      | name            | status  | message                                                       |
      | cron-duplicates | success | All cron job counts are within normal operating expectations. |
    And STDERR should be empty

  Scenario: Cron check errors with excess duplicate crons
    Given a wp-content/mu-plugins/plugin.php file:
      """
      <?php
      for ( $i=0; $i < 55; $i++ ) {
          // WP Cron doesn't permit registering two at the same time
          // so we need to distribute these crons against a spread of time
          wp_schedule_event( time() + ( $i * 3 ), 'hourly', 'too_many_crons_hook' );
      }
      """

    When I try `wp doctor check cron-duplicates`
    Then STDOUT should be a table containing rows:
      | name            | status  | message                                          |
      | cron-duplicates | error   | Detected 10 or more of the same cron job.        |
    And STDERR should contain:
      """
      Error: 1 check reports 'error'.
      """
    And the return code should be 1
