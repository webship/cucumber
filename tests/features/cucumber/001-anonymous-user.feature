Feature: Aonymous user

  @javascript @local @development @staging @production
  Scenario: Check that anonymous users cannot register.
    Given I am an anonymous user
     When I go to "/user/register"
     Then I should see "Access denied"

  @javascript @local @development @staging @production
  Scenario: Check that anonymous users cannot access admin pages.
    Given I am an anonymous user
     When I go to "/admin"
     Then I should see "Access denied"
  
