Feature: Aonymous user

  @javascript @api @local @development @staging @production
  Scenario: Check that anonymous users cannot access admin pages.
    Given I am an anonymous user
     When I go to "/admin"
     Then I should see "Access denied"
  
