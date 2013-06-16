<?php

/**
 * @file
 * Handle a Mailin Normal attribute.
 */

namespace Mailin\Attribute;

/**
 * The Mailin Attribute Normal class.
 */
class Normal extends Attribute {

  /**
   * The type of data handled by this attribute.
   *
   * @var string
   */
  protected $dataType = NULL;

  /**
   * @inheritdoc
   */
  public function __construct(array $attribute = array(), $value = NULL) {
    parent::__construct($attribute, $value);

    if (isset($attribute['type'])) {
      $this->setDataType($attribute['type']);
    }
  }

  /**
   * @inheritdoc
   */
  public function __toString() {
    return $this->isValid() ? $this->name . ', ' . $this->dataType : '';
  }

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
    );
  }

  /**
   * Get the data type.
   *
   * @return string
   */
  public function getDataType() {
    return $this->dataType;
  }

  /**
   * Set the data type.
   *
   * @param $dataType
   *
   * @return AttributeInterface
   */
  public function setDataType($dataType) {
    $dataType = strtoupper($dataType);

    if (in_array($dataType, $this->getAllowedDataTypes())) {
      $this->dataType = $dataType;
    }

    return $this;
  }

  /**
   * @inheritdoc
   */
  public function isValid() {
    return !empty($this->name) && isset($this->dataType);
  }

  /**
   * @inheritdoc
   */
  public static function attributeEntityMap() {
    return array(
      'list' => self::ATTRIBUTE_LIST_NORMAL,
      'user' => self::ATTRIBUTE_USER_NORMAL,
    );
  }

}
