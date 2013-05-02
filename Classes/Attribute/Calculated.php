<?php

/**
 * @file
 * Handle a Mailin Calculated attribute.
 */

namespace Mailin\Attribute;

use Mailin\API as Mailin;

/**
 * The Mailin Attribute Calculated class.
 */
class Calculated extends Computation {

  /**
   * @inheritdoc
   */
  public function getType() {
    return Mailin::ATTRIBUTE_CALCULATED;
  }

}
