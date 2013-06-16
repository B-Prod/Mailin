<?php

/**
 * @file
 * Define an interface for handling Mailin attributes.
 */

namespace Mailin\Attribute;

/**
 * The Mailin Attribute Interface.
 */
interface AttributeInterface {

  /**
   * The "normal" attribute type.
   */
  const ATTRIBUTE_LIST_NORMAL = 'normal_attributes';

  /**
   * The "category" attribute type.
   */
  const ATTRIBUTE_LIST_CATEGORY = 'category_attributes';

  /**
   * The "transactionel" attribute type.
   */
  const ATTRIBUTE_LIST_TRANSACTIONAL = 'transactional_attributes';

  /**
   * The "calculated value" attribute type.
   */
  const ATTRIBUTE_LIST_CALCULATED = 'calculated_value';

  /**
   * The "global computation" attribute type.
   */
  const ATTRIBUTE_LIST_COMPUTATION = 'global_computation_value';

  /**
   * The "normal" attribute type.
   */
  const ATTRIBUTE_USER_NORMAL = 'normal_attribute';

  /**
   * The "category" attribute type.
   */
  const ATTRIBUTE_USER_CATEGORY = 'category_attributes';

  /**
   * The "transactionel" attribute type.
   */
  const ATTRIBUTE_USER_TRANSACTIONAL = 'transactional_attributes';

  /**
   * The "calculated value" attribute type.
   */
  const ATTRIBUTE_USER_CALCULATED = 'calculated_values';

  /**
   * The "deleted" attribute type.
   */
  const ATTRIBUTE_USER_DELETED = 'deleted_attributes';

  /**
   * The attribute data type that stores strings.
   */
  const ATTRIBUTE_DATA_TYPE_TEXT = 'TEXT';

  /**
   * The attribute data type that stores numbers.
   */
  const ATTRIBUTE_DATA_TYPE_NUMBER = 'NUMBER';

  /**
   * The attribute data type that stores dates.
   */
  const ATTRIBUTE_DATA_TYPE_DATE = 'DATE';

  /**
   * The attribute data type that stores IDs.
   */
  const ATTRIBUTE_DATA_TYPE_ID = 'ID';

  /**
   * Class constructor.
   *
   * @param $attribute
   *   The attribute definition.
   * @param $value
   *   The attribute value, if any.
   */
  public function __construct(array $attribute = array(), $value = NULL);

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
   * @return AttributeInterface
   */
  public function setName($name);

  /**
   * Get the attribute value.
   *
   * @return mixed
   */
  public function getValue();

  /**
   * Set the attribute value.
   *
   * @return AttributeInterface
   */
  public function setValue($value);

  /**
   * Get the attribute type.
   *
   * @return string
   */
  public function getType($context = 'list');

  /**
   * Whether the attribute is valid or not.
   *
   * @return boolean
   */
  public function isValid();

  /**
   * Get the entity types this attribute may be affected to.
   *
   * @return array
   */
  public static function supportedEntityTypes();

  /**
   * Give the correspondence between supporeted entity types and the
   * attribute type label that is used when requesting the Mailin Services.
   *
   * @return array
   *   An array whose keys are entity types and whose values are attribute type
   *   labels.
   */
  public static function attributeEntityMap();

  /**
   * Get a list of attribute types for a given context.
   *
   * @param $type
   *   The attribute type to return. It may be one of:
   *   - list: The types of attributes related to list context.
   *   - user: The types of attributes related to user context.
   *
   * @return array
   */
  public static function getAllTypes($context = 'list');

}
