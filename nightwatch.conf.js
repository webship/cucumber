const Services = {}; loadServices();

module.exports = {
  // An array of folders (excluding subfolders) where your tests are located;
  // if this is not specified, the test source must be passed as the second argument to the test runner.
  src_folders: ['node_modules/webship-js/tests/step-definitions'],

  // See https://nightwatchjs.org/guide/extending-nightwatch/adding-custom-commands.html
  custom_commands_path: './node_modules/webship-js/lib/custom-commands',

  // See https://nightwatchjs.org/guide/extending-nightwatch/adding-custom-assertions.html
  custom_assertions_path: './node_modules/webship-js/lib/custom-assertions',

  test_runner: {
    type: 'cucumber',
    options: {
      feature_path: 'tests/features/*.feature',
      additional_config: '',
      parallel: 2,
    },
  },

  test_settings: {
    default: {
      launch_url: 'http://cucumber.test',
      selenium_port: 4444,
      selenium_host: '127.0.0.1',
      silent: true,
      screenshots: {
        enabled: true,
        path: './reports/screenshots',
      },

      desiredCapabilities: {
        browserName: 'chrome',
        'goog:chromeOptions': {
          // More info on Chromedriver: https://sites.google.com/a/chromium.org/chromedriver/
          //
          // w3c:false tells Chromedriver to run using the legacy JSONWire protocol (not required in Chrome 78)
          w3c: false,
          args: [
            '--headless',
            '--start-maximized',
            '--disable-gpu',
            '--window-size=1600,1200',
            '--no-sandbox',
            '--disable-dev-shm-usage',
            '--disable-setuid-sandbox',
            '--disable-web-security',
            '--DNS-prefetch-disable',
            '--disable-translate',
            '--ignore-certificate-errors',
            '--test-type',
            '--disable-extensions',
            '--incognito',
            '--disable-infobars',
            '--remote-debugging-port=9222',
            '--allowed-ips=*',
            '--whitelisted-ips=*',
            '--allow-insecure-localhost',
          ],
        },
      },
    },
  },

  selenium_server: {
    // Selenium Server is running locally and is managed by Nightwatch
    selenium: {
      start_process: true,
      port: 4444,
      server_path: (Services.seleniumServer ? Services.seleniumServer.path : ''),
      cli_args: {
        'webdriver.gecko.driver': (Services.geckodriver ? Services.geckodriver.path : ''),
        'webdriver.chrome.driver': (Services.chromedriver ? Services.chromedriver.path : ''),
      },
    },
  },

  'selenium.chrome': {
    extends: 'selenium_server',
    desiredCapabilities: {
      browserName: 'chrome',
      chromeOptions: {
        w3c: true,
      },
    },
  }
};

function loadServices() {
  try {
    Services.seleniumServer = require('selenium-server');
  } catch (err) { }

  try {
    Services.chromedriver = require('chromedriver');
  } catch (err) { }

  try {
    Services.geckodriver = require('geckodriver');
  } catch (err) { }
}