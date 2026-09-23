<?php

/**
 * @file
 * Contains hook implementations and callbacks for the Cucumber installer.
 *
 * @internal
 *   Everything in the Cucumber installer is internal and may be changed or
 *   removed at any time without warning.
 */

declare(strict_types=1);

use Drupal\Core\Batch\BatchBuilder;
use Drupal\Core\Extension\ExtensionDiscovery;
use Drupal\Core\Extension\ModuleExtensionList;
use Drupal\Core\Extension\ModuleInstallerInterface;
use Drupal\Core\Extension\Requirement\RequirementSeverity;
use Drupal\Core\Extension\ThemeExtensionList;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Recipe\Recipe;
use Drupal\Core\Recipe\RecipeRunner;
use Drupal\cucumber\ComposerExecutor;
use Drupal\cucumber\Form\SiteNameForm;
use Drupal\cucumber\Form\SiteTemplateForm;
use Drupal\cucumber\RecipeHandler;
use Drupal\cucumber\SiteTemplate;

/**
 * Implements hook_form_FORM_ID_alter() for install_settings_form().
 *
 * Cucumber runs happily on SQLite, which needs no database server, so it is
 * offered as the default.
 */
function cucumber_form_install_settings_form_alter(array &$form, FormStateInterface $form_state): void {
  $form['driver']['#default_value'] = 'sqlite';
  if (isset($form['settings']['sqlite']['database'])) {
    $form['settings']['sqlite']['database']['#default_value'] = '../database/cucumber.sqlite';
  }
}

/**
 * Implements hook_form_FORM_ID_alter() for install_configure_form().
 */
function cucumber_form_install_configure_form_alter(array &$form, FormStateInterface $form_state): void {
  $form['site_information']['site_mail']['#default_value'] = 'info@webship.co';
  $form['admin_account']['account']['mail']['#default_value'] = 'info@webship.co';
}

/**
 * Implements hook_install_tasks_alter().
 */
function cucumber_install_tasks_alter(array &$tasks, array $install_state): void {
  // Cucumber is installed in English. Translations are not downloaded, so
  // there is nothing to choose between.
  unset($tasks['install_select_language'], $tasks['install_download_translation']);

  $insert_before = function (string $key, array $additions) use (&$tasks): void {
    $key = array_search($key, array_keys($tasks), TRUE);
    if ($key === FALSE) {
      return;
    }
    // This isn't very clean, but it's the only way to positionally splice
    // into an associative (and therefore by definition unordered) array.
    $tasks_before = array_slice($tasks, 0, $key, TRUE);
    $tasks_after = array_slice($tasks, $key, NULL, TRUE);
    $tasks = $tasks_before + $additions + $tasks_after;
  };

  // When we install the profile itself, we'll also need User to configure the
  // site and administrator account.
  $install_profile_task = [
    'function' => 'cucumber_install_profile',
  ] + $tasks['install_install_profile'];

  $configure_form_task = $tasks['install_configure_form'];
  unset($tasks['install_install_profile'], $tasks['install_configure_form']);

  // Before applying any recipes:
  // - Install the profile itself.
  // - Choose a name for the site.
  // - Choose a site template.
  // - Set up the administrator account.
  $insert_before('install_profile_modules', [
    'install_install_profile' => $install_profile_task,
    'cucumber_set_site_name' => [
      'display_name' => t('Name your site'),
      'type' => 'form',
      'run' => $install_state['parameters'][SiteNameForm::TASK_ID] ?? INSTALL_TASK_RUN_IF_REACHED,
      'function' => SiteNameForm::class,
    ],
    'cucumber_choose_template' => [
      'display_name' => t('Choose site template'),
      'run' => $install_state['parameters'][SiteTemplateForm::TASK_ID] ?? INSTALL_TASK_RUN_IF_REACHED,
    ],
    'install_configure_form' => $configure_form_task,
  ]);

  // Wrap the install_profile_modules() function, which returns a batch job, and
  // add all the necessary operations to apply the chosen template recipe.
  $tasks['install_profile_modules']['function'] = 'cucumber_apply_recipes';

  // When the installation is finished, perform additional clean-up tasks (i.e.,
  // uninstall this profile).
  $tasks['install_finished']['function'] = 'cucumber_finished';
}

