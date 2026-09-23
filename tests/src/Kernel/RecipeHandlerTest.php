<?php

declare(strict_types=1);

namespace Drupal\Tests\cucumber\Kernel;

use Drupal\KernelTests\KernelTestBase;
use Drupal\cucumber\RecipeHandler;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;

/**
 * Tests the queue of recipes the installer applies.
 */
#[Group('cucumber')]
#[CoversClass(RecipeHandler::class)]
final class RecipeHandlerTest extends KernelTestBase {

  /**
   * Tests queueing recipes and marking them as applied.
   */
  public function testEnqueueAndMarkAsApplied(): void {
    $handler = new RecipeHandler(
      $this->container->get('state'),
      $this->container->get('messenger'),
    );
    $this->assertSame([], $handler->list());

    $handler->enqueue('core/recipes/administrator_role', 'drupal/cucumber_starter');
    // Queueing the same recipe twice does not duplicate it.
    $handler->enqueue('drupal/cucumber_starter');
    $this->assertSame(
      ['core/recipes/administrator_role', 'drupal/cucumber_starter'],
      $handler->list(),
    );

    $handler->markAsApplied('core/recipes/administrator_role');
    $this->assertSame(['drupal/cucumber_starter'], $handler->list());

    // Once everything is applied, the state entry is deleted.
    $handler->markAsApplied('drupal/cucumber_starter');
    $this->assertSame([], $handler->list());
    $this->assertNull($this->container->get('state')->get(RecipeHandler::STATE_KEY));
  }

}
