Feature: First test for cucumber
As a tester
I want to be able to test the cucumber site
So that I know it is working

  Scenario: Check the cucumber site
    Given I go to "http://localhost:8080"
     Then I should see "Username"
