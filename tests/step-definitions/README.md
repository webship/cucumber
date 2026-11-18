# Step definitions

`cucumber.steps.js` adds what webship-js and @webship-js/drupal-core-steps do not ship:

- `Then I should see a "<Label>" field` and `Then I should see the button "<Text>"`.
- `When I fill in the Ace editor with:` sets the Ace editor content from a doc string and waits
  until the editor has written it back to its textarea.
- `Then "<selector>" should have the CSS "<property>" set to "<value>"`.

The login by handle (`Given I am a logged in user with the "<X>" user`), the module checks and the
access checks come from [@webship-js/drupal-core-steps](https://www.npmjs.com/package/@webship-js/drupal-core-steps).
