Feature: Login for every configured user
  As a site administrator
  I want every account in cucumber.js worldParameters.users to log in
  So that the role scenarios have known-good accounts

  The site build creates the accounts (tests/README.md): the suite only logs
  in, it never creates or deletes an account.

  Scenario: Webmaster can log in
    Given I am a logged in user with the "Webmaster" user
    Then I should be logged in

  Scenario: Admin lands on the Admin Dashboard after logging in
    Given I am a logged in user with the "Admin" user
    Then the path should be "/webdashboard/admin_dashboard"
     And I should see "Admin Dashboard"

  Scenario: Tester lands on the Tester Dashboard after logging in
    Given I am a logged in user with the "Tester" user
    Then the path should be "/webdashboard/tester_dashboard"
     And I should see "Tester Dashboard"

  Scenario: Authenticated user lands on the Default Dashboard after logging in
    Given I am a logged in user with the "Authenticated user" user
    Then the path should be "/webdashboard/default_dashboard"
     And I should see "Default Dashboard"
