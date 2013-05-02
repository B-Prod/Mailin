<?php

/**
 * @file
 * Handle a Mailin Transactional attribute.
 */

namespace Mailin\Attribute;

use Mailin\API as Mailin;

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
      Mailin::ATTRIBUTE_DATA_TYPE_TEXT,
      Mailin::ATTRIBUTE_DATA_TYPE_NUMBER,
      Mailin::ATTRIBUTE_DATA_TYPE_DATE,
      Mailin::ATTRIBUTE_DATA_TYPE_ID,
    );
  }

  /**
   * @inheritdoc
   */
  public function getType() {
    return Mailin::ATTRIBUTE_TRANSACTIONAL;
  }

}
