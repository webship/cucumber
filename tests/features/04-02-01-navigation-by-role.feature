Feature: Every role has the navigation of its work and a way out
  As a signed-in member of the team
  I want the UIkit Admin shell with the links of my work and a Log out link
  So that I reach the listings and leave the site without typing an address

  Scenario: A member without a role has the shell and the Log out link
    Given I am a logged in user with the "Authenticated user" user
    When I set the viewport size to 1280x900
    Then I should see a ".uikit-admin-shell" element by attr
     And I should see "Log out" in the ".uikit-admin-rail__logout" element
     And I should not see "Features" in the ".uikit-admin-rail__body" element
     And I should not see "Products" in the ".uikit-admin-rail__body" element
    When I follow "Log out"
    Then the path should be "/user/login"

  Scenario Outline: The "<user>" user has the links of the work in the navigation
    Given I am a logged in user with the "<user>" user
    When I set the viewport size to 1280x900
    Then I should see a ".uikit-admin-shell" element by attr
     And I should see "Features" in the ".uikit-admin-rail__body" element
     And I should see "Products" in the ".uikit-admin-rail__body" element
     And I should see "Components" in the ".uikit-admin-rail__body" element
     And I should see "Projects" in the ".uikit-admin-rail__body" element
     And I should see "Log out" in the ".uikit-admin-rail__logout" element

    Examples:
      | user   |
      | Admin  |
      | Tester |

  Scenario Outline: The "<user>" user follows the "<link>" link of the navigation
    Given I am a logged in user with the "<user>" user
    When I set the viewport size to 1280x900
     And I follow "<link>"
     # The rail link loads a new page: wait for it before checking the path.
     And I wait until the URL contains "<path>"
    Then the path should be "<path>"
     And I should not see "Access denied"
     And I should not see "Insert selected"

    Examples:
      | user   | link       | path        |
      | Admin  | Features   | /features   |
      | Admin  | Products   | /products   |
      | Admin  | Components | /components |
      | Admin  | Projects   | /projects   |
      | Tester | Features   | /features   |
      | Tester | Products   | /products   |

  Scenario Outline: The "<user>" user logs out by the link
    Given I am a logged in user with the "<user>" user
    When I set the viewport size to 1280x900
     And I follow "Log out"
    Then the path should be "/user/login"
    When I navigate to "/dashboard/default_dashboard"
    Then the path should be "/user/login"

    Examples:
      | user   |
      | Admin  |
      | Tester |

  Scenario: A member without a role may not open the Features page
    Given I am a logged in user with the "Authenticated user" user
    When I navigate to "/features"
    Then I should see "Access denied"
