<?php

/**
 * @file
 * Provide PHPUnit tests for Mailin Log class.
 */

namespace Mailin\Tests;

use Mailin\Tests\APITest as MailinTest;
use Mailin\API as Mailin;
use Mailin\Log;

/**
 * Test class for the Mailin Log class.
 *
 * @backupGlobals disabled
 * @backupStaticAttributes disabled
 */
class LogTest extends \PHPUnit_Framework_TestCase {

  /**
   * The Mailin API instance.
   *
   * @var Mailin\API
   */
  protected $mailin;

  /**
  * The Mailin\Log Reflection Class.
  *
  * @var ReflectionClass
  */
  protected static $rc;

  /**
   * Store the default values of the Mailin\Log static properties.
   *
   * @var array
   */
  protected static $defaultStaticProperties;

  /**
   * @inheritdoc
   */
  protected function setUp() {
    $this->mailin = new Mailin(MailinTest::API_KEY);
    self::ensureDefaultProperties();
  }

  /**
   * @inheritdoc
   */
  protected function TearDown() {
    self::restoreDefaultProperties();
  }

  /**
   * Get the Reflection class for the Maillin\MaillinLog class.
   */
  public static function getReflectionClass() {
    if (!isset(self::$rc)) {
      self::$rc = new \ReflectionClass('Mailin\Log');
    }

    return self::$rc;
  }

  /**
   * Ensure the default properties values have been initialized.
   */
  public static function ensureDefaultProperties() {
    if (!isset(self::$defaultStaticProperties)) {
      self::$defaultStaticProperties = self::getReflectionClass()->getDefaultProperties();
    }
  }

  /**
   * Restore the staic properties to their default values.
   */
  public static function restoreDefaultProperties() {
    // Set static properties to their default values.
    foreach (array_keys(self::getReflectionClass()->getStaticProperties()) as $name) {
      $property = self::getReflectionClass()->getProperty($name);
      $property->setAccessible(TRUE);
      $property->setValue(self::$defaultStaticProperties[$name]);
    }//end foreach
  }

}
