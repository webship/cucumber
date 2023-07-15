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

    // Modify the database type options in the form.
    $tasks['install_locale']['install_locale_settings']['database']['#options']['sqlite'] = t('SQLite');

    // Set the SQLite database file path.
    $tasks['install_locale']['install_locale_settings']['database']['#default_value'] = '../database/cucumber.sqlite';
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
 * Implements hook_requirements().
 */
function cucumber_requirements($phase)
{
    $requirements = [];
    if (!extension_loaded('yaml')) {
        $requirements['php_yaml_extension'] = [
          'title' => 'PHP YAML extension',
          'description' => t('The PHP YAML extension is not enabled. It is recommended that you enable the PHP YAML extension for your server.'),
          'severity' => REQUIREMENT_WARNING,
        ];
    }

    if (!extension_loaded('sqlite')) {
        $requirements['sqlite_extension'] = [
        'title' => 'SQLite extension',
        'description' => t('SQLite extension is not install. It is recommended that you install the SQLite extension for your server.'),
        'severity' => REQUIREMENT_WARNING,
        ];
    }

    return $requirements;
}
