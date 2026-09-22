/**
 * @file
 * Cucumber profile steps: shared Webship login/provisioning plus Ace input.
 */

const { Given, When, Then } = require('@cucumber/cucumber');
const { friendly } = require('webship-js/tests/step-definitions/webship');

/**
 * Run a step body and rethrow any failure as a tester-friendly error.
 *
 * @param {Function} body  - async function performing the step.
 * @param {string} message - human-readable description for failures.
 */
async function attempt(body, message) {
  try {
    await body();
  } catch (err) {
    throw friendly(message, err);
  }
}

/**
 * Log in as a named test user defined in cucumber.js worldParameters.users.
 *
 * The Webmaster row is the site-install super-admin. Every other row is
 * provisioned by `Given I add testing users` (see below). The same
 * phrasing is used by varbase_project so suites can move between
 * projects without re-learning step names.
 *
 * Example #1: Given I am a logged in user with the "Webmaster" user
 * Example #2: Given I am a logged in user with the "Admin" user
 * Example #3: Given I am a logged in user with the "Authenticated user" user
 * Example #4: Given I am a logged in user with the username "Tester" user
 * Example #5: Given I am a logged in user with "Webmaster"
 */
Given(
  /^I am a logged in user with( the)*( username)* "([^"]*)?"( user)?$/,
  async function (theCase, usernameCase, key, userCase) {
    const users = this.parameters.users || {};
    if (!(key in users)) {
      throw new Error(
        `No user named "${key}" in cucumber.js worldParameters.users`,
      );
    }
    const { username, password } = users[key];
    if (!username || !password) {
      throw new Error(
        `User "${key}" is missing username or password in worldParameters.users`,
      );
    }
    await this.page.goto(`${this.parameters.launchUrl}/user/login`);
    // Target the form controls by their name attribute: Web Admin 12.0.x
    // brings in View Password, whose "Show password" toggle carries an
    // aria-label of "Password" and makes getByLabel('Password') ambiguous.
    await this.page.locator('input[name="name"]').fill(username);
    await this.page.locator('input[name="pass"]').fill(password);
    await this.page.locator('input[value="Log in"]').click();
    await this.page.waitForLoadState('networkidle');
  },
);

/**
 * Provision every non-admin user from cucumber.js worldParameters.users via
 * Drupal's /admin/people/create form. Entries flagged isAdmin: true are
 * skipped (the site-install Webmaster already exists). Idempotent: a
 * second run reports "name is already taken" and the step swallows it.
 *
 * Must be invoked while logged in as the Webmaster (or any user with the
 * "administer users" permission).
 *
 * Example #1: Given I add testing users
 * Example #2: And I add testing users
 * Example #3: When I add testing users
 * Example #4: Given I add the testing users
 * Example #5: And we add testing users
 */
Given(/^(?:I |we )?add( the)? testing users$/, async function (theCase) {
  const users = this.parameters.users || {};
  for (const [key, info] of Object.entries(users)) {
    if (info.isAdmin) continue;
    await this.page.goto(`${this.parameters.launchUrl}/admin/people/create`);
    await this.page.locator('#edit-name').fill(info.username);
    await this.page
      .locator('#edit-mail')
      .fill(info.email || `${info.username}@example.test`);
    await this.page.locator('#edit-pass-pass1').fill(info.password);
    await this.page.locator('#edit-pass-pass2').fill(info.password);
    for (const role of info.roles || []) {
      const cb = this.page.locator(`input[name="roles[${role}]"]`);
      if ((await cb.count()) > 0) await cb.check();
    }
    await this.page.locator('#edit-submit').click();
    await this.page.waitForLoadState('networkidle');
  }
});

/**
 * Resolve a form field locator by label, falling back to the label element
 * itself for inputs that are visually replaced by rich editors (CKEditor,
 * file widgets, etc.) which hide the underlying control.
 */
function fieldLocator(page, label) {
  return page
    .locator('label.form-item__label, label.form-required, label')
    .filter({
      hasText: new RegExp(
        `^\\s*${label.replace(/[.*+?^${}()|[\\]\\\\]/g, '\\$&')}(\\s|$)`,
        'i',
      ),
    })
    .first();
}

/**
 * Assert that a form field with the given label is visible on the page.
 *
 * Example #1: Then I should see a "Title" field
 * Example #2: Then I should see a "Body" field
 * Example #3: Then I should see a "Username" field
 * Example #4: Then I should see a "Password" field
 * Example #5: Then I should see a "Summary" field
 */
Then(/^(?:I |we )?should see a "([^"]*)" field$/, async function (label) {
  await attempt(async () => {
    const locator = fieldLocator(this.page, label);
    await locator.waitFor({ state: 'visible', timeout: 10000 });
  }, `Expected to find a field labeled "${label}"`);
});

/**
 * Assert that a form field with the given label (with article "an") is visible.
 *
 * Example #1: Then I should see an "Image" field
 * Example #2: Then I should see an "Author" field
 * Example #3: Then I should see an "Options" field
 * Example #4: And I should see an "Image" field
 * Example #5: And I should see an "Author" field
 */
Then(/^(?:I |we )?should see an "([^"]*)" field$/, async function (label) {
  await attempt(async () => {
    const locator = fieldLocator(this.page, label);
    await locator.waitFor({ state: 'visible', timeout: 10000 });
  }, `Expected to find a field labeled "${label}"`);
});

/**
 * Assert that a button with the given text is visible on the page.
 *
 * Example #1: Then I should see the button "Save"
 * Example #2: Then I should see the button "Log in"
 * Example #3: Then I should see the button "Preview"
 * Example #4: Then I should see the button "Delete"
 * Example #5: Then I should see the button "Submit"
 */
Then(/^(?:I |we )?should see the button "([^"]*)"$/, async function (text) {
  await attempt(async () => {
    const locator = this.page
      .getByRole('button', { name: text, exact: false })
      .first();
    await locator.waitFor({ state: 'visible', timeout: 10000 });
  }, `Expected to find a button with text "${text}"`);
});

/**
 * Set the content of the Ace editor that replaced a textarea, then wait for
 * ace_editor's debounced sync back to the hidden textarea.
 *
 * Example #1: When I fill in the Ace editor with:
 * Example #2: And I fill in the Ace editor with:
 * Example #3: When we fill in the Ace editor with:
 * Example #4: And we fill in the Ace editor with:
 * Example #5: When fill in the Ace editor with:
 */
When(/^(?:I |we )?fill in the Ace editor with:$/, async function (text) {
  await attempt(async () => {
    await this.page.waitForFunction(
      () => window.ace && document.querySelector('.ace_editor'),
      null,
      { timeout: 15000 },
    );
    await this.page.evaluate((value) => {
      window.ace
        .edit(document.querySelector('.ace_editor'))
        .session.setValue(value);
    }, text);
    await this.page.waitForTimeout(700);
  }, 'Could not fill in the Ace editor');
});

/**
 * Checks a computed CSS property of the first element matching a selector.
 *
 * Example: Then ".ace_editor" should have the CSS "resize" set to "vertical"
 */
Then(
  /^"([^"]*)" should have the CSS "([^"]*)" set to "([^"]*)"$/,
  async function (selector, property, expected) {
    const locator = this.page.locator(selector).first();
    await locator.waitFor({ state: 'visible', timeout: 15000 });
    const actual = await locator.evaluate(
      (el, prop) => getComputedStyle(el).getPropertyValue(prop),
      property,
    );
    if (actual.trim() !== expected) {
      throw new Error(
        `Expected ${property} "${expected}" on ${selector}, got "${actual}".`,
      );
    }
  },
);
