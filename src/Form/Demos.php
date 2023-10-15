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
    $form['#title'] = $this->t('Cucumber Demos');
    $form['cucumber_demos_introduction'] = [
      '#weight' => -1,
      '#prefix' => '<p>',
      '#markup' => $this->t("Install demos of content in your site."),
      '#suffix' => '</p>',
    ];

    $form['demos'] = [
      "#name" => "demos",
      '#type' => 'fieldset',
      '#title' => $this->t('Site Demos'),
    ];
    

    // Cucumber Demo list.
    $demos_file = DRUPAL_ROOT . '/' . \Drupal::service('extension.list.profile')->getPath('cucumber') . '/config/install_tasks/demos.yml';

    if (file_exists($demos_file)) {
      $demos_content = file_get_contents($demos_file);
      $demos = (array) Yaml::parse($demos_content);
      
      $options = array();
      foreach ($demos as $demo_key => $demo_info) {
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
      
    $demo_key = $form_state->getValue('cucumber_demos');
      
    if ($demo_key != 'non_demo') {
      $installer = \Drupal::service('module_installer');
      $installer->install([$demo_key]);
    }
  }
}