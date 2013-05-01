<?php

/**
 * @file
 * Handle a Mailin Transactional attribute.
 */

namespace Mailin\Attribute;

use Mailin\Attribute\MailinAttributeNormal;
use Mailin\MailinAPI;

/**
 * The Mailin Attribute Transactional class.
 */
class MailinAttributeTransactional extends MailinAttributeNormal {

  /**
   * Get the allowed data types.
   *
   * @return array
   */
  public function getAllowedDataTypes() {
    return array(
      MailinAPI::ATTRIBUTE_DATA_TYPE_TEXT,
      MailinAPI::ATTRIBUTE_DATA_TYPE_NUMBER,
      MailinAPI::ATTRIBUTE_DATA_TYPE_DATE,
      MailinAPI::ATTRIBUTE_DATA_TYPE_ID,
    );
  }

  /**
   * @inheritdoc
   */
  public function getType() {
    return MailinAPI::ATTRIBUTE_TRANSACTIONAL;
  }

}
