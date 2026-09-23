<?php

declare(strict_types=1);

namespace Drupal\cucumber;

use Drupal\Component\Utility\UrlHelper;
use Drupal\Core\Link;
use Drupal\Core\Recipe\Recipe;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\Url;

/**
 * Defines a value object with information about a site template.
 *
 * @internal
 *   Everything in the Cucumber installer is internal and may be changed or
 *   removed at any time without warning. External code should not interact
 *   with this class.
 */
final readonly class SiteTemplate {

  /**
   * The path of the recipe in the file system, or its package name.
   */
  public string $locator;

  /**
   * The path, or URL, of a screenshot.
   */
  private string $screenshot;

  /**
   * Informational links about the site template.
   *
   * Demo, documentation, and so on. All must point to external URLs.
   *
   * @var list<\Drupal\Core\Link>
   */
  public array $links;

  /**
   * The price of the site template, or 0 if it is free.
   */
  public float $price;

  /**
   * A URL where the site template can be purchased, or NULL if it is free.
   *
   * Must point to an external URL.
   */
  public ?Url $purchaseUrl;

  /**
   * The URL of the Composer repository that provides the package.
   */
  public ?string $repository;

  /**
   * The type of authorization, if any, used by the repository.
   */
  public ?string $authorization;

  /**
   * A URL where the license key can be validated.
   */
  public ?Url $keyValidationUrl;

  public function __construct(
    public string $name,
    ?string $screenshot = NULL,
    ?string $path = NULL,
    array|string $package = [],
    public ?string $description = NULL,
    array $links = [],
    array $purchase = [],
    public ?string $creator = NULL,
  ) {
    // A site template may not ship a screenshot; fall back to the Cucumber
    // logo so the installer always has something to show.
    $default = dirname(__DIR__) . '/logo.png';
    if (!isset($screenshot)) {
      $screenshot = $default;
    }
    elseif (!file_exists($screenshot)) {
      $screenshot = UrlHelper::isValid($screenshot) && UrlHelper::isExternal($screenshot)
        ? $screenshot
        : $default;
    }
    $this->screenshot = $screenshot;

    if ($path) {
      assert(is_dir($path));
      $this->locator = $path;
      $this->repository = $this->authorization = NULL;
    }
    else {
      $this->locator = $package['name'] ?? $package;

      $repository = $package['repository'] ?? NULL;
      if ($repository) {
        assert(UrlHelper::isValid($repository) && UrlHelper::isExternal($repository));
      }
      elseif (is_array($package)) {
        unset($package['authorization']);
      }
      $this->repository = $repository;
      $this->authorization = $package['authorization'] ?? NULL;
    }

    $to_link = function (array|string $link): Link {
      $link = Link::fromTextAndUrl(
        $link['text'] ?? new TranslatableMarkup('More Info'),
        Url::fromUri($link['url'] ?? $link),
      );
      assert($link->getUrl()->isExternal());
      return $link;
    };
    $this->links = array_map($to_link, $links);

    $this->price = $purchase['price'] ?? 0;
    if ($this->price > 0) {
      assert(array_key_exists('url', $purchase));
      $this->purchaseUrl = Url::fromUri($purchase['url']);
      assert($this->purchaseUrl->isExternal());

      $this->keyValidationUrl = isset($purchase['validation_url'])
        ? Url::fromUri($purchase['validation_url'])
        : NULL;
      assert($this->keyValidationUrl?->isExternal() ?? TRUE);
    }
    else {
      $this->purchaseUrl = $this->keyValidationUrl = NULL;
    }
  }

  /**
   * Returns the screenshot, suitable for use in an <img src> attribute.
   *
   * @return string
   *   The screenshot as a base64-encoded data URI if it is a local file, or
   *   the original URL if it is an external link.
   */
  public function getScreenshot(): string {
    if (file_exists($this->screenshot)) {
      $extension = strtolower(pathinfo($this->screenshot, PATHINFO_EXTENSION));
      $type = match ($extension) {
        'png' => 'image/png',
        'jpg', 'jpeg' => 'image/jpeg',
        'svg' => 'image/svg+xml',
        default => 'image/webp',
      };
      return "data:$type;base64," . base64_encode(file_get_contents($this->screenshot));
    }
    return $this->screenshot;
  }

  /**
   * Constructs an instance of this class from a recipe.
   *
   * @param \Drupal\Core\Recipe\Recipe $recipe
   *   The recipe.
   */
  public static function createFromRecipe(Recipe $recipe): self {
    $extra = $recipe->getExtra('drupal_cms_installer');

    return new self(
      $recipe->name,
      $recipe->path . DIRECTORY_SEPARATOR . 'screenshot.webp',
      $recipe->path,
      [],
      $recipe->description,
      $extra['links'] ?? [],
      [],
      $extra['creator'] ?? NULL,
    );
  }

}
