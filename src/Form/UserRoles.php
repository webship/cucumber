<?php

namespace Drupal\cucumber\Form;

use Symfony\Component\Yaml\Yaml;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

class UserRoles extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'cucumber_user_roles';
  }

  public function buildForm(array $form, FormStateInterface $form_state) {

    // Cucumber User Roles.
    $user_roles_file = DRUPAL_ROOT . '/' . \Drupal::service('extension.list.profile')->getPath('cucumber') . '/config/install_tasks/user_roles.yml';

    if (file_exists($user_roles_file)) {
      $user_roles_content = file_get_contents($user_roles_file);
      $user_roles = (array) Yaml::parse($user_roles_content);

      $form['#title'] = $this->t($user_roles['user_roles']['display_name']);
      $form['description'] = [
        '#weight' => -1,
        '#prefix' => '<p>',
        '#markup' => $this->t($user_roles['user_roles']['description']),
        '#suffix' => '</p>',
      ];

      $form['user_roles'] = [
        "#name" => "user_roles",
        '#type' => 'fieldset',
      ];

      $user_roles_options = $user_roles['user_roles']['options'];

      foreach ($user_roles_options as $user_roles_key => $user_roles_info) {
        $form['user_roles'][$user_roles_key] = [
          '#type' => 'checkbox',
          '#title' => $user_roles_info['title'],
          '#description' => $user_roles_info['description'],
          '#default_value' => (bool) $user_roles_info['selected'],
          '#disabled' => $user_roles_info['disabled'],
        ];
      }
    }

    $form['actions'] = [
      'continue' => [
        '#type' => 'submit',
        '#value' => $this->t('Install User Roles'),
        '#button_type' => 'primary',
      ],
      '#type' => 'actions',
      '#weight' => 5,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $user_roles_file = DRUPAL_ROOT . '/' . \Drupal::service('extension.list.profile')->getPath('cucumber') . '/config/install_tasks/user_roles.yml';

    $user_roles_content = file_get_contents($user_roles_file);
    $user_roles = (array) Yaml::parse($user_roles_content);

    $user_roles_selected = 0;
    $user_roles_options = $user_roles['user_roles']['options'];

    foreach ($user_roles_options as $user_roles_key => $user_roles_info) {
      if ($form_state->getValue($user_roles_key) == 1) {
        $user_roles_selected += 1;
      }
    }

    if ($user_roles_selected == 0) {
      $form_state->setErrorByName('user_roles', $this->t('Please select at least one user roles'));
      $form_state->setRebuild();
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    // Cucumber User Roles.
    $user_roles_file = DRUPAL_ROOT . '/' . \Drupal::service('extension.list.profile')->getPath('cucumber') . '/config/install_tasks/user_roles.yml';

    $user_roles_content = file_get_contents($user_roles_file);
    $user_roles = (array) Yaml::parse($user_roles_content);

    $user_roles_options = $user_roles['user_roles']['options'];

    foreach ($user_roles_options as $user_roles_key => $user_roles_info) {

      if ($user_roles_key != "admin" && $form_state->getValue($user_roles_key) == 1) {
        $installer = \Drupal::service('module_installer');
        $installer->install([$user_roles_info['source_config']]);
      }
    }
  }

}
