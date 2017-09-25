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
      | name         | status  | message                   |
      | theme-update | success | Languages are up to date. |

  Scenario: One language has an update available
    Given a WP install
      And a wp-content/languages/custom.po file:
        """
        # Translation of WordPress - 4.8.x in Japanese
        # This file is distributed under the same license as the WordPress - 4.8.x package.
        msgid ""
        msgstr ""
        "PO-Revision-Date: 2016-08-03 23:23:50+0000\n"
        "MIME-Version: 1.0\n"
        "Content-Type: text/plain; charset=UTF-8\n"
        "Content-Transfer-Encoding: 8bit\n"
        "Plural-Forms: nplurals=1; plural=0;\n"
        "X-Generator: GlotPress/2.4.0-alpha\n"
        "Language: ja_JP\n"
        "Project-Id-Version: WordPress - 4.8.x\n"
        """

      When I run `wp language core install ja`
      And I run `wp-content/languages/custom.po > wp-content/languages/ja.po`

      When I run `wp doctor check language-update`
      Then STDOUT should be a table containing rows:
        | name         | status  | message                             |
        | theme-update | warning | 1 language has an update available. |
