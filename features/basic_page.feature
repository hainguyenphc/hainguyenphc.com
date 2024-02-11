@api
Feature: Check Basic Page content type
  In order to create a Basic Page
  As an administrator
  I want to access /node/add/page
  So that I can create a Basic Page node

  Scenario: Basic Page content type
    # The `antibot` and `group` modules can interfere with this step.
    # The `group` module checks the group role of a user, even if he is an administrator.
    # In particular, Fatal error: assert($storage instanceof GroupRoleStorageInterface)
    # The `antibot` module hides the `log_out` link, thus Behat cannot determine if user is logged in.
    Given I am logged in as a user with the "administrator" role
    # Then I break
    When I visit "/node/add/page"
    When I fill in "Title" with "Test Basic Page"
    When I press the "Save" button
    # Then I break
    Then I should see the text "Test Basic Page"
