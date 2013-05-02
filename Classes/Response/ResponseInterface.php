<?php

/**
 * @file
 * Define an interface for handling Mailin server responses.
 */

namespace Mailin\Response;

/**
 * The Mailin Response interface.
 */
interface ResponseInterface {

  /**
   * Class constructor.
   *
   * @param $data
   *   The raw response data
   */
  public function __construct($data);

  /**
   * Whether the request was succesfull or not.
   *
   * @return boolean
   */
  public function isSuccessful();

  /**
   * Get the response data or FALSE if something went wrong.
   *
   * Note that this method assumes that the expected data reside in the "result"
   * key.
   */
  public function getResult();

  /**
   * Get the response data.
   */
  public function getData();

  /**
   * Get the response data only if the response is successful.
   *
   * @return array
   *   The data or en empty array on failure.
   */
  public function getDataOnSuccess();

  /**
   * Get the error message if any.
   */
  public function getErrorMessage();

}
