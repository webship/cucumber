<?php

/**
 * @file
 * Site configuration for Cucumber app.webship.co site installation.
 */

use Drupal\Core\Form\FormStateInterface;

/**
 * Implements hook_form_FORM_ID_alter() for install_configure_form().
 *
 * Allows the profile to alter the site configuration form.
 */
function cucumber_form_install_configure_form_alter(&$form, FormStateInterface $form_state)
{
    $form['site_information']['site_name']['#default_value'] = t('Cucumber app.webship.co');
    $form['site_information']['site_mail']['#default_value'] = 'admin@webship.co';
    $form['admin_account']['account']['name']['#default_value'] = 'webmaster';
    $form['admin_account']['account']['mail']['#default_value'] = 'admin@webship.co';
}

/**
 * Implements hook_install_tasks_alter().
 */
function cucumber_install_tasks_alter(&$tasks, $install_state)
{
    unset($tasks['install_select_language']);
    unset($tasks['install_download_translation']);
}

/**
 * Implements hook_preprocess_install_page().
 */
function cucumber_preprocess_install_page(&$variables)
{
    // Cucumber has custom styling for the install page.
    $variables['#attached']['library'][] = 'cucumber/install-page';
}

/**
 * Implements hook_install_tasks().
 */
function cucumber_install_tasks() {
  $tasks = array();
  
  // Define the task to set SQLite as the default database.
  $tasks['cucumber_set_sqlite_default'] = array(
    'display_name' => 'Set SQLite as default database',
    'type' => 'function',
    'function' => 'cucumber_set_sqlite_default',
  );
  
  return $tasks;
}

/**
 * Sets SQLite as the default database.
 */
function cucumber_set_sqlite_default() {
  // Get the path to the settings.php file.
  $settings_file = DRUPAL_ROOT . '/sites/default/settings.php';
  
  // Read the contents of the settings.php file.
  $settings_contents = file_get_contents($settings_file);
  
  // Replace the default database driver.
  $settings_contents = str_replace("'mysql'", "'sqlite'", $settings_contents);
  
  // Write the modified contents back to the settings.php file.
  file_put_contents($settings_file, $settings_contents);
}