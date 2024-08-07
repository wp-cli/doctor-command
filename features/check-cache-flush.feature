Feature: Check if wp_cache_flush() function is used inside wp-content directory

  Scenario: Detect wp_cache_flush()
    Given a WP install
    And a wp-content/mu-plugins/plugin.php file:
      """
      <?php
      wp_cache_flush();
      """

    When I run `wp doctor check cache-flush`
    Then STDOUT should contain:
      """
      cache-flush
      """
    And STDOUT should contain:
      """
      warning
      """
    And STDOUT should contain:
      """
      Use of wp_cache_flush() detected
      """
    And STDOUT should contain:
      """
      mu-plugins/plugin.php
      """
