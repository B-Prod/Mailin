<?php

/**
 * @file
 * This class helps to find attributes using search criteria.
 */

namespace Mailin\Attribute;

/**
 * The Mailin Attributes filter class.
 */
class MailinAttributesFilter extends \FilterIterator {

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
   */
  public function __construct(array $attributes, array $filters = array()) {
    $this->setAttributes($attributes);
    $this->setFilters($filters);
    parent::__construct($this->attributes);
  }

  /**
   * Set the attributes.
   *
   * @return MailinAttributesFilter
   *
   * @see MailinAttributesFilter::__construct()
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
   * @return MailinAttributesFilter
   *
   * @see MailinAttributesFilter::__construct()
   */
  public function setFilters(array $filters) {
    $this->filters = $filters;
    return $this;
  }

  /**
   * Set a filter.
   *
   * If a filter on the given property does not exist, it is created, otherwise
   * it is updated.
   *
   * @return MailinAttributesFilter
   */
  public function setFilter($property, $value) {
    $this->filters[$property] = $value;
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

    if (is_object($current) && $current instanceof MailinAttribute) {
      $match = TRUE;

      foreach ($this->filters as $property => $value) {
        $getter = 'get' . ucfirst($property);

        if (!is_callable(array($current, $getter)) || $current->{$getter}() != $value) {
          $match = FALSE;
          break;
        }
      }//end foreach
    }

    return $match;
  }

}
