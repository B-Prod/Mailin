<?php

/**
 * @file
 * Handle a Mailin Calculated attribute.
 */

namespace Mailin\Attribute;

/**
 * The Mailin Attribute Calculated class.
 */
class Calculated extends Computation {

  /**
   * @inheritdoc
   */
  public static function attributeEntityMap() {
    return array(
      'list' => self::ATTRIBUTE_LIST_CALCULATED,
      'user' => self::ATTRIBUTE_USER_CALCULATED,
    );
  }

}
