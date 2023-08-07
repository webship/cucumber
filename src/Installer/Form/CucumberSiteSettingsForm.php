<?php

namespace Drupal\Cucmber\src\Installer\Form;

use Drupal\Component\Utility\Crypt;
use Drupal\Core\Database\Database;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\Site\Settings;
use Drupal\Core\Site\SettingsEditor;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a form to configure and rewrite settings.php.
 *
 * @internal
 */
class CucumberSiteSettingsForm extends SiteSettingsForm {

  
  /**
   * Constructs a new SiteSettingsForm.
   *
   * @param string $site_path
   *   The site path.
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The renderer.
   */
  public function __construct($site_path, RendererInterface $renderer) {
    parent::__construct($site_path, $renderer);
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    // Make sure the install API is available.
    include_once DRUPAL_ROOT . '/core/includes/install.inc';
    $settings_file = './' . $this->sitePath . '/settings.php';

    $form['#title'] = $this->t('Database configuration');

    $drivers = drupal_get_database_types();
    $drivers_keys = array_keys($drivers);

    // Unless there is input for this form (for a non-interactive installation,
    // input originates from the $settings array passed into install_drupal()),
    // check whether database connection settings have been prepared in
    // settings.php already.
    // Note: The installer even executes this form if there is a valid database
    // connection already, since the submit handler of this form is responsible
    // for writing all $settings to settings.php (not limited to $databases).
    $input = &$form_state->getUserInput();
    if (!isset($input['driver']) && $database = Database::getConnectionInfo()) {
      // $database['default']['driver'] = 'sqlite';
      $input['driver'] = $database['default']['driver'];
      $input[$database['default']['driver']] = $database['default'];
    }

    if (isset($input['driver'])) {
      $default_driver = $input['driver'];
      // In case of database connection info from settings.php, as well as for a
      // programmed form submission (non-interactive installer), the table prefix
      // information is usually normalized into an array already, but the form
      // element only allows to configure one default prefix for all tables.
      $prefix = &$input[$default_driver]['prefix'];
      if (isset($prefix) && is_array($prefix)) {
        $prefix = $prefix['default'];
      }
      $default_options = $input[$default_driver];
    }
    // If there is no database information yet, suggest the first available driver
    // as default value, so that its settings form is made visible via #states
    // when JavaScript is enabled (see below).
    else {
      $default_driver = current($drivers_keys);
      $default_options = [];
    }

    $form['driver'] = [
      '#type' => 'radios',
      '#title' => $this->t('Database type'),
      '#required' => TRUE,
      '#default_value' => $default_driver,
    ];
    if (count($drivers) == 1) {
      $form['driver']['#disabled'] = TRUE;
    }

    // Add driver specific configuration options.
    foreach ($drivers as $key => $driver) {
      $form['driver']['#options'][$key] = $driver->name();

      $form['settings'][$key] = $driver->getFormOptions($default_options);
      $form['settings'][$key]['#prefix'] = '<h2 class="js-hide">' . $this->t('@driver_name settings', ['@driver_name' => $driver->name()]) . '</h2>';
      $form['settings'][$key]['#type'] = 'container';
      $form['settings'][$key]['#tree'] = TRUE;
      $form['settings'][$key]['advanced_options']['#parents'] = [$key];
      $form['settings'][$key]['#states'] = [
        'visible' => [
          ':input[name=driver]' => ['value' => $key],
        ],
      ];
    }

    $form['actions'] = ['#type' => 'actions'];
    $form['actions']['save'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save and continue'),
      '#button_type' => 'primary',
      '#limit_validation_errors' => [
        ['driver'],
        [$default_driver],
      ],
      '#submit' => ['::submitForm'],
    ];

    $form['errors'] = [];
    $form['settings_file'] = ['#type' => 'value', '#value' => $settings_file];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    global $install_state;

    // Make sure the install API is available.
    include_once DRUPAL_ROOT . '/core/includes/install.inc';

    // Update global settings array and save.
    $settings = [];
    $database = $form_state->get('database');
    $settings['databases']['default']['default'] = (object) [
      'value'    => $database,
      'required' => TRUE,
    ];
    $settings['settings']['hash_salt'] = (object) [
      'value'    => Crypt::randomBytesBase64(55),
      'required' => TRUE,
    ];
    // If settings.php does not contain a config sync directory name we need to
    // configure one.
    if (empty(Settings::get('config_sync_directory'))) {
      if (empty($install_state['config_install_path'])) {
        // Add a randomized config directory name to settings.php
        $config_sync_directory = $this->createRandomConfigDirectory();
      }
      else {
        // Install profiles can contain a config sync directory. If they do,
        // 'config_install_path' is a path to the directory.
        $config_sync_directory = $install_state['config_install_path'];
      }
      $settings['settings']['config_sync_directory'] = (object) [
        'value' => $config_sync_directory,
        'required' => TRUE,
      ];
    }

    SettingsEditor::rewrite($this->sitePath . '/settings.php', $settings);

    // Indicate that the settings file has been verified, and check the database
    // for the last completed task, now that we have a valid connection. This
    // last step is important since we want to trigger an error if the new
    // database already has Drupal installed.
    $install_state['settings_verified'] = TRUE;
    $install_state['config_verified'] = TRUE;
    $install_state['database_verified'] = TRUE;
    $install_state['completed_task'] = install_verify_completed_task();
  }
}
