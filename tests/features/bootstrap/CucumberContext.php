<?php

use Behat\Behat\Tester\Exception\PendingException;
use Drupal\DrupalExtension\Context\RawDrupalContext;
use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;

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
   *
   * @param array $parameters .
   *   Context parameters (set them up through behat.yml or behat.local.yml).
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
