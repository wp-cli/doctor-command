Feature: Check for presence of .php files in the uploads folder

  Scenario: Detect PHP files
    Given a WP install
    And a wp-content/uploads/malicious.php file:
      """
      <?php some_malicious_code(); ?>
      """

    When I run `wp doctor check php-in-upload`
    Then STDOUT should be a table containing rows:
      | name          | status  | message                                   |
      | php-in-upload | success | PHP files detected in the Uploads folder. |