# 10.0.0-alpha1

### Highlighted important changes
* Issue [#3305029](https://www.drupal.org/i/3305029): Started a 10.0.x branch for the Cucumber Distribution to support Drupal 10
* Issue [#3378114](https://www.drupal.org/i/3378114): Updated Selenium to 4.1.2 in the Circleci config file

### Added:
* Issue [#3353006](https://www.drupal.org/i/3353006): Added custom_assertions_path to nightwatch config file
* Issue [#3353007](https://www.drupal.org/i/3353007): Added export report command in package.json file with Nightwatch run command
* Issue [#3378652](https://www.drupal.org/i/3378652): Added the logo image used in the ecosystem

### Changed:
* Issue [#3365963](https://www.drupal.org/i/3365963): Changed the cucumber profile database from MySQL to SQLite

### Updates:
* Issue [#3358871](https://www.drupal.org/i/3358871): Updated README.md CircleCI status path
* Issue [#3353009](https://www.drupal.org/i/3353009): Updated tests folder reports, logs
* Issue [#3358860](https://www.drupal.org/i/3358860): Updated check cucumber site feature Given path

### Fixes:
* Issue [#3336213](https://www.drupal.org/i/3336213): Fixed install package google-chrome-stable error after upgrading into 109 (Circle CI)
* Issue [#3336370](https://www.drupal.org/i/3336370): Fixed Automated Testing for Cucumber on Circle CI with the new release of Webship-js 1.0.12
* Issue [#3346685](https://www.drupal.org/i/3346685): Fixed the process of redirecting the anonymous user to the login page
