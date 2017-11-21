Feature: Check total number of cron entries

  Background:
    Given a WP install

  Scenario: Verify check description
    When I run `wp doctor list --fields=name,description`
    Then STDOUT should be a table containing rows:
      | name                       | description                                                                    |
      | cron-count                 | Errors when there's an excess of 50 total cron jobs registered.                |

  Scenario: Cron check is healthy against a normal WordPress install
    When I run `wp doctor check cron-count`
    Then STDOUT should be a table containing rows:
      | name            | status  | message                                                            |
      | cron-count      | success | Total number of cron jobs is within normal operating expectations. |

  Scenario: Cron check errors with excess total crons
    Given a wp-content/mu-plugins/plugin.php file:
      """
      <?php
      for ( $i=0; $i < 55; $i++ ) {
          // WP Cron doesn't permit registering two at the same time
          // so we need to distribute these crons against a spread of time
          wp_schedule_event( time() + ( $i * 3 ), 'hourly', 'too_many_crons_hook' );
      }
      """

    When I try `wp doctor check cron-count`
    Then STDOUT should be a table containing rows:
      | name            | status  | message                                                 |
      | cron-count      | error   | Total number of cron jobs exceeds expected threshold.   |
    And STDERR should contain:
      """
      Error: 1 check reports 'error'.
      """
    And the return code should be 1
