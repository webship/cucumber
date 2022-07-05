<?php

/**
 * @file
 * Site configuration for Cucumber site installation.
 */

/**
 * Implements hook_install_tasks_alter().
 */
function cucumber_install_tasks_alter(&$tasks, $install_state) {
  unset($tasks['install_select_language']);
  unset($tasks['install_download_translation']);
}

/**
 * Implements hook_preprocess_install_page().
 */
function cucumber_preprocess_install_page(&$variables) {
  // Cucumber has custom styling for the install page.
  $variables['#attached']['library'][] = 'cucumber/install-page';
}
