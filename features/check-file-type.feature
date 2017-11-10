Feature: Check the type of file

  Scenario: Check that object-cache.php isn't a symlink
    Given a WP install
    And a config.yml file:
      """
      file-object-cache-symlink:
        check: File_Type
        options:
          path: wp-content/object-cache.php
          symlink: false
      """

    When I run `wp doctor check file-object-cache-symlink --config=config.yml`
    Then STDOUT should be a table containing rows:
      | name                      | status    | message                                                       |
      | file-object-cache-symlink | success   | All 'php' files passed assertion that symlink is 'false'.     |

    Given a wp-content/test.php file:
      """
      <?php
      """
    And I run `ln -s wp-content/test.php wp-content/object-cache.php`

    When I try `wp doctor check file-object-cache-symlink --config=config.yml`
    Then STDOUT should be a table containing rows:
      | name                      | status    | message                                                       |
      | file-object-cache-symlink | error     | 1 'php' file failed assertion that symlink is 'false'.        |
    And the return code should be 1


  Scenario: Check that object-cache.php is a symlink
    Given a WP install
    And a config.yml file:
      """
      file-object-cache-symlink:
        check: File_Type
        options:
          path: wp-content/object-cache.php
          symlink: true
      """
    And a wp-content/object-cache.php file:
      """
      <?php
      """

    When I try `wp doctor check file-object-cache-symlink --config=config.yml`
    Then STDOUT should be a table containing rows:
      | name                      | status    | message                                                       |
      | file-object-cache-symlink | error     | 1 'php' file failed assertion that symlink is 'true'.         |
    And the return code should be 1

    Given a wp-content/test.php file:
      """
      <?php
      """
    And I run `rm wp-content/object-cache.php`
    And I run `ln -s wp-content/test.php wp-content/object-cache.php`

    When I run `wp doctor check file-object-cache-symlink --config=config.yml`
    Then STDOUT should be a table containing rows:
      | name                      | status    | message                                                       |
      | file-object-cache-symlink | success   | All 'php' files passed assertion that symlink is 'true'.      |