/**
 * Installs the install profile.
 *
 * This is only overridden to ensure that User is installed first, since we need
 * it and its configuration to set up the administrator account properly.
 *
 * @param array $install_state
 *   The current install state.
 */
function cucumber_install_profile(array &$install_state): void {
  Drupal::service(ModuleInstallerInterface::class)->install(['user']);
  install_install_profile($install_state);
}

/**
 * Selects a site template, or presents a form to choose one.
 *
 * @param array $install_state
 *   The current install state.
 *
 * @return array|null
 *   The form to display, or NULL if it was submitted programmatically.
 */
function cucumber_choose_template(array &$install_state): ?array {
  $recipes = Drupal::service(RecipeHandler::class)
    // Always apply the administrator role recipe.
    ->enqueue('core/recipes/administrator_role')
    ->scan('Site');

  // Put the discovered recipes into $install_state because we have no other way
  // to pass them to the form.
  $install_state['recipes'] = array_map(
    SiteTemplate::createFromRecipe(...),
    iterator_to_array($recipes),
  );

  $was_interactive = $install_state['interactive'];
  // If there's only one recipe, submit the form programmatically.
  if (count($install_state['recipes']) === 1) {
    $install_state['interactive'] = FALSE;
  }
  $return = install_get_form(SiteTemplateForm::class, $install_state);
  $install_state['interactive'] = $was_interactive;
  unset($install_state['recipes']);

  return $return;
}

/**
 * Install task to apply all queued recipes.
 *
 * Recipes that are not yet in the code base will be required using Composer,
 * then applied in a subsequent batch job.
 *
 * @param array $install_state
 *   The current install state.
 *
 * @return array
 *   A batch job to execute.
 */
function cucumber_apply_recipes(array &$install_state): array {
  $list = Drupal::service(RecipeHandler::class)->list();
  // If we've already applied all the queued recipes, there's nothing to do.
  // Since we only start applying recipes once `install_profile_modules()` has
  // finished, we can be safely certain that we already did that step.
  if (empty($list)) {
    return [];
  }
  // Let `install_profile_modules()` generate the initial batch job, to which
  // we will add operations.
  $batch = [
    'title' => t('Setting up your site'),
  ] + install_profile_modules($install_state);

  $operations = [];
  foreach ($list as $locator) {
    // If the locator is a directory, the recipe is already present in the
    // code base and we just need to apply it as per usual.
    if (is_dir($locator)) {
      $recipe = Recipe::createFromDirectory($locator);
      $operations = array_merge($operations, RecipeRunner::toBatchOperations($recipe));
      $operations[] = ['_cucumber_mark_recipe_applied', [$locator]];
    }
    // Otherwise, prepend an operation to require the recipe via Composer,
    // then generate an additional batch job to apply it. We prepend the
    // operation so that all the necessary dependencies will be physically
    // present before we apply or install anything.
    else {
      array_unshift($batch['operations'], ['_cucumber_require_recipe', [$locator]]);
      $batch['init_message'] = t('Installing %name. This may take a few minutes.', [
        '%name' => $locator,
      ]);
    }
  }

  // Only do each recipe's batch operations once.
  foreach ($operations as $operation) {
    if (!in_array($operation, $batch['operations'], TRUE)) {
      $batch['operations'][] = $operation;
    }
  }
  return $batch;
}

/**
 * Uninstalls the profile.
 *
 * @param array $install_state
 *   The current install state.
 */
function cucumber_finished(array &$install_state): void {
  Drupal::service(ModuleInstallerInterface::class)->uninstall([
    'cucumber',
  ]);
  install_finished($install_state);

  // Clear all previous status messages to avoid clutter, including the
  // pointless "Congratulations, you installed Drupal!" message set by
  // `install_finished()`.
  $messenger = Drupal::messenger();
  $messenger->deleteByType($messenger::TYPE_STATUS);
}

/**
 * Uses Composer to install a recipe, then queues a batch job to apply it.
 *
 * @param string $package_name
 *   The name of the package to install.
 * @param array $context
 *   The current batch context.
 */
