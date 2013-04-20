<?php

/**
 * @file
 * Support for Mailin Web services response.
 */

namespace Mailin\Response;

use Mailin\Response\MailinResponseInterface;

/**
 * The Mailin Response class.
 */
class MailinResponse implements MailinResponseInterface, \ArrayAccess {

  /**
   * The response parsed data.
   *
   * @var array
   */
  protected $data = array();

  /**
   * Flag indicating whether the request was successful or not.
   *
   * @var boolean
   */
  protected $success = FALSE;

  /**
   * @inheritdoc
   */
  public function __construct($data) {
    if (FALSE === ($this->data = json_decode($data, TRUE))) {
      // Ensure the data property is always an array.
      $this->data = array();
    }
    elseif (empty($this['errorMsg'])) {
      $this->success = TRUE;
    }
  }

  /**
   * @inheritdoc
   */
  public function isSuccessful() {
    return $this->success;
  }

  /**
   * @inheritdoc
   */
  public function getResult($expected_key = 'result') {
    return ($this->success && isset($this[$expected_key])) ? $this[$expected_key] : FALSE;
  }

  /**
   * @inheritdoc
   */
  public function getData() {
    return $this->data;
  }

  /**
   * @inheritdoc
   */
  public function getDataOnSuccess() {
    return $this->success ? $this->data : array();
  }

  /**
   * @inheritdoc
   */
  public function getErrorMessage() {
    return isset($this['errorMsg']) ? $this['errorMsg'] : ($this->success ? '' : 'Unknown error');
  }

  /**
   * @inheritdoc
   */
  public function offsetExists($offset) {
    return isset($this->data[$offset]);
  }

  /**
   * @inheritdoc
   */
  public function offsetGet($offset) {
    return isset($this->data[$offset]) ? $this->data[$offset] : NULL;
  }

  /**
   * @inheritdoc
   */
  public function offsetSet($offset, $value) {
    if (is_null($offset)) {
      $this->data[] = $value;
    } else {
      $this->data[$offset] = $value;
    }
  }

  /**
   * @inheritdoc
   */
  public function offsetUnset($offset) {
    unset($this->data[$offset]);
  }

}
