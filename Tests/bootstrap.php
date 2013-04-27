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
  public static function randomName($length = 8, $prefix = 'test-', $case = 0, $allowPunctuation = TRUE, $extraCharacters = '') {
    $values = array();

    // Add lowercase characters.
    if ($case <= 0) {
      $values = array_merge($values, range(97, 122));
    }

    // Add uppercase characters.
    if ($case >= 0) {
      $values = array_merge($values, range(65, 90));
    }

    // Add punctuation.
    if ($allowPunctuation) {
      $values = array_merge($values, range(48, 57));
    }

    // Add extra characters if any.
    if (!empty($extraCharacters)) {
      $values = array_unique(array_merge($values, array_map('ord', $extraCharacters)));
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
    return self::randomName($length, '', -1) . '@' . self::$emailDomain;
  }
}
