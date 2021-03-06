<?php

/**
 * @file
 * Handle a Mailin Computation attribute.
 */

namespace Mailin\Attribute;

/**
 * The Mailin Attribute Computation class.
 */
class Computation extends Attribute {

  /**
   * The computed formula.
   *
   * @var string
   */
  protected $formula = NULL;

  /**
   * @inheritdoc
   */
  public function __construct(array $attribute = array(), $value = NULL) {
    parent::__construct($attribute, $value);

    if (isset($attribute['value'])) {
      $this->setFormula($attribute['value']);
    }
  }

  /**
   * @inheritdoc
   */
  public function __toString() {
    return $this->isValid() ? $this->name . ', ' . $this->formula : '';
  }

  /**
   * Get the formula.
   *
   * @return string
   */
  public function getFormula() {
    return $this->formula;
  }

  /**
   * Set the formula.
   *
   * @param $formula
   *
   * @return AttributeInterface
   */
  public function setFormula($formula) {
    $this->formula = $formula;
    return $this;
  }

  /**
   * @inheritdoc
   *
   * @todo perform more advanced checks.
   * @see http://ressources.mailin.fr/formules/?lang=en
   */
  public function isValid() {
    return !empty($this->name) && isset($this->formula);
  }

  /**
   * @inheritdoc
   */
  public static function attributeEntityMap() {
    return array(
      'list' => self::ATTRIBUTE_LIST_COMPUTATION,
    );
  }

}
