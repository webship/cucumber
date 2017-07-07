Feature: Cucumber User - User Roles.
As a logged in as a user with the "Administer users" permission
I want to be able to see the list of User Roles
So that they should be (Support, System Admin, Programmer, Designer,
 Project Manager, Tester, Quality Analysts Manager, Business Analysts Manager,
 Software Development Manager)

  @local @development @staging @production
  Scenario: Check that all default list of roles are present.
    Given I am logged in as a user with the "Administer users" permission
     When I go to "admin/people/roles"
     Then I should see "Support"
      And I should see "System Admin"
      And I should see "Programmer"
      And I should see "Designer"
      And I should see "Project Manager"
      And I should see "Product Manager"
      And I should see "Tester"
      And I should see "Quality Analysts Manager"
      And I should see "Analyst"
      And I should see "Business Analysts Manager"
      And I should see "Software Development Manager"
