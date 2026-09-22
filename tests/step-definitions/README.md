# Step definitions

`cucumber.steps.js` adds what webship-js does not ship:

- `Given I am a logged in user with the "<X>" user` and `Given I add testing users` (shared with the Webship modules).
- `Then I should see a "<Label>" field` and `Then I should see the button "<Text>"`.
- `When I fill in the Ace editor with:` sets the Ace editor content from a doc string.