function _cucumber_require_recipe(string $package_name, array &$context): void {
  // Allow the recipe to scaffold files into the project; for example, a site
  // template may wish to provide a default AGENTS.md file at the project root.
  ComposerExecutor::execute(
    'config',
    'extra.drupal-scaffold.allowed-packages',
    '--merge',
    '--json',
    json_encode([$package_name], JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR),
  );
  ComposerExecutor::execute(
    'require',
    $package_name,
    '--minimal-changes',
    '--update-with-all-dependencies',
  );

  // Since the list of available extensions has changed, we need to reset all
  // extension discovery caches. Reflection is the only real way to do this.
  (new ReflectionProperty(ExtensionDiscovery::class, 'files'))
    ->setValue(NULL, []);
  Drupal::service(ModuleExtensionList::class)->reset();
  Drupal::service(ThemeExtensionList::class)->reset();

  // We have the recipe, so generate a batch job to apply it.
  $batch = new BatchBuilder();
  $directory = Drupal::service(RecipeHandler::class)->getPath($package_name);
  $recipe = Recipe::createFromDirectory($directory);
  foreach (RecipeRunner::toBatchOperations($recipe) as [$callable, $arguments]) {
    $batch->addOperation($callable, $arguments);
  }
  $batch->addOperation('_cucumber_mark_recipe_applied', [$package_name]);
  batch_set($batch->toArray());

  $context['message'] = t('Installed @name', ['@name' => $recipe->name]);
}

/**
 * Marks a particular recipe as having been applied.
 *
 * This is done to increase fault tolerance. On hosting plans that don't have
 * a ton of RAM or computing power to spare, the possibility of the installer
 * timing out or failing in mid-stream is increased. Tracking the recipes which
 * have been applied allows the installer to recover and "pick up where it left
 * off", without applying recipes that have already been applied successfully.
 * Once the install is done, the list of recipes is deleted.
 *
 * @param string $locator
 *   The path, or package name, of a recipe.
 */
function _cucumber_mark_recipe_applied(string $locator): void {
  Drupal::service(RecipeHandler::class)->markAsApplied($locator);
}

/**
 * Implements hook_theme().
 */
function cucumber_theme(): array {
  return [
    'form_element__site_template' => [
      'base hook' => 'form_element',
    ],
  ];
}

/**
 * Implements template_preprocess_form_element() for site template choices.
 */
function cucumber_preprocess_form_element(array &$variables, string $hook): void {
  if ($hook !== 'form_element__site_template') {
    return;
  }

  /** @var \Drupal\cucumber\SiteTemplate $template */
  $template = $variables['element']['item'];
  $variables['screenshot'] = $template->getScreenshot();
  // Open all links in a new tab.
  foreach ($template->links as $link) {
    $link->getUrl()->setOption('attributes', ['target' => '_blank']);
  }
  $variables['links'] = $template->links;
  $variables['price'] = $template->price;
  $variables['purchase_url'] = $template->purchaseUrl;
  $variables['creator'] = $template->creator;

  $variables['name'] = $variables['element']['#title'];
  $variables['id'] = $variables['element']['#id'];
}

/**
 * Implements hook_preprocess_install_page().
 */
function cucumber_preprocess_install_page(array &$variables): void {
  // Cucumber has custom styling for the install page.
  $variables['#attached']['library'][] = 'cucumber/install-page';
}

/**
 * Implements hook_requirements().
 */
function cucumber_requirements(string $phase): array {
  $requirements = [];
  if (!extension_loaded('yaml')) {
    $requirements['php_yaml_extension'] = [
      'title' => t('PHP YAML extension'),
      'description' => t('The PHP YAML extension is not enabled. It is recommended that you enable the PHP YAML extension for your server.'),
      'severity' => RequirementSeverity::Warning,
    ];
  }

  if ($phase === 'install') {
    // SQLite is the default database driver offered by the installer.
    $has_sqlite = extension_loaded('pdo_sqlite');
    $requirements['cucumber_sqlite'] = [
      'title' => t('SQLite database driver'),
      'value' => $has_sqlite ? t('Enabled') : t('Not enabled'),
      'severity' => $has_sqlite ? RequirementSeverity::OK : RequirementSeverity::Warning,
      'description' => $has_sqlite
        ? t('The PDO SQLite extension is enabled on your server, so a SQLite database can be used.')
        : t('The PDO SQLite extension is not enabled on your server. Another database, such as MySQL or PostgreSQL, has to be used.'),
    ];
  }

  return $requirements;
}
