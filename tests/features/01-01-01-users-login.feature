Feature: Login for every configured user
  As a site administrator
  I want every user defined in cucumber.js worldParameters.users to be
  able to log in
  So that the suite has known-good fixtures for the Cucumber roles
  before any role-specific scenarios run

  Scenario: Webmaster can log in and provision the rest of the testing users
    Given I am a logged in user with the "Webmaster" user
    When I add testing users
     And I navigate to "/admin/people"
    Then I should see "admin_user"
     And I should see "tester_user"
     And I should see "authenticated_user"

  Scenario: Admin lands on the Admin Dashboard after logging in
    Given I am a logged in user with the "Admin" user
    Then the path should be "/dashboard/admin_dashboard"
     And I should see "Admin Dashboard"

  Scenario: Tester lands on the Tester Dashboard after logging in
    Given I am a logged in user with the "Tester" user
    Then the path should be "/dashboard/tester_dashboard"
     And I should see "Tester Dashboard"

  Scenario: Authenticated user lands on the Default Dashboard after logging in
    Given I am a logged in user with the "Authenticated user" user
    Then the path should be "/dashboard/default_dashboard"
     And I should see "Default Dashboard"
