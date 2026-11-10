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
