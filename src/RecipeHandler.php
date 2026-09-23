<?php

declare(strict_types=1);

namespace Drupal\cucumber;

use Composer\InstalledVersions;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Recipe\Recipe;
use Drupal\Core\Recipe\RecipeFileException;
use Drupal\Core\State\StateInterface;
use Symfony\Component\Finder\Finder;

/**
 * Defines a service for finding and queueing recipes for installation.
 *
 * @internal
 *   Everything in the Cucumber installer is internal and may be changed or
 *   removed at any time without warning. External code should not interact
 *   with this class.
 */
final class RecipeHandler {

  /**
   * The state key under which we store the list of recipes to apply.
   */
  public const string STATE_KEY = 'cucumber.recipe_list';

  /**
   * The directory where recipes are stored, extrapolated from `composer.json`.
   *
   * @see ::getRecipeDirectory()
   *
   * @var string|null
   */
  private ?string $recipeDirectory = NULL;

  public function __construct(
    private readonly StateInterface $state,
    private readonly MessengerInterface $messenger,
  ) {}

  /**
   * Scans the recipe directory for recipes.
   *
   * @param string|null $type
   *   (optional) Only yield recipes whose `type` key is equal to this value.
   *
   * @return iterable<string, \Drupal\Core\Recipe\Recipe>
   */
  public function scan(?string $type = NULL): iterable {
    // The general recipe directory will not have package-specific placeholders,
    // because that makes no sense.
    $directory = str_replace(['{$name}', '{$vendor}'], '', $this->getRecipeDirectory());
    $directory = rtrim($directory, DIRECTORY_SEPARATOR);

    $finder = Finder::create()
      ->in($directory)
      ->files()
      ->followLinks()
      ->name('recipe.yml');

    foreach ($finder as $file) {
      try {
        $recipe = Recipe::createFromDirectory($file->getPath());
        if (empty($type) || $recipe->type === $type) {
          yield basename($recipe->path) => $recipe;
        }
      }
      catch (RecipeFileException $e) {
        $this->messenger->addError($e->getMessage());
      }
    }
  }

  /**
   * Returns the directory where recipes are installed.
   *
   * Placeholder tokens, like {$name} and {$vendor}, are left as-is. If the
   * recipe directory cannot be determined, returns NULL.
   */
  private function getRecipeDirectory(): ?string {
    if (isset($this->recipeDirectory)) {
      return $this->recipeDirectory;
    }
    ['install_path' => $project_root] = InstalledVersions::getRootPackage();
    $project_root = realpath($project_root);
    assert(is_string($project_root));

    $file = $project_root . DIRECTORY_SEPARATOR . 'composer.json';
    $data = file_get_contents($file);
    $data = json_decode($data, TRUE, flags: JSON_THROW_ON_ERROR);

    $directory = array_find_key(
      $data['extra']['installer-paths'] ?? [],
      fn (array $criteria): bool => in_array('type:' . Recipe::COMPOSER_PROJECT_TYPE, $criteria, TRUE),
    );
    if ($directory) {
      $this->recipeDirectory = $project_root . DIRECTORY_SEPARATOR . $directory;
    }
    return $this->recipeDirectory;
  }

  /**
   * Returns the path of a specific recipe.
   *
   * @param string $package_name
   *   The name of the recipe package.
   */
  public function getPath(string $package_name): string {
    try {
      return InstalledVersions::getInstallPath($package_name);
    }
    catch (\OutOfBoundsException $e) {
      // Composer doesn't know where it is, so try to extrapolate the path.
      return str_replace(
        ['{$vendor}', '{$name}'],
        explode('/', $package_name, 2),
        $this->getRecipeDirectory() ?? throw $e,
      );
    }
  }

  /**
   * Returns a list of recipes that will be installed.
   *
   * @return list<string>
   *   A mix of recipe directory paths and Composer package names.
   */
  public function list(): array {
    return $this->state->get(self::STATE_KEY, []);
  }

  /**
   * Queues one or more recipes for installation.
   *
   * @param string ...$locators
   *   The local paths, or Composer package names, of the recipes to install.
   */
  public function enqueue(string ...$locators): self {
    $list = array_unique(array_merge($this->list(), $locators));
    $this->state->set(self::STATE_KEY, array_values($list));
    return $this;
  }

  /**
   * Marks one or more recipes as applied.
   *
   * @param string ...$locators
   *   The local paths, or Composer package names, of the recipes that have been
   *   applied.
   */
  public function markAsApplied(string ...$locators): self {
    $list = array_diff($this->list(), $locators);
    if ($list) {
      $this->state->set(self::STATE_KEY, array_values($list));
    }
    else {
      $this->state->delete(self::STATE_KEY);
    }
    return $this;
  }

}
