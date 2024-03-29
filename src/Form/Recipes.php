<?php

namespace Drupal\cucumber\Form;

use Symfony\Component\Yaml\Yaml;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

class Recipes extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'cucumber_recipes';
  }

  public function buildForm(array $form, FormStateInterface $form_state) {

    // Cucumber Recipes.
    $recipes_file = DRUPAL_ROOT . '/' . \Drupal::service('extension.list.profile')->getPath('cucumber') . '/config/install_tasks/recipes.yml';

    if (file_exists($recipes_file)) {
      $recipes_content = file_get_contents($recipes_file);
      $recipes = (array) Yaml::parse($recipes_content);

      $form['#title'] = $this->t($recipes['recipes']['display_name']);
      $form['description'] = [
        '#weight' => -1,
        '#prefix' => '<p>',
        '#markup' => $this->t($recipes['recipes']['description']),
        '#suffix' => '</p>',
      ];

      $form['recipes'] = [
        "#name" => "recipes",
        '#type' => 'fieldset',
      ];

      $recipe_options = $recipes['recipes']['options'];

      foreach ($recipe_options as $recipe_key => $recipe_info) {
        $form['recipes'][$recipe_key] = [
          '#type' => 'checkbox',
          '#title' => $recipe_info['title'],
          '#description' => $recipe_info['description'],
          '#default_value' => (bool) $recipe_info['selected'],
        ];
      }
    }

    $form['actions'] = [
      'continue' => [
        '#type' => 'submit',
        '#value' => $this->t('Install Recipes'),
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
    $recipes_file = DRUPAL_ROOT . '/' . \Drupal::service('extension.list.profile')->getPath('cucumber') . '/config/install_tasks/recipes.yml';

    $recipes_content = file_get_contents($recipes_file);
    $recipes = (array) Yaml::parse($recipes_content);

    $recipes_selected = 0;
    $recipe_options = $recipes['recipes']['options'];

    foreach ($recipe_options as $recipe_key => $recipe_info) {
      if ($form_state->getValue($recipe_key) == 1) {
        $recipes_selected += 1;
      }
    }

    if ($recipes_selected == 0) {
      $form_state->setErrorByName('recipes', $this->t('Please select at least one recipe'));
      $form_state->setRebuild();
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    // Cucumber Recipes.
    $recipes_file = DRUPAL_ROOT . '/' . \Drupal::service('extension.list.profile')->getPath('cucumber') . '/config/install_tasks/recipes.yml';

    $recipes_content = file_get_contents($recipes_file);
    $recipes = (array) Yaml::parse($recipes_content);

    $recipe_options = $recipes['recipes']['options'];

    foreach ($recipe_options as $recipe_key => $recipe_info) {

      if ($form_state->getValue($recipe_key) == 1) {
        $installer = \Drupal::service('module_installer');
        $installer->install([$recipe_info['source_name']]);
      }
    }
  }

}
