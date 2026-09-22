Feature: Adding a Feature media item with a Gherkin script
  As a tester
  I want to write a Gherkin script in the Ace editor and save it as a Feature
  So that the script is stored and listed with the other features

  Background:
    Given I am a logged in user with the "Webmaster" user

  Scenario: Add Feature form loads the Ace editor
    When I navigate to "/media/add/feature"
    Then I should see a "Name" field
     And ".ace_editor" should be visible within 15 seconds
     And "textarea[name='field_media_gherkin[0][value]']" should be hidden
     And I should see the button "Save"

  Scenario: A Feature media item with a Gherkin script saves
    When I navigate to "/media/add/feature"
     And I fill in "Name" with "Login smoke feature"
     And I fill in the Ace editor with:
      """
      Feature: Login smoke
        Scenario: Visitor opens the login page
          Given I am on "/user/login"
      """
     And I press "Save"
    Then I should see "Feature Login smoke feature has been created."
    When I navigate to "/admin/content/media"
    Then I should see "Login smoke feature"
