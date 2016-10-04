Feature: Check files in a WordPress install

  Scenario: Check for use of eval(base64_decode()) in files
    Given a WP install

    When I run `wp doctor check file-eval`
    Then STDOUT should be a table containing rows:
      | name          | status    | message                                                       |
      | file-eval     | success   | All 'php' files passed check for 'eval\(.*base64_decode\(.*'. |

    Given a wp-content/mu-plugins/exploited.php file:
      """
      <?php
      eval( base64_decode( $_POST ) );
      """

    When I run `wp doctor check file-eval`
    Then STDOUT should be a table containing rows:
      | name          | status    | message                                                      |
      | file-eval     | error     | 1 'php' file failed check for 'eval\(.*base64_decode\(.*'.   |
