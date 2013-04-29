<?php

/**
 * @file
 * Handle requests against Mailin server using CURL.
 */

namespace Mailin\Request;

use Mailin\Response\MailinResponse;

/**
 * The Mailin Request CURL class.
 *
 * This class is used by default by the Mailin API class, but such behavior
 * may be overriden if necessary. To do so, you have to implement your own
 * request class and tell Mailin API instance to use yours within the
 * "setRequesthandler" method.
 */
class MailinRequestCurl implements MailinRequestInterface {

  /**
   * The Mailin Web services URL.
   */
  const MAILIN_URL = 'http://ws.mailin.fr/';

  /**
   * @inheritdoc
   */
  public function request(array $query) {
    $ch = curl_init();
    $params = array();

    foreach ($query as $key => $value) {
      $params[] = rawurlencode($key) . '=' . str_replace('%2F', '/', rawurlencode($value));
    }//end foreach

    curl_setopt_array($ch, array(
      CURLOPT_URL => self::MAILIN_URL,
      CURLOPT_POST => 1,
      CURLOPT_POSTFIELDS => implode('&', $params),
      CURLOPT_HTTPHEADER => array('Expect:'),
      CURLOPT_HEADER => 0,
      CURLOPT_RETURNTRANSFER => 1,
    ));

    $response = new MailinResponse(curl_exec($ch));
    curl_close($ch);

    return $response;
  }

}
