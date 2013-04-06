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

  public static function randomName($length = 8, $prefix = 'test-') {
    $values = array_merge(range(65, 90), range(97, 122), range(48, 57));
    $max = count($values) - 1;
    $str = chr(mt_rand(97, 122));
    for ($i = 1; $i < $length; $i++) {
      $str .= chr($values[mt_rand(0, $max)]);
    }
    return $prefix . $str;
  }

}
