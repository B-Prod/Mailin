<?php

/**
 * @file
 * Define an interface for handling Mailin attributes.
 */

namespace Mailin\Attribute;

/**
 * The Mailin Attribute Interface.
 */
interface MailinAttributeInterface {

  /**
   * class constructor.
   */
  public function __construct(array $attribute);

  /**
   * Magic method for string cast.
   *
   * @return string
   *   The string representation of the Mailin attribute, in the format
   *   expected by the CREATE-ATTRIBUTES service.
   */
  public function __toString();

  /**
   * Get the attribute name.
   *
   * @return string
   */
  public function getName();

  /**
   * Set the attribute name.
   *
   * @return MailinAttributeInterface
   */
  public function setName($name);

  /**
   * Get the attribute type.
   *
   * @return string
   */
  public function getType();

  /**
   * Whether the attribute is valid or not.
   *
   * @return boolean
   */
  public function isValid();

}
