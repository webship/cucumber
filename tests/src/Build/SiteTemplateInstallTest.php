<?php

declare(strict_types=1);

namespace Drupal\Tests\cucumber\Build;

use Drupal\BuildTests\QuickStart\QuickStartTestBase;
use Drupal\Component\Utility\Html;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\RequiresPhpExtension;

/**
 * Contains an end-to-end test of installing Cucumber with a site template.
 */
#[Group('cucumber')]
#[RequiresPhpExtension('pdo_sqlite')]
final class SiteTemplateInstallTest extends QuickStartTestBase {

  /**
   * Tests that the installer applies the chosen site template.
   *
   * The site template used here is a local recipe, so Composer is never
   * invoked; that path is covered by the installer's own integration with
   * Composer, which cannot be exercised without network access.
   */
  public function testInstallWithLocalSiteTemplate(): void {
    $this->copyCodebase();
    $directory = $this->getWorkspaceDirectory();

    // Provide a site template that is physically present in the code base, so
    // that the installer offers it without having to require it.
    $recipe = $this->getWorkingPath('recipes/test_site_template');
    file_put_contents("$recipe/recipe.yml", <<<'YAML'
    name: 'Test Site Template'
    description: 'A site template used by the Cucumber installer test.'
    type: 'Site'
    install:
      - node
    YAML);

    // Limit the curated list, so the test never reaches out to the network.
    $site_directory = $this->getWorkingPath('web/sites/default');
    file_put_contents("$site_directory/site-templates.php", '<?php return [];');

    $mink = $this->visit(working_dir: 'web');
    $page = $mink->getSession()->getPage();
    $assert_session = $mink->assertSession();

    // The database step, then the two steps this issue adds.
    $page->selectFieldOption('driver', 'SQLite');
    $page->pressButton('Save and continue');
    $assert_session->pageTextContains('Give your site a name');
    $page->fillField('Site name', 'Cucumber Test');
    $page->pressButton('Next');
    $assert_session->pageTextContains('Choose a site template');
    $assert_session->pageTextContains('Test Site Template');
    $page->selectFieldOption('add_ons', 'test_site_template');
    $page->pressButton('Next');

    // Finish the installation.
    $assert_session->pageTextContains('Create your account');
    $page->fillField('Email', 'test@example.com');
    $page->fillField('Password', 'test');
    $page->pressButton('Finish');

    $refreshed = 0;
    do {
      if ($refreshed === 100) {
        $this->fail('The installation is taking too long and is probably stuck in an infinite loop.');
      }
      $refresh = $page->find('css', 'meta[http-equiv="refresh"], meta[http-equiv="Refresh"]')
        ?->getAttribute('content');

      if ($refresh && preg_match('/\d+;\s*URL=\'?(?<url>[^\']*)/i', $refresh, $match)) {
        $url = Html::decodeEntities($match['url']);
        $this->visit($url, 'web');
        $refreshed++;
      }
    } while (isset($refresh));

    // The site template was applied, and the profile uninstalled itself.
    $this->assertFileExists("$directory/recipes/test_site_template/recipe.yml");
    $assert_session->statusCodeEquals(200);
  }

}
