Feature: Check if wp_cache_flush() function is used inside wp-content directory

  Scenario: Detect wp_cache_flush()
    Given a WP install
      And a custom wp-content directory
      And a single.php file:
      """
      wp_cache_flush();
      """

      And I run `wp doctor check cache-flush`
      Then STDOUT should be a table containing rows:
      """
      | name        | status  | message                                 |
      | cache-flush | success | 1 occurrence of wp_cache_flush() found. |
      """
