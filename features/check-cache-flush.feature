Feature: Check if wp_cache_flush() function is used inside wp-content directory

  Scenario: Detect wp_cache_flush()
    Given a wp-content/mu-plugins/plugin.php file:
      """
      <?php
      wp_cache_flush();
      """

    When I run `wp doctor check cache-flush`
    Then STDOUT should be a table containing rows:
      | name        | status  | message                                 |
      | cache-flush | success | 1 occurrence of wp_cache_flush() found. |
