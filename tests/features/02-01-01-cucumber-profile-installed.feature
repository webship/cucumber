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
    When I navigate to "/admin/modules"
    Then the element "#edit-modules-cucumber-core-enable" with the attribute "checked" and the value "checked" should exist
     And the element "#edit-modules-gherkin-enable" with the attribute "checked" and the value "checked" should exist
     And the element "#edit-modules-cucumber-user-roles-enable" with the attribute "checked" and the value "checked" should exist
     And the element "#edit-modules-cucumber-products-enable" with the attribute "checked" and the value "checked" should exist
     And the element "#edit-modules-cucumber-components-enable" with the attribute "checked" and the value "checked" should exist
     And the element "#edit-modules-cucumber-projects-enable" with the attribute "checked" and the value "checked" should exist
