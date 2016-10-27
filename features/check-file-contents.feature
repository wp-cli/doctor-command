Feature: Check files in a WordPress install

  Scenario: Verify check description
    Given an empty directory

    When I run `wp doctor list --fields=name,description`
    Then STDOUT should be a table containing rows:
      | name                       | description                                                                    |
      | file-eval                  | Checks files on the filesystem for regex pattern `eval\(.*base64_decode\(.*`.  |

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

  Scenario: Check for the use of sessions
    Given a WP install
    And a config.yml file:
      """
      file-sessions:
        check: File_Contents
        options:
          regex: .*(session_start|\$_SESSION).*
          only_wp_content: true
      """

    When I run `wp doctor check file-sessions --config=config.yml --format=json`
    Then STDOUT should be JSON containing:
      """
      [{"name":"file-sessions","status":"success","message":"All 'php' files passed check for '.*(session_start|\\$_SESSION).*'."}]
      """

    Given a wp-content/mu-plugins/sessions1.php file:
      """
      <?php
      session_start();
      """
    And a wp-content/mu-plugins/sessions2.php file:
      """
      <?php
      echo '';
      $_SESSION['foo'] = bar;
      """

    When I run `wp doctor check file-sessions --config=config.yml --format=json`
    Then STDOUT should be JSON containing:
      """
      [{"name":"file-sessions","status":"error","message":"2 'php' files failed check for '.*(session_start|\\$_SESSION).*'."}]
      """

  Scenario: Check for use of $_SERVER['SERVER_NAME'] in wp-config.php
    Given a WP install
    And a config.yml file:
      """
      file-server-name-wp-config:
        check: File_Contents
        options:
          regex: define\(.+WP_(HOME|SITEURL).+\$_SERVER.+SERVER_NAME
          path: wp-config.php
      """
    And a wp-content/mu-plugins/ignored-define.php file:
      """
      <?php
      @define( 'WP_SITEURL', $_SERVER['SERVER_NAME'] );
      """

    When I run `wp doctor check file-server-name-wp-config --config=config.yml --format=json`
    Then STDOUT should be JSON containing:
      """
      [{"name":"file-server-name-wp-config","status":"success","message":"All 'php' files passed check for 'define\\(.+WP_(HOME|SITEURL).+\\$_SERVER.+SERVER_NAME'."}]
      """

    Given a prepend-siteurl.php file:
      """
      <?php
      $contents = file_get_contents( dirname( __FILE__ ) . '/wp-config.php' );
      $contents = str_replace( '<?php', '<?php' . PHP_EOL . "@define( 'WP_SITEURL', \$_SERVER['SERVER_NAME'] );", $contents );
      file_put_contents( dirname( __FILE__ ) . '/wp-config.php', $contents );
      """

    When I run `wp eval-file prepend-siteurl.php --skip-wordpress`
    Then STDERR should be empty

    When I run `cat wp-config.php`
    Then STDOUT should contain:
      """
      <?php
      @define( 'WP_SITEURL', $_SERVER['SERVER_NAME'] );
      """

    When I run `wp doctor check file-server-name-wp-config --config=config.yml --format=json`
    Then STDOUT should be JSON containing:
      """
      [{"name":"file-server-name-wp-config","status":"error","message":"1 'php' file failed check for 'define\\(.+WP_(HOME|SITEURL).+\\$_SERVER.+SERVER_NAME'."}]
      """
