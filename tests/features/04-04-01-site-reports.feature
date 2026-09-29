Feature: The reports of the site have nothing to say about Cucumber
  As a site administrator
  I want the status report and the log free of the errors of the distribution
  So that a real problem is not hidden among them

  Background:
    Given I am a logged in user with the "Webmaster" user

  Scenario: The profile carries no version of its own
    When I navigate to "/admin/reports/status"
    Then I should not see "11.0.x-dev"
     And I should not see "Unsupported release"
     And I should not see "HTTPS must be enabled for Composer downloads"

  Scenario: No module of the past administration theme is installed
    When I navigate to "/admin/appearance"
    Then I should see "UIkit Admin"
     And I should not see "Gin"
    When I navigate to "/admin/modules"
    Then I should not see "Gin Login"
     And I should not see "Gin Toolbar"

  Scenario: Anonymous Redirect is installed
    When I navigate to "/admin/modules"
    Then the element "#edit-modules-anonymous-redirect-enable" with the attribute "checked" and the value "checked" should exist
