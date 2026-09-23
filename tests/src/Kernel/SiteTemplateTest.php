<?php

declare(strict_types=1);

namespace Drupal\Tests\cucumber\Kernel;

use Drupal\Component\Assertion\Inspector;
use Drupal\Core\Link;
use Drupal\KernelTests\KernelTestBase;
use Drupal\cucumber\SiteTemplate;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;

/**
 * Tests the site template value object.
 */
#[Group('cucumber')]
#[CoversClass(SiteTemplate::class)]
#[RunTestsInSeparateProcesses]
final class SiteTemplateTest extends KernelTestBase {

  /**
   * Tests building a site template from a curated list entry.
   */
  public function testCreateFromValues(): void {
    $values = [
      'name' => 'Acceptance Testing Suite',
      'screenshot' => 'https://www.example.com/screenshot',
      'package' => [
        'name' => 'example/acceptance-testing-suite',
        'repository' => 'https://packages.example.com',
        'authorization' => 'bearer',
      ],
      'description' => $this->getRandomGenerator()->sentences(3),
      'links' => [
        'https://www.example.com/demo',
        ['text' => 'Documentation', 'url' => 'https://www.example.com/docs'],
      ],
      'purchase' => [
        'price' => 899,
        'url' => 'https://www.example.com/buy',
        'validation_url' => 'https://www.example.com/validate',
      ],
      'creator' => 'Webship',
    ];

    $template = new SiteTemplate(...$values);
    $this->assertSame($values['name'], $template->name);
    $this->assertSame($values['package']['name'], $template->locator);
    $this->assertSame($values['description'], $template->description);
    $this->assertSame($values['screenshot'], $template->getScreenshot());
    $this->assertSame($values['creator'], $template->creator);
    $this->assertTrue(
      Inspector::assertAllObjects($template->links, Link::class),
    );
    $this->assertCount(2, $template->links);
    $this->assertSame('More Info', (string) $template->links[0]->getText());
    $this->assertSame($values['links'][1]['text'], (string) $template->links[1]->getText());
    $this->assertSame((float) $values['purchase']['price'], $template->price);
    $this->assertSame($values['purchase']['url'], $template->purchaseUrl?->toString());
    $this->assertSame($values['purchase']['validation_url'], $template->keyValidationUrl?->toString());
    $this->assertSame($values['package']['repository'], $template->repository);
    $this->assertSame($values['package']['authorization'], $template->authorization);
  }

  /**
   * Tests that a free site template falls back to the Cucumber logo.
   */
  public function testFreeTemplateWithoutScreenshot(): void {
    $template = new SiteTemplate(
      name: 'Cucumber Starter',
      package: 'drupal/cucumber_starter',
      description: 'The Cucumber site template.',
    );
    $this->assertSame('drupal/cucumber_starter', $template->locator);
    $this->assertStringStartsWith('data:image/png;base64,', $template->getScreenshot());
    $this->assertSame([], $template->links);
    $this->assertSame(0.0, $template->price);
    $this->assertNull($template->purchaseUrl);
    $this->assertNull($template->keyValidationUrl);
    $this->assertNull($template->repository);
    $this->assertNull($template->authorization);
  }

}
