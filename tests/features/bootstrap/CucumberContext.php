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
   *
   * @var array
   */
  protected $parameters = [];

  /**
   * Initializes context.
   *
   * @param array $parameters
   *   The parameters.
   */
  public function __construct(array $parameters) {

    // Set the list of parameters.
    $this->parameters = $parameters;

  }

  /**
   * Clean users.
   */
  public function cleanUsers() {

  }

}
