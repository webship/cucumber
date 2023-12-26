[![CircleCI](https://circleci.com/gh/webship/cucumber/tree/10.0.x.svg?style=svg)](https://circleci.com/gh/webship/cucumber/tree/10.0.x) Cucumber 10.0.x-dev

# Cucumber
Cucumber Automated Functional Acceptance Testing Management system

The app.webship.co console dashboard system was built on top of Drupal, as it has many options, tools, frameworks, and configuration management, which are needed in building solutions.

Helps in speeding up the work of having Automated Functional Acceptance Testing for products to ship websites in a swift way.

[![Cucumber](https://www.drupal.org/files/project-images/drupal-cucumber.png)](https://www.drupal.org/project/cucumber)

### We LOVE to help with:
* Setup of Automated Functional Testing configurations for projects.
* Writing Cucumber descriptions, Gherkin scripts for web apps.
* Development of Webship-js using Nightwatch.js, Cucumber-js with custom and advanced general step definitions.
* Setup Selenium robot servers, and Web-drivers to work on a local or remote public/private CI/CD servers.

## Usage

First you need to [install composer](https://getcomposer.org/doc/00-intro.md#installation-linux-unix-osx).

> Note: The instructions below refer to the [global composer installation](https://getcomposer.org/doc/00-intro.md#globally).
You might need to replace `composer` with `php composer.phar` (or similar) 
for your setup.

To install the dev version of Cucumber 10.0.x run this command:
```
composer create-project webship/cucumber-project:10.0.x-dev WEBSITE_NAME --stability dev --no-interaction
```