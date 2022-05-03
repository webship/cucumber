const {Given} = require('@cucumber/cucumber');
const {When, Before} = require('@cucumber/cucumber');
const {Then} = require('@cucumber/cucumber');

Given(/^I go to "([^"]*)?"$/, function(url) {
  return browser.url(url);
});

Then(/^I should( not)* see "([^"]*)?"$/, function(negativeCase, expectedText) {
  if (negativeCase) {
    return browser.assert.not.textContains('body', expectedText);
  }
  
  return browser.assert.textContains('body', expectedText);
});