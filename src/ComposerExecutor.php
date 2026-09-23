<?php

declare(strict_types=1);

namespace Drupal\cucumber;

use Composer\InstalledVersions;
use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Process\Process;

/**
 * Runs Composer.
 *
 * @internal
 *   Everything in the Cucumber installer is internal and may be changed or
 *   removed at any time without warning. External code should not interact
 *   with this class.
 */
final class ComposerExecutor {

  /**
   * Executes a Composer command.
   *
   * @param string ...$arguments
   *   Arguments to pass to Composer. The path of the Composer binary, and the
   *   `--no-interaction` option, are automatically prepended.
   */
  public static function execute(string ...$arguments): void {
    $command = [
      (new PhpExecutableFinder())->find(),
      InstalledVersions::getInstallPath('composer/composer') . '/bin/composer',
      '--no-interaction',
      ...$arguments,
    ];
    ['install_path' => $project_root] = InstalledVersions::getRootPackage();
    // Composer can take a while, so give it a nice, generous timeout.
    (new Process($command, $project_root, timeout: 300))->mustRun();
  }

}
