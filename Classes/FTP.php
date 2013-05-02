<?php

/**
 * @file
 * Provide support for the Mailin FTP account.
 */

namespace Mailin;

/**
 * The Mailin FTP class.
 */
class FTP {

  /**
   * The IP of the Mailin FTP server.
   *
   * @var string
   */
  protected $ip;

  /**
   * The Mailin account login.
   *
   * @var string
   */
  protected $login;

  /**
   * The Mailin account password.
   *
   * @var string
   */
  protected $password;

  /**
   * Class constructor.
   *
   * @param $ip
   *   The FTP server IP address.
   * @param $login
   *   The Mailin account login.
   * @param $password
   *   The Mailin account password.
   */
  public function _construct($ip, $login, $password) {
    $this->ip = $ip;
    $this->login = $login;
    $this->password = $password;
  }

}
