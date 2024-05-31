Feature: Check whether languages are up to date

  Scenario: Verify check description
    Given an empty directory

    When I run `wp doctor list --fields=name,description`
    Then STDOUT should be a table containing rows:
      | name            | description                                      |
      | language-update | Warns when there are language updates available. |

  Scenario: Languages are up to date
    Given a WP install

    When I run `wp doctor check language-update`
    Then STDOUT should be a table containing rows:
      | name            | status  | message                   |
      | language-update | success | Languages are up to date. |

  @require-php-5.6 @less-than-php-7.0
  Scenario Outline: Two languages have updates available
    Given an empty directory
    And WP files
    And a database
    And I run `wp core download --version=<original> --force`
    And wp-config.php
    And I run `wp core install --url='localhost:8001' --title='Test' --admin_user=wpcli --admin_email=admin@example.com --admin_password=1`

    When I run `wp language core list --fields=language,status,update`
    Then STDOUT should be a table containing rows:
      | language | status      | update    |
      | ar       | uninstalled | none      |
      | en_CA    | uninstalled | none      |
      | en_US    | active      | none      |
      | ja       | uninstalled | none      |

    When I run `wp language core install en_CA ja`
    Then the wp-content/languages/admin-en_CA.po file should exist
    And the wp-content/languages/en_CA.po file should exist
    And the wp-content/languages/admin-ja.po file should exist
    And the wp-content/languages/ja.po file should exist
    And STDOUT should contain:
      """
      Success: Installed 2 of 2 languages.
      """
    And STDERR should be empty

    Given I try `wp core download --version=<update> --force`
    Then the return code should be 0
    And I run `wp core update-db`

    When I run `wp language core list --fields=language,status,update`
    Then STDOUT should be a table containing rows:
      | language | status      | update    |
      | ar       | uninstalled | none      |
      | en_CA    | installed   | available |
      | en_US    | active      | none      |
      | ja       | installed   | available |

    When I run `wp doctor check language-update`
    Then STDOUT should be a table containing rows:
      | name            | status  | message                             |
      | language-update | warning | 2 languages have updates available. |

    Examples:
      | original | update |
      | 4.8      | 4.9    |
      | 4.0.1    | 4.2    |
