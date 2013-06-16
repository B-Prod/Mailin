<?php

/**
 * @file
 * Handle a Mailin Category attribute.
 */

namespace Mailin\Attribute;

/**
 * The Mailin Attribute Category class.
 */
class Category extends Attribute {

  /**
   * The category items.
   *
   * @var array
   */
  protected $enumeration = array();

  /**
   * @inheritdoc
   */
  public function __construct(array $attribute = array(), $value = NULL) {
    parent::__construct($attribute, $value);

    if (isset($attribute['enumeration'])) {
      $this->setEnumeration($attribute['enumeration']);
    }
  }

  /**
   * @inheritdoc
   */
  public function __toString() {
    if ($this->isValid()) {
      $args = $this->getEnumeration(TRUE);
      array_unshift($args, $this->name);
      return implode(', ', $args);
    }

    return '';
  }

  /**
   * Get the category items.
   *
   * @param $labelOnly
   *   Whether to return only the labels or the all item.
   *
   * @return array
   */
  public function getEnumeration($labelOnly = FALSE) {
    return $labelOnly ? array_map(function($v) { return $v['label']; }, $this->enumeration) : $this->enumeration;
  }

  /**
   * Set the category items.
   *
   * @param $items
   *   An array of item names.
   *
   * @return AttributeInterface
   */
  public function setEnumeration(array $items) {
    $this->enumeration = array();

    foreach ($items as $item) {
      if (isset($label)) {
        unset($label);
      }
      if (isset($value)) {
        unset($value);
      }

      if (is_array($item)) {
        extract($item, EXTR_SKIP);
      }
      else {
        $label = $item;
      }

      if (isset($label)) {
        $this->addEnumerationItem($label, isset($value) ? $value : NULL);
      }
    }//end foreach

    return $this;
  }

  /**
   * Add an item to the current enumeration.
   *
   * @param $label
   *   The item label.
   * @param $value
   *   The item value.
   *
   * @return AttributeInterface
   */
  public function addEnumerationItem($label, $value = NULL) {
    if (is_string($label) && strlen($label) && (is_null($value) || is_numeric($value))) {
      $this->enumeration[] = array(
        'label' => $label,
        'value' => $value,
      );
    }

    return $this;
  }

  /**
   * @inheritdoc
   */
  public function isValid() {
    return !empty($this->name) && !empty($this->enumeration);
  }

  /**
   * @inheritdoc
   */
  public static function attributeEntityMap() {
    return array(
      'list' => self::ATTRIBUTE_LIST_CATEGORY,
      'user' => self::ATTRIBUTE_USER_CATEGORY,
    );
  }

}
