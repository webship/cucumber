<?php

/**
 * @file
 * Contains \CucumberContext.
 */

use Drupal\DrupalExtension\Context\RawDrupalContext;
use Behat\Behat\Context\SnippetAcceptingContext;

/**
 * Defines application features from the specific context.
 */
class CucumberContext extends RawDrupalContext implements SnippetAcceptingContext {

  /**
  * Hold all passed parameters.
  */
  protected $parameters = array();

  /**
   * Initializes context.
   */
  public function __construct(array $parameters) {

    // Set the list of parameters.
    $this->parameters = $parameters;

  }

  public function cleanUsers() {

  }

  /**
   * Maximize the window before scenario.
   *
   * @BeforeScenario @javascript
   */
  public function maximizeWindow() {
    $this->getSession()->getDriver()->maximizeWindow();
  }
}
