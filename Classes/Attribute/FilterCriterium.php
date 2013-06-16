<?php

/**
 * @file
 * Defines an attribute filter criterium.
 */

namespace Mailin\Attribute;

/**
 * The Mailin Attributes filter class.
 */
class FilterCriterium {

  /**
   * The filter criterium.
   *
   * @var mixed
   */
  protected $criterium;

  /**
   * The filter criterium arguments.
   *
   * @var array
   */
  protected $arguments;

  /**
   * Whether the matching condition is inversed or not.
   *
   * @var boolean
   */
  protected $negate;

  /**
   * Set a filter.
   *
   * If a filter on the given property does not exist, it is created, otherwise
   * it is updated.
   *
   * @param $criterium
   *   May be either a single value or a list of values.
   * @param $arguments
   *   The arguments for the getter function.
   * @param $negate
   *   If TRUE, the filter matches when the given property has not the specified value.
   *
   * @return FilterCriterium
   */
  public function __construct($criterium, array $arguments = array(), $negate = FALSE) {
    $this->criterium = $criterium;
    $this->arguments = $arguments;
    $this->negate = $negate;
  }

  /**
   * Get the callback arguments.
   *
   * @return array
   */
  public function getArguments() {
    return $this->arguments;
  }

  /**
   * Whether the given value matches the criterium or not.
   *
   * @param $value
   *   The value to test.
   *
   * @return boolean
   */
  public function match($value) {
    $match = is_array($this->criterium) ? in_array($value, $this->criterium) : $value == $this->criterium;
    return !($match xor !$this->negate);
  }

}
