const { client } = require('nightwatch/api');
const { Given, When, Then, And, But } = require('cucumber');


When('I go to {string}', function (urlAddress) {
  return client.url(urlAddress).waitForElementVisible('body', 1000);
});
