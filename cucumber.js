module.exports = {
  default: {
    timeout: 45000,
    requireModule: ['tsx/cjs'],
    require: [
      'node_modules/webship-js/tests/step-definitions/**/*.js',
      'tests/step-definitions/**/*.js',
    ],
    paths: ['tests/features/**/*.feature'],
    format: [
      '@cucumber/pretty-formatter',
      'json:tests/reports/cucumber_report.json',
      'junit:tests/reports/junit.xml',
    ],
    worldParameters: {
      launchUrl: process.env.LAUNCH_URL || 'http://localhost',
      // Test users for the roles the Cucumber profile installs.
      //
      // Webmaster is the site-install account (created by `drush
      // site:install cucumber --account-name=webmaster
      // --account-pass=dD.123123ddd`). The rest are provisioned by
      // `Given I add testing users` (see tests/step-definitions/cucumber.steps.js),
      // which iterates this registry and skips entries flagged `isAdmin: true`.
      users: {
        Webmaster: {
          username: 'webmaster',
          email: 'webmaster@example.test',
          password: 'dD.123123ddd',
          isAdmin: true,
        },
        Admin: {
          username: 'admin_user',
          email: 'admin_user@example.test',
          password: 'dD.123123ddd',
          roles: ['admin'],
        },
        Tester: {
          username: 'tester_user',
          email: 'tester_user@example.test',
          password: 'dD.123123ddd',
          roles: ['tester'],
        },
        'Authenticated user': {
          username: 'authenticated_user',
          email: 'authenticated_user@example.test',
          password: 'dD.123123ddd',
          roles: [],
        },
      },
      minWaitTime: {
        page: 3000,
        before_scenario: 0,
        after_scenario: 0,
        before_step: 0,
        after_step: 0,
      },
      selectors: {
        css: {},
        xpath: {},
        filesPath: './tests/selectors/',
        files: ['cms-drupal-cms-gin.json'],
        offset: 60,
        breakpoints: {
          xs: { width: 375, height: 667 },
          sm: { width: 576, height: 800 },
          md: { width: 768, height: 1024 },
          lg: { width: 992, height: 768 },
          xl: { width: 1200, height: 900, default: true },
          xxl: { width: 1400, height: 900 },
        },
      },
      screenshot: {
        dir: './tests/screenshots',
        purge: false,
        onFailed: true,
        onEveryStep: false,
        alwaysFullscreen: false,
        failedPrefix: 'failed_',
        filenamePattern: '{datetime}.{feature_file}.feature_{step_line}.{ext}',
        filenamePatternFailed:
          '{failed_prefix}{datetime}.{feature_file}.feature_{step_line}.{ext}',
        infoTypes: '',
      },
      video: {
        mode: 'on-failure',
        dir: './tests/videos',
        size: { width: 1280, height: 720 },
        filenamePattern: '{datetime}.{feature_file}.{scenario}.{status}.{ext}',
      },
      javascript: {
        mode: 'warn',
        levels: ['error'],
        ignore: '',
        beforeScenario: false,
        afterScenario: true,
      },
    },
  },
};
