const {Given,When, Then, Before} = require('@cucumber/cucumber');

Given(/^I go to "([^"]*)?"$/, function(url) {
  return browser.url(url);
});

Then(/^I should( not)* see "([^"]*)?"$/, function(negativeCase, text) {
  if (negativeCase) {
    return browser.assert.not.textContains(text);
  }
  
  return browser.assert.textContains(text);
});
