// The webship-js configuration of this project, from the one `npx init-webship-js`
// writes (webship-js 2.0.12), with @webship-js/drupal-core-steps
// and the test accounts added. How to run it: tests/README.md.

// A feature file, a scenario (file:line), a folder or a rerun file named on
// the command line of cucumber-js runs alone (cucumber-js 12 would add it to
// the paths below): `yarn test tests/features/04-01-01-anonymous-sign-in.feature`.
const OPTIONS_WITH_A_VALUE = new Set([
  '-c',
  '--config',
  '-f',
  '--format',
  '--format-options',
  '--i18n-keywords',
  '-i',
  '--import',
  '-l',
  '--loader',
  '--language',
  '--name',
  '--order',
  '-p',
  '--profile',
  '--parallel',
  '--plugin',
  '--plugin-options',
  '-r',
  '--require',
  '--require-module',
  '--retry',
  '--retry-tag-filter',
  '--shard',
  '-t',
  '--tags',
  '--world-parameters',
]);

function commandLinePaths() {
  if (!/cucumber(-js|\.js)?$/.test(String(process.argv[1] || ''))) return [];
  const args = process.argv.slice(2);
  const paths = [];
  for (let i = 0; i < args.length; i += 1) {
    if (args[i].startsWith('-')) {
      if (OPTIONS_WITH_A_VALUE.has(args[i])) i += 1;
    } else {
      paths.push(args[i]);
    }
  }
  return paths;
}

// FEATURES=tests/features/a.feature,tests/features/b.feature runs a subset too.
function featurePaths() {
  if (process.env.FEATURES) return process.env.FEATURES.split(',');
  return commandLinePaths().length ? [] : ['tests/features/**/*.feature'];
}

module.exports = {
  default: {
    timeout: 30000, // Overridden by webship-js's setDefaultTimeout (45s); raise it with WEBSHIP_STEP_TIMEOUT.
    requireModule: ['tsx/cjs'],
    require: [
      'node_modules/webship-js/tests/step-definitions/**/*.js', // Webship-js core step definitions (auto HTML report on exit; disable: WEBSHIP_REPORT_DISABLE=1).
      'node_modules/@webship-js/drupal-core-steps/tests/step-definitions/**/*.js', // @webship-js/drupal-core-steps: the login by handle, access control, content, the site structure.
      'tests/step-definitions/**/*.js', // This project's own steps.
    ],
    paths: featurePaths(),
    format: [
      '@cucumber/pretty-formatter',
      'json:tests/reports/cucumber_report.json',
      'junit:tests/reports/junit.xml',
    ],
    formatOptions: {
      theme: {
        'feature keyword': ['bold', 'blue'],
        'feature name': ['blue', 'underline'],
        'feature description': ['blueBright'],
        'scenario keyword': ['bold', 'magenta'],
        'scenario name': ['magenta', 'underline'],
        'step keyword': ['bold', 'green'],
        'step text': ['greenBright', 'italic'],
      },
    },
    worldParameters: {
      launchUrl: process.env.LAUNCH_URL || 'http://localhost:8080',
      // A login form protected by a time limit (Honeypot) refuses a quick submit:
      // the login by handle waits this long (milliseconds) before it submits.
      loginWait: parseInt(process.env.DRUPAL_LOGIN_WAIT || '0', 10),
      // "I wait out the form time limit" waits this long (milliseconds).
      formWait: parseInt(process.env.DRUPAL_FORM_WAIT || '5000', 10),
      // The accounts the features log in as, by handle. The site build creates
      // them (tests/README.md); the suite never creates or deletes accounts.
      // Credentials come from the environment: CHANGE_ME is a placeholder.
      users: {
        Webmaster: {
          username: process.env.DRUPAL_ADMIN_USER || 'webmaster',
          password: process.env.DRUPAL_ADMIN_PASS || 'CHANGE_ME',
          email: process.env.DRUPAL_ADMIN_MAIL || 'webmaster@example.test',
          isAdmin: true,
        },
        Admin: {
          username: process.env.CUCUMBER_ADMIN_USER || 'admin_user',
          password: process.env.CUCUMBER_ADMIN_PASS || 'CHANGE_ME',
          email: process.env.CUCUMBER_ADMIN_MAIL || 'admin_user@example.test',
          roles: ['admin'],
        },
        Tester: {
          username: process.env.CUCUMBER_TESTER_USER || 'tester_user',
          password: process.env.CUCUMBER_TESTER_PASS || 'CHANGE_ME',
          email: process.env.CUCUMBER_TESTER_MAIL || 'tester_user@example.test',
          roles: ['tester'],
        },
        'Authenticated user': {
          username:
            process.env.DRUPAL_AUTHENTICATED_USER || 'authenticated_user',
          password: process.env.DRUPAL_AUTHENTICATED_PASS || 'CHANGE_ME',
          email:
            process.env.DRUPAL_AUTHENTICATED_MAIL ||
            'authenticated_user@example.test',
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
        files: ['cms-drupal-uikit-admin.json'],
        // Named viewport sizes for the viewing-the-site and set-the-viewport
        // steps and the @mobile / @desktop tags (built in: xs..xxxl, mobile,
        // tablet, desktop). Without a desktop here, @desktop opens at the
        // breakpoint marked default: true.
        breakpoints: {
          xs: { width: 375, height: 667 },
          sm: { width: 576, height: 800 },
          md: { width: 768, height: 1024 },
          lg: { width: 992, height: 768 },
          xl: { width: 1200, height: 900, default: true },
          xxl: { width: 1400, height: 900 },
          xxxl: { width: 1920, height: 1080 },
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
