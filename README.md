# Cucumber

A Drupal installation profile for managing automated functional
acceptance testing: features written as Gherkin scripts, organised in
directories, with dashboards for each team role.


## Table of contents

- Features
- Requirements
- Installation
- Usage
- Testing


## Features

- A "Feature" media type that stores a Gherkin script, edited with the
  Ace editor ([Gherkin Script](https://www.drupal.org/project/gherkin)).
- A features page, a feature directory and default and admin
  dashboards ([Cucumber Core](https://www.drupal.org/project/cucumber_core)).
- Optional user roles, each with its own dashboard
  ([Cucumber User Roles](https://www.drupal.org/project/cucumber_user_roles)).
- Products, components and projects recipes
  ([Cucumber Recipes](https://www.drupal.org/project/cucumber_recipes)),
  and optional demo content
  ([Cucumber Demos](https://www.drupal.org/project/cucumber_demos)).


## Requirements

Drupal core `^11.4 || ^12`. Drupal 11.4 on PHP 8.4 is the tested default.
Every library comes from a Composer package, so no asset-packagist,
npm-asset or bower-asset repository is needed.


## Installation

Create a project from the Cucumber project template:

```
composer create-project webship/cucumber-project:11.0.x-dev cucumber --stability dev --no-interaction
cd cucumber
drush site:install cucumber --account-name=webmaster --account-pass=<password> -y
```

The installer asks which user roles, recipes and demo to add. The
defaults install the Admin role and all three recipes, with no demo.


## Usage

Log in and create a "Feature" media item at `media/add/feature`: paste
or upload a Gherkin script and file it in a feature directory. The
front page is the default dashboard; features are listed at `features`.


## Testing

A [webship-js](https://www.npmjs.com/package/webship-js) suite (Playwright
and Cucumber-js) lives in `tests/`. See `tests/README.md`.
