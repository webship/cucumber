const {Given} = require('@cucumber/cucumber');
const {When, Before} = require('@cucumber/cucumber');
const {Then} = require('@cucumber/cucumber');

Given(/^I go to "([^"]*)?"$/, function(url) {
  return browser.url(url)
                .waitForElementVisible('body', 1000);
});

Then(/^I should( not)* see "([^"]*)?"$/, function(negativeCase, text) {
  if (negativeCase) {
    return browser.assert.not.textContains("body", text);
  }
  
  return browser.assert.textContains("body", text);
});
