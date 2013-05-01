<?php

/**
 * @file
 * Handle a Mailin Calculated attribute.
 */

namespace Mailin\Attribute;

use Mailin\Attribute\MailinAttributeComputation;
use Mailin\MailinAPI;

/**
 * The Mailin Attribute Calculated class.
 */
class MailinAttributeCalculated extends MailinAttributeComputation {

  /**
   * @inheritdoc
   */
  public function getType() {
    return MailinAPI::ATTRIBUTE_CALCULATED;
  }

}
