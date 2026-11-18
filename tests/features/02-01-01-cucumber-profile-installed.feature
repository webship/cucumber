Feature: The Cucumber installation profile is installed
  As a site administrator
  I want the site to run on the Cucumber profile with its modules enabled
  So that the testing management system is ready to use

  Background:
    Given I am a logged in user with the "Webmaster" user

  Scenario: The status report names the Cucumber profile
    When I navigate to "/admin/reports/status"
    Then I should see "Installation profile"
     And I should see "Cucumber"

  Scenario: The Cucumber modules installed by the profile are enabled
    Then the "cucumber_core" module should be installed
     And the "gherkin" module should be installed
     And the "cucumber_user_roles" module should be installed
     And the "cucumber_products" module should be installed
     And the "cucumber_components" module should be installed
     And the "cucumber_projects" module should be installed
