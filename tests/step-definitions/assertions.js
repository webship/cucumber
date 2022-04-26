const { client } = require('nightwatch/api');
const { Given, When, Then, And, But } = require('cucumber');


Then('I should see {string}', function(textValue) {
  return client.assert.containsText('body', textValue);
});
