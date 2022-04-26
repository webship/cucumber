const { client } = require('nightwatch/api');
const { Given, When, Then, And, But } = require('cucumber');


Given('I am an anonymous user', function () {
  return 'binding';
});

