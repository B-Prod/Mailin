<?php

/**
 * @file
 * This class helps to find attributes using search criteria.
 */

namespace Mailin\Attribute;

/**
 * The Mailin Attributes filter class.
 *
 * This class helps to iterate through a set of attributes, whose structure
 * is the one defined in the DISPLAY-ATTRIBUTES documentation.
 *
 * Specifying no filter allows to iterate on the whole list of attributes.
 *
 * The filter declaration may be very simple or more advanced.
 *
 * Simple example of filters:
 * @code
 * $filters = array(
 *   'type' => Mailin\API::ATTRIBUTE_NORMAL,
 *   'value' => 'NOM',
 * );
 * @endcode
 *
 * With such criteria, you may use the getOne() method since there could not
 * be 2 attributes that match those.
 *
 * You can also use multiple values and negation in filters:
 * @code
 * $filters = array(
 *   'type' => Mailin\API::ATTRIBUTE_NORMAL,
 *   'name' => array(
 *     'value' => array('NOM', 'PRENOM', 'TELEPHONE'),
 *     'negate' => TRUE,
 *   ),
 * );
 * @endcode
 *
 * It is also possible to directly use a built-in criterium:
 * @code
 * $filters = array(
 *   'name' => new Mailin\Attribute\FilterCriterium('NOM', TRUE),
 * );
 * @endcode
 */
class Filter extends \FilterIterator {

  /**
   * The filter criteria.
   *
   * @var array
   */
  protected $filters;

  /**
   * The attributes to search in.
   *
   * @var RecursiveArrayIterator
   */
  protected $attributes;

  /**
   * Class constructor.
   *
   * @param $attributes
   *   An array of attributes, keyed by attribute type.
   * @param $filters
   *   An array whose keys are attribute properties and values the
   *   expected value for this property.
   *   Some more complex conditions are possible, using an array instead
   *   of a single value.
   *   You may also use an instance of the Mailin\Attribute\FilterCriterium
   *   class as value.
   */
  public function __construct(array $attributes, array $filters = array()) {
    $this->setAttributes($attributes);
    $this->setFilters($filters);
    parent::__construct($this->attributes);
  }

  /**
   * Set the attributes.
   *
   * @param $attributes
   *
   * @return Mailin\Attribute\Filter
   */
  public function setAttributes(array $attributes) {
    $array = array();

    while ($items = array_shift($attributes)) {
      $array = array_merge($array, $items);
    }//end while

    $this->attributes = new \ArrayIterator($array);
    return $this;
  }

  /**
   * Set the filters.
   *
   * @param $filters
   *
   * @return Mailin\Attribute\Filter
   */
  public function setFilters(array $filters) {
    $this->filters = array();

    foreach ($filters as $property => $value) {
      $negate = FALSE;

      if (is_array($value) && array_key_exists('value', $value)) {
        if (isset($value['negate'])) {
          $negate = $value['negate'];
          unset($value['negate']);
        }

        $value = $value['value'];
      }

      $this->setFilter($property, $value, $negate);
    }//end foreach

    return $this;
  }

  /**
   * Set a filter.
   *
   * If a filter on the given property does not exist, it is created, otherwise
   * it is updated.
   *
   * @param $property
   *   The property to filter on.
   * @param $value
   * @param $negate
   *
   * @return Mailin\Attribute\Filter
   *
   * @see Mailin\Attribute\FilterCriteria::__construct()
   */
  public function setFilter($property, $value, $negate = FALSE) {
    $this->filters[$property] = new FilterCriterium($value, $negate);
    return $this;
  }

  /**
   * Get the first result.
   */
  public function getOne() {
    $this->rewind();
    return $this->current();
  }

  /**
   * @inheritdoc
   *
   * @see FilterIterator
   */
  public function accept() {
    if (empty($this->filters)) {
      return TRUE;
    }

    $match = FALSE;
    $current = $this->getInnerIterator()->current();

    if (is_object($current) && $current instanceof Attribute) {
      $match = TRUE;

      foreach ($this->filters as $property => $criterium) {
        $getter = 'get' . ucfirst($property);

        if (!is_callable(array($current, $getter)) || !$criterium->match($current->{$getter}())) {
          $match = FALSE;
          break;
        }
      }//end foreach
    }

    return $match;
  }

}
