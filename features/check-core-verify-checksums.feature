Feature: Check whether WordPress core verifies against its checksums

  Scenario: Verify check description
    Given an empty directory

    When I run `wp doctor list --fields=name,description`
    Then STDOUT should be a table containing rows:
      | name                       | description                                                                    |
      | core-verify-checksums      | Verifies WordPress files against published checksums; errors on failure.       |

  Scenario: WordPress verifies against checksums
    Given a WP install

    When I run `wp doctor check core-verify-checksums`
    Then STDOUT should be a table containing rows:
      | name                  | status  | message                                    |
      | core-verify-checksums | success | WordPress verifies against its checksums.  |

  Scenario: WordPress doesn't verify against checksums
    Given a WP install

    When I run `sed -i.bak s/WordPress/Wordpress/g readme.html`
    Then STDERR should be empty

    When I try `wp doctor check core-verify-checksums`
    Then STDOUT should be a table containing rows:
      | name                  | status  | message                                         |
      | core-verify-checksums | error   | WordPress doesn't verify against its checksums. |
    And STDERR should be:
      """
      Error: 1 check reports 'error'.
      """
    And the return code should be 1
