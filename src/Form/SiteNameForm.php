<?php

declare(strict_types=1);

namespace Drupal\cucumber\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Defines a form to set the site name.
 *
 * @internal
 *   Everything in the Cucumber installer is internal and may be changed or
 *   removed at any time without warning. External code should not interact
 *   with this class.
 */
final class SiteNameForm extends ConfigFormBase {

  /**
   * An identifier for this task, to mark it as completed.
   */
  public const string TASK_ID = 'name';

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'installer_site_name_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames(): array {
    return ['system.site'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, array $install_state = []): array {
    $form['site_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Site name'),
      '#required' => TRUE,
      '#default_value' => $install_state['forms']['install_configure_form']['site_name'] ?? $this->t('My Cucumber site'),
    ];
    $form['actions'] = [
      '#type' => 'actions',
      'submit' => [
        '#type' => 'submit',
        '#value' => $this->t('Next'),
        '#button_type' => 'primary',
      ],
    ];
    $form['#title'] = $this->t('Give your site a name');

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    // Set the configuration directly so we won't see a success message.
    $this->config('system.site')
      ->set('name', $form_state->getValue('site_name'))
      ->save();

    // Mark the task as finished.
    $GLOBALS['install_state']['parameters'][self::TASK_ID] = INSTALL_TASK_SKIP;
  }

}
