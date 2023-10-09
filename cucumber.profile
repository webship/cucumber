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
 * Implements hook_form_FORM_ID_alter() for install_settings_form().
 *
 * Allows the profile to alter the site settings form.
 */
function cucumber_form_install_settings_form_alter(&$form, FormStateInterface $form_state)
{
  $form['driver']['#default_value'] = 'sqlite';
  $form['settings']['sqlite']['database']['#default_value'] = '../database/cucumber.sqlite';
  
  foreach ($form['driver']['#options'] as $key => $value) {
    if($key != "sqlite"){
      unset($form['driver']['#options'][$key]);
    }
  }
  
  return $form;
}

/**
 * Implements hook_install_tasks_alter().
 */
function cucumber_install_tasks_alter(&$tasks, $install_state) {
  unset($tasks['install_select_language']);
  unset($tasks['install_download_translation']);

  $settings_file = './sites/default/settings.php';
  $contents = file_get_contents('./sites/default/settings.php');

  if(!strpos($contents, 'Drupal\\Core\\Database\\Driver\\sqlite')) {
      $fp = fopen($settings_file, 'a');

      fwrite(
          $fp, 
          "
  \$databases['default']['default'] = array (
  'database' => '../database/cucumber.sqlite',
  'prefix' => '',
  'namespace' => 'Drupal\\Core\\Database\\Driver\\sqlite',
  'driver' => 'sqlite',
  );"
    );

    fclose($fp);
  }
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

    if ($phase === 'install') {
        // Check if the SQLite database driver is available.
        if (!extension_loaded('pdo_sqlite')) {
            $requirements['cucumber_sqlite'] = [
            'title' => t('SQLite Database Driver'),
            'value' => t('Enabled'),
            'severity' => REQUIREMENT_ERROR,
            'description' => t('The PDO SQLite extension is not enabled on your server. SQLite database is required for this site.'),
            ];
        }
        else {
            $requirements['cucumber_sqlite'] = [
            'title' => t('SQLite Database Driver'),
            'value' => t('Enabled'),
            'severity' => REQUIREMENT_OK,
            'description' => t('The PDO SQLite extension is enabled on your server. SQLite database is available.'),
            ];
        }
    }

    return $requirements;
}
