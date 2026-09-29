Feature: The roles write a feature and move it through the workflow
  As a member of the Admin role or a tester
  I want to write a feature with its Gherkin script and move it from step to step
  So that the listings show the state every feature is in

  Scenario Outline: The "<user>" user writes a feature with a Gherkin script
    Given I am a logged in user with the "<user>" user
    When I navigate to "/media/add/feature"
    Then I should not see "you do not have sufficient permissions"
    When I fill in "Name" with "<user> profile feature"
     And I fill in the Ace editor with:
      """
      Feature: Sign in
        Scenario: A visitor opens the log in page
          Given I am on "/user/login"
          Then I should see "Log in"
      """
     And I press "Save"
    Then I should see "Feature <user> profile feature has been created."
     And I should not see "You do not have access to transition"
     And I should not see "Access denied"
    When I navigate to "/features?name=<user>%20profile%20feature"
    Then I should see "To Do" in the "<user> profile feature" row

    Examples:
      | user   |
      | Admin  |
      | Tester |

  Scenario Outline: The "<user>" user moves the feature to "<state>"
    Given I am a logged in user with the "<user>" user
    When I navigate to "/features?name=<user>%20profile%20feature"
     And I click "Edit" in the "<user> profile feature" row
     And I select "<state>" from "Change to"
     And I press "Save"
    Then I should see "Feature <user> profile feature has been updated."
    When I navigate to "/features?name=<user>%20profile%20feature"
    Then I should see "<state>" in the "<user> profile feature" row

    Examples:
      | user   | state       |
      | Admin  | In Progress |
      | Admin  | Implemented |
      | Admin  | Published   |
      | Admin  | To Do       |
      | Tester | In Progress |
      | Tester | Implemented |
      | Tester | To Do       |

  Scenario: The Admin role sends a feature back to Draft
    Given I am a logged in user with the "Admin" user
    When I navigate to "/features?name=Admin%20profile%20feature"
     And I click "Edit" in the "Admin profile feature" row
     And I select "Draft" from "Change to"
     And I press "Save"
    Then I should see "Feature Admin profile feature has been updated."
    When I navigate to "/features?name=Admin%20profile%20feature"
     And I click "Edit" in the "Admin profile feature" row
    Then I should see "Draft" in the "#edit-moderation-state-0-current" element
    When I select "To Do" from "Change to"
     And I press "Save"
    Then I should see "Feature Admin profile feature has been updated."

  Scenario: Publishing a feature is not a step of a tester
    Given I am a logged in user with the "Tester" user
    When I navigate to "/features?name=Tester%20profile%20feature"
     And I click "Edit" in the "Tester profile feature" row
    Then the option "In Progress" should exist within the select element "#edit-moderation-state-0-state"
     And the option "Published" should not exist within the select element "#edit-moderation-state-0-state"
