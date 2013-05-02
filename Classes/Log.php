<?php

/**
 * @file
 * Log information relative to Mailin API calls.
 */

namespace Mailin;

use Mailin\Response\ResponseInterface;

/**
 * The Mailin Log class.
 */
abstract class Log {

  /**
   * Store the API calls debug information.
   *
   * @var array
   */
  protected static $apiCalls = array();

  /**
   * Store references to some variables related to current operation, if any.
   *
   * @var array
   */
  protected static $references = array();

  /**
   * Start logging indormation about a new API call.
   *
   * @param $query
   *   The original query.
   */
  public static function startApiCall(array $query) {
    self::$apiCalls[] = array(
      'start' => microtime(TRUE),
      'query' => $query,
    );

    // Set the current API call reference.
    end(self::$apiCalls);
    $key = key(self::$apiCalls);
    self::$references['apiCall'] = & self::$apiCalls[$key];
  }

  /**
   * Store log indormation about a given API call.
   *
   * @param $response
   *   The API call response.
   */
  public static function endApiCall(ResponseInterface $response) {
    if (isset(self::$references['apiCall'])) {
      $current = & self::$references['apiCall'];
      $current += array(
        'end' => microtime(TRUE),
        'status' => $response->isSuccessful(),
        'error' => $response->getErrorMessage(),
      );

      // Calculate the API call duration in ms.
      $current['duration'] = round(($current['end'] - $current['start']) * 1000, 2);

      // Clear the current API call reference.
      unset(self::$references['apiCall']);
    }
  }

  /**
   * Get the API calls count.
   *
   * @return int
   */
  public static function countApiCalls() {
    return sizeof(self::$apiCalls);
  }

  /**
   * Get the error message related to the last operation.
   *
   * @return string
   */
  public static function getLastOperationError() {
    $operation = end(self::$apiCalls);

    if (isset($operation['error']) && strlen($operation['error'])) {
      return $operation['error'];
    }

    return '';
  }

}
