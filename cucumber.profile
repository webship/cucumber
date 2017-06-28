<?php

/**
 * @file
 * Enables modules and site configuration for a Varbase site installation.
 */

/**
 * Implements hook_install_tasks().
 */
function cucumber_install_tasks($install_state){
  $tasks = array(
    'cucumber_post_install' => array(
      'display_name' => t('Post Install'),
      'display' => FALSE,
      'type' => 'normal'
    ),
  );

  return $tasks;
}

/**
 * Post install task.
 */
function cucumber_post_install() {

  \Drupal::service('theme_installer')->uninstall(['cucumber_install']);
}