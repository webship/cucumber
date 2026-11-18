# Cucumber tests

The functional tests of the Cucumber profile: Gherkin features run by
[webship-js](https://www.npmjs.com/package/webship-js) (Playwright and Cucumber)
with the Drupal steps of
[@webship-js/drupal-core-steps](https://www.npmjs.com/package/@webship-js/drupal-core-steps)
(the login by account handle, access checks, module checks). `cucumber.js` in
the project root holds the configuration.

| Folder | What it holds |
| --- | --- |
| `features/` | the features (8 files, 52 scenarios) |
| `step-definitions/` | this project's own steps (the Feature form and the Ace editor) |
| `selectors/` | named selectors for UIkit Admin, the theme of Cucumber |
| `reports/`, `screenshots/`, `videos/` | what a run writes (not committed) |

## From nothing to a green run

### 1. A Cucumber site in DDEV

Build a site from the
[Cucumber project template](https://www.drupal.org/project/cucumber_project):

```bash
mkdir cucumber-site && cd cucumber-site
ddev config --project-type=drupal --docroot=web
ddev start
ddev composer create-project drupal/cucumber_project:~12.0
ddev restart
```

### 2. The site, with the test accounts

The suite logs in as the accounts that `cucumber.js` lists in
`worldParameters.users`, and never creates or deletes an account: the site
build does. Choose four passwords, keep them in your environment, install the
site with every user role and the Webmaster account, and add the other three:

```bash
export DRUPAL_ADMIN_PASS='…' CUCUMBER_ADMIN_PASS='…' CUCUMBER_TESTER_PASS='…' DRUPAL_AUTHENTICATED_PASS='…'
ddev drush site:install cucumber --account-name=webmaster --account-mail=webmaster@example.test --account-pass="$DRUPAL_ADMIN_PASS" \
  --site-name="Cucumber Test" \
  cucumber_user_roles.tester=1 cucumber_user_roles.developer=1 \
  cucumber_user_roles.analyst=1 cucumber_user_roles.coordinator=1 \
  cucumber_user_roles.designer=1 cucumber_user_roles.product_owner=1 -y
ddev drush user:create admin_user --mail=admin_user@example.test --password="$CUCUMBER_ADMIN_PASS"
ddev drush user:role:add admin admin_user
ddev drush user:create tester_user --mail=tester_user@example.test --password="$CUCUMBER_TESTER_PASS"
ddev drush user:role:add tester tester_user
ddev drush user:create authenticated_user --mail=authenticated_user@example.test --password="$DRUPAL_AUTHENTICATED_PASS"
```

CI builds the same site (see the `webship-js-test` job in `.gitlab-ci.yml`),
with passwords made for the job.

### 3. Run the suite

From this project's folder, with Node.js 24 and Yarn 4 (through Corepack):

```bash
corepack enable
yarn install
npx playwright install chromium
export LAUNCH_URL=https://cucumber-site.ddev.site
export NODE_EXTRA_CA_CERTS="$(mkcert -CAROOT)/rootCA.pem"
yarn test --tags "not @wip"
```

`NODE_EXTRA_CA_CERTS` lets Node.js trust DDEV's HTTPS certificate for the
steps that send a request outside the browser.
`yarn test:dry` checks that every step resolves to one definition, without
a browser.

### 4. What green looks like

```
52 scenarios (52 passed)
304 steps (304 passed)
```

The HTML report is written to `tests/reports/cucumber_report.html`. A failed
step leaves a screenshot in `tests/screenshots/` and a video in
`tests/videos/`. The suite removes the Features it writes, so a second run
starts from the same site and is green too.

One scenario asks for a new password for `authenticated_user`, and Drupal
allows five such requests for an account in six hours. To run the suite more
often than that on one site, clear the counters first:
`ddev drush sql:query "DELETE FROM flood"`.

## Test users

The accounts as `cucumber.js` lists them in `worldParameters.users`. Each
value comes from the environment variable named below; `CHANGE_ME` stands for
the password you chose in step 2.

```json
{
  "Webmaster": { "username": "webmaster", "password": "CHANGE_ME", "email": "webmaster@example.test", "isAdmin": true },
  "Admin": { "username": "admin_user", "password": "CHANGE_ME", "email": "admin_user@example.test", "roles": ["admin"] },
  "Tester": { "username": "tester_user", "password": "CHANGE_ME", "email": "tester_user@example.test", "roles": ["tester"] },
  "Authenticated user": { "username": "authenticated_user", "password": "CHANGE_ME", "email": "authenticated_user@example.test", "roles": [] }
}
```

| Handle | Variables | The account |
| --- | --- | --- |
| Webmaster | `DRUPAL_ADMIN_USER`, `DRUPAL_ADMIN_PASS`, `DRUPAL_ADMIN_MAIL` | the account the installer created |
| Admin | `CUCUMBER_ADMIN_USER`, `CUCUMBER_ADMIN_PASS`, `CUCUMBER_ADMIN_MAIL` | a member of the Admin role |
| Tester | `CUCUMBER_TESTER_USER`, `CUCUMBER_TESTER_PASS`, `CUCUMBER_TESTER_MAIL` | a member of the Tester role |
| Authenticated user | `DRUPAL_AUTHENTICATED_USER`, `DRUPAL_AUTHENTICATED_PASS`, `DRUPAL_AUTHENTICATED_MAIL` | a user with no role |
