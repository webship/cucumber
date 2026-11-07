<?php

namespace Drupal\cucumber\Form;

use Drupal\Component\Serialization\Yaml;
use Drupal\Core\Extension\ModuleInstallerInterface;
use Drupal\Core\Extension\ProfileExtensionList;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class Recipes extends FormBase {

  /**
   * Constructs the form.
   */
  public function __construct(
    protected ProfileExtensionList $profileList,
    protected ModuleInstallerInterface $moduleInstaller,
  ) {}

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('extension.list.profile'),
      $container->get('module_installer'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'cucumber_recipes';
  }

  public function buildForm(array $form, FormStateInterface $form_state) {

    // Cucumber Recipes.
    $recipes_file = DRUPAL_ROOT . '/' . $this->profileList->getPath('cucumber') . '/config/install_tasks/recipes.yml';

    if (file_exists($recipes_file)) {
      $recipes_content = file_get_contents($recipes_file);
      $recipes = (array) Yaml::decode($recipes_content);

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
    $recipes_file = DRUPAL_ROOT . '/' . $this->profileList->getPath('cucumber') . '/config/install_tasks/recipes.yml';

    $recipes_content = file_get_contents($recipes_file);
    $recipes = (array) Yaml::decode($recipes_content);

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
    $recipes_file = DRUPAL_ROOT . '/' . $this->profileList->getPath('cucumber') . '/config/install_tasks/recipes.yml';

    $recipes_content = file_get_contents($recipes_file);
    $recipes = (array) Yaml::decode($recipes_content);

    $recipe_options = $recipes['recipes']['options'];

    foreach ($recipe_options as $recipe_key => $recipe_info) {

      if ($form_state->getValue($recipe_key) == 1) {
        $this->moduleInstaller->install([$recipe_info['source_name']]);
      }
    }
  }

}
