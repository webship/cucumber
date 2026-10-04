Feature: The key Cucumber pages respond
  As a site administrator
  I want the features page and every dashboard the profile installs to load
  So that each team role has a working home in the system

  Background:
    Given I am a logged in user with the "Webmaster" user

  Scenario: The front page is the Default Dashboard
    When I go to the homepage
    Then I should see "Default Dashboard"
     And I should not see "The website encountered an unexpected error"

  Scenario Outline: The "<page>" page loads
    When I navigate to "<path>"
    Then I should see "<page>"
     And I should not see "The website encountered an unexpected error"

    Examples:
      | page                    | path                               |
      | Features                | /features                          |
      | Products                | /products                          |
      | Components              | /components                        |
      | Projects                | /projects                          |
      | Default Dashboard       | /webdashboard/default_dashboard       |
      | Admin Dashboard         | /webdashboard/admin_dashboard         |
      | Tester Dashboard        | /webdashboard/tester_dashboard        |
      | Developer Dashboard     | /webdashboard/developer_dashboard     |
      | Analyst Dashboard       | /webdashboard/analyst_dashboard       |
      | Coordinator Dashboard   | /webdashboard/coordinator_dashboard   |
      | Designer Dashboard      | /webdashboard/designer_dashboard      |
      | Product Owner Dashboard | /webdashboard/product_owner_dashboard |
