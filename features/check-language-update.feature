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
