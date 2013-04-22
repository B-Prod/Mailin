<?php

/**
 * @file
 * Bootstrap file for PHPUnit.
 */

include dirname(__DIR__) . '/mailin.php';

/**
 * The Mailin Test Helper class.
 *
 * This class provides some helpers for the test classes.
 */
abstract class MailinTestHelper {

  protected static $emailDomain = 'test.mailin.fr';

  /**
   * Create a random string that only uses alphbetical and numeric characters.
   *
   * The string always begins by a lowercase character.
   */
  public static function randomName($length = 8, $prefix = 'test-', $lowercaseOnly = FALSE) {
    $values = array_merge(range(97, 122), range(48, 57));

    if (!$lowercaseOnly) {
      $values = array_merge(range(65, 90), $values);
    }

    $max = count($values) - 1;
    $str = chr(mt_rand(97, 122));
    for ($i = 1; $i < $length; $i++) {
      $str .= chr($values[mt_rand(0, $max)]);
    }
    return $prefix . $str;
  }

  /**
   * Create a random e-mail address.
   */
  public static function randomEmail($length = 8) {
    return self::randomName($length, '', TRUE) . '@' . self::$emailDomain;
  }
}
