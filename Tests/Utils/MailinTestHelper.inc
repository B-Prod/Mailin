<?php

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
      $values = array_unique(array_merge($values, array_map('ord', str_split($extraCharacters, 1))));
    }

    $max = count($values) - 1;
    $str = $case <= 0 ? chr(mt_rand(97, 122)) : chr(mt_rand(65, 90));

    for ($i = 2; $i < $length; $i++) {
      $str .= chr($values[mt_rand(0, $max)]);
    }

    if (strlen($str) < $length) {
      $str .= $case <= 0 ? chr(mt_rand(97, 122)) : chr(mt_rand(65, 90));
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
