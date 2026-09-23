<?php

namespace Drupal\cucumber\Form;

use Composer\InstalledVersions;
use Drupal\Component\Serialization\Yaml;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\DependencyInjection\AutowireTrait;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\cucumber\ComposerExecutor;
use Drupal\cucumber\RecipeHandler;
use Drupal\cucumber\SiteTemplate;
use GuzzleHttp\ClientInterface;
use Psr\Http\Client\ClientExceptionInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Yaml\Exception\ParseException;

/**
 * Defines a form to choose a site template.
 *
 * @internal
 *   Everything in the Cucumber installer is internal and may be changed or
 *   removed at any time without warning. External code should not interact
 *   with this class.
 */
final class SiteTemplateForm extends FormBase {

  use AutowireTrait {
    create as traitCreate;
  }

  /**
   * An identifier for this task, to mark it as completed.
   */
  public const string TASK_ID = 'template';

  private readonly string $sitePath;

  public function __construct(
    private readonly ClientInterface $http,
    private readonly RecipeHandler $recipeHandler,
    #[Autowire(service: 'cache.default')] private readonly CacheBackendInterface $cache,
  ) {}

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): self {
    $form = self::traitCreate($container);
    $form->sitePath = $container->getParameter('site.path');
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'installer_site_template_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, ?array $install_state = NULL): array {
    // @see cucumber_choose_template()
    $all_choices = $install_state['recipes'] ?? [];

    // Load additional choices. If any of them are already in the code base, the
    // ones that are physically present will "win".
    $all_choices += array_map(
      fn (array $values): SiteTemplate => new SiteTemplate(...$values),
      $this->getCuratedList(),
    );

    // The site template Cucumber is built around; pre-selected, and used when
    // installing non-interactively (e.g., via Drush).
    $default = 'cucumber_starter';
    // Must be called `add_ons` to agree with the theme.
    $form['add_ons'] = [
      '#options' => [],
      '#type' => 'radios',
      '#required' => TRUE,
      '#default_value' => array_key_exists($default, $all_choices) ? $default : array_key_first($all_choices),
    ];

    // Premium site templates may require an access (license) key.
    $form['access_key'] = [
      '#type' => 'container',
      '#theme_wrappers' => ['container__access_key'],
      '#tree' => TRUE,
    ];
    foreach ($all_choices as $key => $choice) {
      assert($choice instanceof SiteTemplate);

      $form['add_ons'][$key] = [
        '#theme_wrappers' => [
          'form_element__site_template' => ['item' => $choice],
        ],
        '#description' => $choice->description,
        '#locator' => $choice->locator,
        '#repository' => $choice->repository,
      ];
      $form['add_ons']['#options'][$key] = $choice->name;

      $form['access_key'][$key] = [
        '#type' => 'textfield',
        // Only visible when the associated site template is chosen.
        '#states' => [
          'visible' => ['input[name="add_ons"]' => ['value' => $key]],
        ],
        '#attributes' => [
          'data-for' => $key,
          'data-validation-url' => $choice->keyValidationUrl?->toString(),
          // Pattern is required for JS .checkValidity() function.
          'pattern' => '[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{12}',
          // Placeholder is required for CSS's show/hide functionality to work.
          'placeholder' => 'XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX',
        ],
        '#access' => $choice->price > 0,
      ];
    }
    if (array_key_exists($default, $form['add_ons'])) {
      $form['add_ons'][$default]['#weight'] = -100;
    }

    $form['actions'] = [
      'submit' => [
        '#type' => 'submit',
        '#value' => $this->t('Next'),
        '#button_type' => 'primary',
        '#op' => 'submit',
      ],
      '#type' => 'actions',
    ];
    $form['#title'] = $this->t('Choose a site template');

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state): void {
    parent::validateForm($form, $form_state);
    $choice = $form_state->getValue('add_ons');

    // If the package is provided by an alternate repository (i.e., not
    // Packagist or packages.drupal.org), make Composer aware of it.
    $repository = $form['add_ons'][$choice]['#repository'];
    if (empty($repository)) {
      return;
    }
    $url = parse_url($repository);
    $url = rtrim($url['host'] . ':' . ($url['port'] ?? ''), ':');
    // Only Composer repositories are supported.
    ComposerExecutor::execute('repository', 'add', hash('xxh3', $url), 'composer', $repository);

    // Alternate repositories might require an access key. If one was entered,
    // configure Composer to use it for this repository.
    $access_key = trim($form_state->getValue(['access_key', $choice]));
    if (empty($access_key)) {
      return;
    }

    // Confirm that the package is available on the repository. If it's not, the
    // access key might be invalid.
    $auth_key = 'bearer.' . $url;
    // @todo $access_key is user input, should we sanitize or validate it?
    //   Or just rely on Symfony's Process constructor handle that for us?
    ComposerExecutor::execute('config', $auth_key, $access_key);
    try {
      ComposerExecutor::execute('show', '--all', $form['add_ons'][$choice]['#locator']);
    }
    catch (ProcessFailedException) {
      $form_state->setErrorByName("access_key[$choice]", $this->t('The access key you entered did not grant access to the package. Contact the seller for support.'));
      ComposerExecutor::execute('config', '--unset', $auth_key);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $choice = $form_state->getValue('add_ons');
    $locator = $form['add_ons'][$choice]['#locator'];

    $this->recipeHandler->enqueue($locator);
    // Mark the task as finished.
    $GLOBALS['install_state']['parameters'][self::TASK_ID] = INSTALL_TASK_SKIP;
  }

  /**
   * Returns a curated list of site template information.
   *
   * @return array<string, array>
   *   An iterable of information about site templates, keyed by machine name.
   */
  private function getCuratedList(): iterable {
    $messenger = $this->messenger();

    // First and foremost, ensure the file system is writable. If it's not, then
    // there's no point in showing the list of site templates because you
    // probably won't be able to install any of them anyway.
    ['install_path' => $project_root] = InstalledVersions::getRootPackage();
    if (!is_writable($project_root)) {
      $messenger->addWarning(
        $this->t('Only showing site templates that are already downloaded, because %dir is not writable.', [
          '%dir' => realpath($project_root),
        ]),
      );
      return [];
    }

    // Allow the list of site templates to be defined per-site. This is helpful
    // for testing, or for hosts which want to limit the available choices. This
    // is an official extension point and can be relied upon.
    // @api
    $list = @include $this->sitePath . '/site-templates.php';
    if (is_iterable($list)) {
      return $list;
    }

    // If the original file exists, read it directly. It should not be included
    // in releases of the installer.
    // @see .gitattributes
    $file = dirname(__DIR__, 2) . '/site-templates.yml';
    if (file_exists($file)) {
      return Yaml::decode(file_get_contents($file));
    }

    // @see site-templates.yml
    $url = 'https://git.drupalcode.org/api/v4/projects/project%2Fcucumber/repository/files/site-templates.yml/raw?ref=12.0.x';
    $cid = hash('xxh32', $url);

    $cached = $this->cache->get($cid);
    if ($cached) {
      return $cached->data;
    }

    $list = [];
    try {
      $list = Yaml::decode(
        (string) $this->http->request('GET', $url)->getBody(),
      );
    }
    catch (ParseException | ClientExceptionInterface $e) {
      $messenger->addWarning($e->getMessage());
    }
    $this->cache->set($cid, $list);
    return $list;
  }

}
