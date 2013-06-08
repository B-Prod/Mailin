<?php

/**
 * @file
 * Handle a Mailin Transactional attribute.
 */

namespace Mailin\Attribute;

/**
 * The Mailin Attribute Transactional class.
 */
class Transactional extends Normal {

  /**
   * Get the allowed data types.
   *
   * @return array
   */
  public function getAllowedDataTypes() {
    return array(
      self::ATTRIBUTE_DATA_TYPE_TEXT,
      self::ATTRIBUTE_DATA_TYPE_NUMBER,
      self::ATTRIBUTE_DATA_TYPE_DATE,
      self::ATTRIBUTE_DATA_TYPE_ID,
    );
  }

  /**
   * @inheritdoc
   */
  public static function attributeEntityMap() {
    return array(
      'list' => self::ATTRIBUTE_LIST_TRANSACTIONAL,
      'user' => self::ATTRIBUTE_USER_TRANSACTIONAL,
    );
  }

}
