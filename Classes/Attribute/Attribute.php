<?php

/**
 * @file
 * Base class to handle a Mailin attribute.
 */

namespace Mailin\Attribute;

/**
 * The Mailin Attribute class.
 */
abstract class Attribute implements AttributeInterface {

  /**
   * The attribute name.
   *
   * @var string
   */
  protected $name = NULL;

  /**
   * The attribute value.
   *
   * @var mixed
   */
  protected $value;

  /**
   * @inheritdoc
   */
  public function __construct(array $attribute = array(), $value = NULL) {
    if (isset($attribute['name']) && is_string($attribute['name']) && preg_match('/^[a-z_-]+$/i', $attribute['name'])) {
      $this->name = strtoupper($attribute['name']);
    }

    $this->value = $value;
  }

  /**
   * @inheritdoc
   */
  public function getName() {
    return $this->name;
  }

  /**
   * @inheritdoc
   */
  public function setName($name) {
    $this->name = $name;
    return $this;
  }

  /**
   * @inheritdoc
   */
  public function getValue() {
    return $this->value;
  }

  /**
   * @inheritdoc
   */
  public function setValue($value) {
    $this->value = $value;
    return $this;
  }

  /**
   * @inheritdoc
   */
  public function getType($context = 'list') {
    $map = call_user_func(array(get_called_class(), 'attributeEntityMap'));
    return isset($map[$context]) ? $map[$context] : NULL;
  }

  /**
   * @inheritdoc
   */
  final public static function supportedEntityTypes() {
    // The Attribute class is a base class that should be extended. So it
    // has no sense to call this method using this class.
    if (get_called_class() !== get_class()) {
      return array_keys(self::attributeEntityMap());
    }

    return array();
  }

  /**
   * @inheritdoc
   */
  final public static function getAllTypes($context = 'list') {
    static $types;

    if (!isset($types)) {
      $types = array();
      $classes = array('Normal', 'Category', 'Transactional', 'Calculated', 'Computation', 'Deleted');

      foreach ($classes as $class) {
        $class = __NAMESPACE__ . '\\' . $class;

        if (is_callable(array($class, 'attributeEntityMap'))) {
          foreach (call_user_func(array($class, 'attributeEntityMap')) as $entityType => $attributeLabel) {
            $types[$entityType][$class] = $attributeLabel;
          }//end foreach
        }
      }//end foreach
    }

    return isset($types[$context]) ? $types[$context] : array();
  }

}
