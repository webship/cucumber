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

Cucumber depends on three contributed projects that have not reached a
stable release yet:

- [Display Builder](https://www.drupal.org/project/display_builder) —
  newest release `1.0.0-beta7` (**beta**), pulled in by Webassets.
- [Media Directories](https://www.drupal.org/project/media_directories)
  and its `_ui` / `_editor` components — newest release `3.0.0-rc1`
  (**rc**). The stable `2.0.x` line only supports Drupal 8 and 9, so
  Cucumber Core pins `^3.0@rc` explicitly.
- [Config Update](https://www.drupal.org/project/config_update) —
  newest release `2.0.0-alpha4` (**alpha**), pulled in by Webconfig.

Composer only honours stability flags such as `@beta`, `@rc` and
`@alpha` in the **root** `composer.json`, so a flag written inside a
dependency is ignored. That is why the project root has to relax the
stability itself. `drupal/cucumber_project` already does it; if you
add Cucumber to a `composer.json` of your own, it needs:

```json
{
    "minimum-stability": "beta",
    "prefer-stable": true,
    "require": {
        "drupal/config_update": "^2.0@alpha"
    }
}
```

`prefer-stable` keeps every other package on its stable release: a full
Cucumber install locks 9 non-stable packages out of 186.


## Installation

### With the project template (recommended)

Start from the
[Cucumber Project](https://www.drupal.org/project/cucumber_project) template.
It requires this distribution and relaxes the stability flags for you, so there
is nothing else to configure. Composer, PHP, Drush and the database all run
inside [DDEV](https://ddev.readthedocs.io/en/stable/users/install/ddev-installation/):

```shell
mkdir my-site && cd my-site
ddev config --project-type=drupal --docroot=web
ddev start
ddev composer create-project drupal/cucumber_project:~12.0
ddev restart
ddev drush site:install cucumber --account-name=webmaster --account-pass=<password> -y
ddev launch
```

`ddev restart` picks up the `.ddev/config.yaml` the template ships (PHP 8.3,
Node.js 22, MariaDB 10.11), which replaces the one `ddev config` wrote. The
site is at `https://<directory>.ddev.site`.

### On an existing DDEV project

Add the distribution to a Drupal project of your own, then install its profile:

```shell
ddev composer require webship/cucumber:~12.0
ddev drush site:install cucumber --account-name=webmaster --account-pass=<password> -y
```

The root `composer.json` has to relax the stability first, as described under
Requirements — otherwise Composer refuses the non-stable dependencies.

The installer asks which user roles, recipes and demo to add. The
defaults install the Admin role and all three recipes, with no demo.
To answer in a browser instead, run `ddev launch` and follow the installer.

### Without DDEV

The same two paths work with a Composer, PHP and database stack of your own:

```shell
composer create-project drupal/cucumber_project:~12.0 my-site --no-interaction
cd my-site
bin/drush site:install cucumber --account-name=webmaster --account-pass=<password> -y
```

Drush lives at `bin/drush`, not `vendor/bin/drush`: the profile sets the
Composer `bin-dir` to `bin/`.


## Usage

Log in and create a "Feature" media item at `media/add/feature`: paste
or upload a Gherkin script and file it in a feature directory. The
front page is the default dashboard; features are listed at `features`.


## Testing

A [webship-js](https://www.npmjs.com/package/webship-js) suite (Playwright
and Cucumber-js) lives in `tests/`. See `tests/README.md`.
