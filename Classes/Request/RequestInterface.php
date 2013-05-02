<?php

/**
 * @file
 * Define an interface for handling requests against Mailin server.
 */

namespace Mailin\Request;

/**
 * The Mailin Request interface.
 */
interface RequestInterface {

  /**
   * Perform a request against Mailin server.
   *
   * @param $query
   *   An array containing the request query.
   *
   * @return string
   *   The Mailin server response.
   */
  public function request(array $query);

}
