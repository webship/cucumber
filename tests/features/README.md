# Cucumber - webship-js feature scenarios

| File | Covers |
|------|--------|
| `01-01-01-users-login.feature` | Every account of `cucumber.js` logs in, and each role lands on its dashboard. |
| `02-01-01-cucumber-profile-installed.feature` | The site runs on the Cucumber profile, with its modules enabled. |
| `02-02-01-cucumber-pages.feature` | The features, products, components and projects pages, and every dashboard, load. |
| `03-01-01-media-add-feature.feature` | Ace loads on the Add Feature form; a Gherkin script saves as a Feature, which the scenario removes. |
| `04-01-01-anonymous-sign-in.feature` | A visitor who is not signed in is led to the log in page, also after asking for a new password. |
| `04-02-01-navigation-by-role.feature` | The UIkit Admin shell, the links of the work and the Log out link, by role. |
| `04-03-01-feature-workflow-by-role.feature` | The Admin role and a tester write a feature, move it through the automated testing workflow and remove it. |
| `04-04-01-site-reports.feature` | The status report carries no error of the distribution, and no module of the past administration theme is installed. |

The site is installed with every user role selected, and the site build creates the accounts the
features log in as (see `tests/README.md`): the suite never creates or deletes an account.
