Feature: Check for presence of .php files in the uploads folder

  Scenario: Detect PHP files
    Given a WP install
    And a wp-content/uploads/malicious.php file:
      """
      <?php some_malicious_code(); ?>
      """

    When I run `wp doctor check php-in-upload --fields=name,status,message,recommendation_message,recommendation_command`
    Then STDOUT should be a table containing rows:
      | name          | status  | message                                   | recommendation_message | recommendation_command |
      | php-in-upload | warning | PHP files detected in the Uploads folder. | The following PHP files were found in wp-content/uploads:\n- malicious.php | |
