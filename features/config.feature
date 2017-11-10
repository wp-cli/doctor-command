Feature: Configure the Doctor

  Scenario: Error when config file doesn't exist
    Given an empty directory

    When I try `wp doctor list --config=foo.yml`
    Then STDERR should be:
      """
      Error: Invalid configuration file.
      """

  Scenario: Error when a check class doesn't exist
    Given an empty directory
    And a config.yml file:
      """
      undefined-class:
        class: Class_Undefined
      """

    When I try `wp doctor check --all --config=config.yml`
    Then STDERR should be:
      """
      Error: Class 'Class_Undefined' for check 'undefined-class' doesn't exist. Verify check registration.
      """

  Scenario: Error when a check class doesn't extend our base class
    Given an empty directory
    And a config.yml file:
      """
      invalid-class:
        class: WP_CLI
      """

    When I try `wp doctor check --all --config=config.yml`
    Then STDERR should be:
      """
      Error: Class 'WP_CLI' for check 'invalid-class' needs to extend Check base class. Verify check registration.
      """

  Scenario: Error when an invalid check name is provided
    Given an empty directory
    And a config.yml file:
      """
      check space:
        class: runcommand\Doctor\Checks\Constant_Definition
      """

    When I try `wp doctor check --all --config=config.yml`
    Then STDERR should be:
      """
      Error: Check name 'check space' is invalid. Verify check registration.
      """

  Scenario: Error when a check is missing its 'check' or 'class'
    Given an empty directory
    And a config.yml file:
      """
      constant-custom:
        constant: Constant_Definition
        options:
          constant: CUSTOM
          defined: true
      """

    When I try `wp doctor list --config=config.yml`
    Then STDERR should be:
      """
      Error: Check 'constant-custom' is missing 'class' or 'check'. Verify check registration.
      """

  Scenario: Error when a check has been provided an unsupported option
    Given an empty directory
    And a config.yml file:
      """
      constant-invalid-option:
        check: Constant_Definition
        options:
          constant_name: CUSTOM
          defined: true
      """

    When I try `wp doctor list --config=config.yml`
    Then STDERR should be:
      """
      Error: Cannot set invalid property 'constant_name'.
      """

  Scenario: Support inheriting another config file
    Given an empty directory
    And a first-config.yml file:
      """
      constant-wp-debug:
        check: Constant_Definition
        options:
          constant: WP_DEBUG
          falsy: true
      """
    And a second-config.yml file:
      """
      _:
        inherit: first-config.yml
      constant-savequeries:
        check: Constant_Definition
        options:
          constant: SAVEQUERIES
          falsy: true
      """

    When I run `wp doctor list --format=count --config=first-config.yml`
    Then STDOUT should be:
      """
      1
      """

    When I run `wp doctor list --format=count --config=second-config.yml`
    Then STDOUT should be:
      """
      2
      """

  Scenario: Support inheriting the default doctor.yml
    Given an empty directory
    And a first-config.yml file:
      """
      _:
        inherit: default
      constant-custom:
        check: Constant_Definition
        options:
          constant: CUSTOM
          defined: true
      """

    When I run `wp doctor list --config=first-config.yml --fields=name`
    Then STDOUT should be a table containing rows:
      | name                        |
      | constant-custom             |
      | constant-savequeries-falsy  |

  Scenario: Permit checks to be skipped when inheriting
    Given an empty directory
    And a skipped-checks.yml file:
      """
      _:
        inherit: default
        skipped_checks:
          - constant-savequeries-falsy
      """

    When I run `wp doctor list --fields=name`
    Then STDOUT should contain:
      """
      constant-savequeries-falsy
      """

    When I run `wp doctor list --config=skipped-checks.yml --fields=name`
    Then STDOUT should not contain:
      """
      constant-savequeries-falsy
      """

  Scenario: Use the 'require' attribute to require an arbitrary path
    Given a WP install
    And a config.yml file:
      """
      plugin-akismet-valid-api-key:
        class: Akismet_Valid_API_Key
        require: akismet-valid-api-key.php
      """
    And a akismet-valid-api-key.php file:
      """
      <?php
      /**
       * Ensures Akismet is activated with the appropriate credentials.
       */
      class Akismet_Valid_API_Key extends runcommand\Doctor\Checks\Check {

        public function run() {
          // If the Akismet isn't activated, bail early.
          if ( ! class_exists( 'Akismet' ) ) {
            $this->set_status( 'error' );
            $this->set_message( "Akismet doesn't appear to be activated." );
            return;
          }
          // Verify that the API exists.
          $api_key = Akismet::get_api_key();
          if ( empty( $api_key ) ) {
            $this->set_status( 'error' );
            $this->set_message( 'API key is missing.' );
            return;
          }
          // Verify that the API key is valid.
          $verification = Akismet::verify_key( $api_key );
          if ( 'failed' === $verification ) {
            $this->set_status( 'error' );
            $this->set_message( 'API key verification failed.' );
            return;
          }
          // Everything looks good, so report a success.
          $this->set_status( 'success' );
          $this->set_message( 'Akismet is activated with a verified API key.' );
        }

      }
      """
    And I run `wp plugin activate akismet`

    When I try `wp doctor check --all --config=config.yml`
    Then STDOUT should be a table containing rows:
      | name                         | status       | message              |
      | plugin-akismet-valid-api-key | error        | API key is missing.  |
    And the return code should be 1

  Scenario: 'require' attribute fails successfully when the file doesn't exist
    Given a WP install
    And a config.yml file:
      """
      plugin-akismet-valid-api-key:
        class: Akismet_Valid_API_Key
        require: akismet-valid-api-key.php
      """

    When I try `wp doctor check --all --config=config.yml`
    Then STDERR should be:
      """
      Error: Required file 'akismet-valid-api-key.php' doesn't exist (from 'plugin-akismet-valid-api-key').
      """
