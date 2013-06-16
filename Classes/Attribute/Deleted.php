<?php

/**
 * @file
 * Handle a Mailin Deleted attribute.
 */

namespace Mailin\Attribute;

/**
 * The Mailin Attribute Deleted class.
 */
class Deleted extends Attribute {

  /**
   * @inheritdoc
   */
  public function __toString() {
    return '';
  }

  /**
   * @inheritdoc
   */
  public function isValid() {
    return FALSE;
  }

  /**
   * @inheritdoc
   */
  public static function attributeEntityMap() {
    return array(
      'user' => self::ATTRIBUTE_USER_DELETED,
    );
  }

}
