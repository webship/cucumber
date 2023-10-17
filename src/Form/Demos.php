<?php

namespace Drupal\cucumber\Form;

use Symfony\Component\Yaml\Yaml;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

class Demos extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'cucumber_demos';
  }

  public function buildForm(array $form, FormStateInterface $form_state) {
    
    // Cucumber Demo list.
    $demos_file = DRUPAL_ROOT . '/' . \Drupal::service('extension.list.profile')->getPath('cucumber') . '/config/install_tasks/demos.yml';

    if (file_exists($demos_file)) {
      $demos_content = file_get_contents($demos_file);
      $demos = (array) Yaml::parse($demos_content);
      
      $form['#title'] = $this->t($demos['demos']['display_name']);
      $form['description'] = [
        '#weight' => -1,
        '#prefix' => '<p>',
        '#markup' => $this->t($demos['demos']['description']),
        '#suffix' => '</p>',
      ];

      $form['demos'] = [
        "#name" => "demos",
        '#type' => 'fieldset',
      ];
    
      $options = array();
      $demo_options = $demos['demos']['options'];

      foreach ($demo_options as $demo_key => $demo_info) {
        $options[$demo_key] = t($demo_info['title']);
      }

      $form['demos']['cucumber_demos'] = array(
        '#type' => 'select',
        '#name' => 'cucumber_demos',
        '#default_value' => 'non_demo',
        '#options' => $options
      );
    }

    $form['actions'] = [
      'continue' => [
        '#type' => 'submit',
        '#value' => $this->t('Install Demo'),
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
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    
    $demos_file = DRUPAL_ROOT . '/' . \Drupal::service('extension.list.profile')->getPath('cucumber') . '/config/install_tasks/demos.yml';

    $demos_content = file_get_contents($demos_file);
    $demos = (array) Yaml::parse($demos_content);
    
    $demo_key = $form_state->getValue('cucumber_demos');
    $source_name = $demos['demos']['options'][$demo_key]['source_name'];

    if ($demo_key != 'non_demo') {
      $installer = \Drupal::service('module_installer');
      $installer->install([$source_name]);
    }
  }
}