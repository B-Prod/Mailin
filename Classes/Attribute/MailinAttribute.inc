<?php

/**
 * @file
 * Base class to handle a Mailin attribute.
 */

namespace Mailin\Attribute;

use Mailin\Attribute\MailinAttributeInterface;

/**
 * The Mailin Attribute class.
 */
abstract class MailinAttribute implements MailinAttributeInterface {

  /**
   * The attribute name.
   *
   * @var string
   */
  protected $name = NULL;

  /**
   * @inheritdoc
   */
  public function __construct(array $attribute) {
    if (isset($attribute['name']) && is_string($attribute['name']) && preg_match('/^[a-z_-]+$/i', $attribute['name'])) {
      $this->name = strtoupper($attribute['name']);
    }
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

}
