# Cucumber tests

[webship-js](https://www.npmjs.com/package/webship-js) (Playwright + Cucumber-js) suite, run against a site installed with the Cucumber profile on Drupal 11.

```bash
ddev drush site:install cucumber --account-name=webmaster --account-pass=dD.123123ddd \
  cucumber_user_roles.tester=1 cucumber_user_roles.developer=1 \
  cucumber_user_roles.analyst=1 cucumber_user_roles.coordinator=1 \
  cucumber_user_roles.designer=1 cucumber_user_roles.product_owner=1 -y
yarn install
./node_modules/.bin/playwright install --with-deps chromium
LAUNCH_URL="https://<project>.ddev.site" yarn test
```

CI: the `webship-js-test` job in `.gitlab-ci.yml`.
