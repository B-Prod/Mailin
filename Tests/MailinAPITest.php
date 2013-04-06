<?php

/**
 * @file
 * Provide PHPUnit tests for Mailin API class.
 */

namespace Mailin\Tests;

use Mailin\MailinAPI;
use Mailin\MailinLog;
use Mailin\Tests\MailinLogTest;

/**
 * Test class for the Mailin API class.
 *
 * @backupGlobals disabled
 * @backupStaticAttributes disabled
 */
class MailinAPITest extends \PHPUnit_Framework_TestCase {

  /**
   * The API key for the test account.
   */
  const API_KEY = 'sdfg545nh';

  /**
   * The Mailin API instance.
   *
   * @var MailinAPI
   */
  protected $mailinAPI;

  /**
   * The total number of calls to the Mailin server.
   *
   * @var int
   */
  protected static $apiCallsCount = 0;

  /**
   * @inheritdoc
   */
  protected function setUp() {
    $this->mailinAPI = new MailinAPI(self::API_KEY);
  }

  /**
   * Create a random folder name that does not exist in Mailin server.
   *
   * @todo what is the method to get all folders?
   */
  protected function createFolderName() {
    do {
      $name = \MailinTestHelper::randomName();
    }
    while ($this->findFolder($name));

    return $name;
  }

  /**
   * @covers Mailin\MailinAPI::getLists
   */
  public function testGetLists() {
    $lists = $this->mailinAPI->getLists();
    self::$apiCallsCount++;
    $this->assertInternalType('array', $lists, 'List fetch operation failed.');
    $this->assertEquals(self::$apiCallsCount, MailinLog::countApiCalls(), 'API calls count does not match.');
  }

  /**
   * @covers Mailin\MailinAPI::getFolders
   */
  public function testGetFolders() {
    $this->markTestIncomplete('Awaiting API call details to retrieve a list of folders.');
  }

  /**
   * @covers Mailin\MailinAPI::addFolder
   * @depends testGetLists
   */
  public function testAddFolder() {
    $folderName = \MailinTestHelper::randomName();
    $folderId = $this->mailinAPI->addFolder($folderName);
    self::$apiCallsCount++;

    $this->assertInternalType('int', $folderId, "Creation of folder $folderName failed.");
    $this->assertEquals(self::$apiCallsCount, MailinLog::countApiCalls(), 'API calls count does not match.');
    return array($folderId, $folderName);
  }

  /**
   * @covers Mailin\MailinAPI::addList
   * @depends testAddFolder
   */
  public function testAddList(array $folder) {
    list($folderId, ) = $folder;
    $listName = \MailinTestHelper::randomName();
    $listId = $this->mailinAPI->addList($listName, $folderId);
    self::$apiCallsCount++;

    $this->assertInternalType('int', $listId, "Creation of list $listId failed.");
    $this->assertEquals(self::$apiCallsCount, MailinLog::countApiCalls(), 'API calls count does not match.');
    return array($listId, $listName);
  }

  /**
   * @covers Mailin\MailinAPI::findLists
   * @depends testAddList
   */
  public function testFindList(array $list) {
    list($listId, $listName) = $list;

    // Force reset to ensure the lists are up to date, since we just added a new one
    // then verify that the list is correctly retrieved from the Mailin server.
    $this->assertTrue($this->mailinAPI->findList($listId, TRUE), "List $listName with ID $listId is missing in the fetched mailing lists.");
    self::$apiCallsCount++;
    $this->assertEquals(self::$apiCallsCount, MailinLog::countApiCalls(), 'API calls count does not match.');

    // Without reseting the lists, the API calls number should not increment.
    $this->assertTrue($this->mailinAPI->findList($listId), "List $listName with ID $listId is missing in the fetched mailing lists.");
    $this->assertEquals(self::$apiCallsCount, MailinLog::countApiCalls(), 'API calls count does not match.');
  }

  /**
   * @covers Mailin\MailinAPI::deleteList
   * @depends testAddList
   */
  public function testDeleteList(array $list) {
    list($listId, $listName) = $list;
    $this->assertTrue($this->mailinAPI->deleteList($listName, $listId));

    $this->markTestIncomplete('Awaiting some answers from the Mailin team, because something is broken there...');

    // Verify that the list has really been removed from the Mailin server.
    //$this->assertFalse($this->mailinAPI->findList($listId, TRUE), "List $listName with ID $listId was not removed.");
  }

  /**
   * @covers Mailin\MailinAPI::deleteList
   * @depends testAddFolder
   */
  public function testDeleteFolder(array $folder) {
    list($folderId, $folderName) = $folder;
    $this->assertTrue($this->mailinAPI->deleteFolder($folderName, $folderId));
  }

}
