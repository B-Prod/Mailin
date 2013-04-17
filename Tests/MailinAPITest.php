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
   * Assert that the number of API call is correct.
   *
   * @param $increment
   *   How much the internal counter of API call should be incremented.
   */
  protected function assertCallcount($increment = 1) {
    if ($increment) {
      self::$apiCallsCount += $increment;
    }

    $this->assertEquals(self::$apiCallsCount, MailinLog::countApiCalls(), 'API calls count does not match.');
  }

  /**
   * Create a random folder name that does not exist in Mailin server.
   *
   * @todo what is the method to get all folders?
   */
  protected function createFolderName() {
    // Do not forget to increment the counter!
    self::$apiCallsCount++;

    if ($folders = $this->mailinAPI->getFolders()) {
      $folders = array_map(function ($folder) { return $folder['name']; }, $folders);
    }

    do {
      $name = \MailinTestHelper::randomName();
    }
    while ($folder && !in_array($name, $folders));

    return $name;
  }

  /**
   * @covers Mailin\MailinAPI::getFolders
   */
  public function testGetFolders() {
    $folders = $this->mailinAPI->getFolders();
    $this->assertInternalType('array', $folders, 'Folder fetch operation failed.');
    $this->assertCallcount();
  }

  /**
   * @covers Mailin\MailinAPI::getLists
   */
  public function testGetLists() {
    $lists = $this->mailinAPI->getLists();
    $this->assertInternalType('array', $lists, 'List fetch operation failed.');
    $this->assertCallcount();
  }

  /**
   * @covers Mailin\MailinAPI::addFolder
   * @depends testGetFolders
   */
  public function testAddFolder() {
    $folderName = \MailinTestHelper::randomName();
    $folderId = $this->mailinAPI->addFolder($folderName);

    $this->assertInternalType('int', $folderId, "Creation of folder $folderName failed.");
    $this->assertCallcount();
    return array($folderId, $folderName);
  }

  /**
   * @covers Mailin\MailinAPI::findFolder
   * @depends testAddFolder
   */
  public function testFindFolder($folder) {
    list($folderId, $folderName) = $folder;
    $folder = $this->mailinAPI->findFolder($folderId);

    $this->assertInternalType('array', $folder, 'Find folder operation failed.');
    $this->assertEquals($folderId, $folder['id'], 'Returned folder has not the expected ID.');
    $this->assertEquals($folderName, $folder['name'], 'Returned folder has not the expected name.');
    $this->assertCallcount();
  }

  /**
   * @covers Mailin\MailinAPI::addList
   * @depends testAddFolder
   */
  public function testAddList(array $folder) {
    list($folderId, ) = $folder;
    $listName = \MailinTestHelper::randomName();
    $listId = $this->mailinAPI->addList($listName, $folderId);

    $this->assertInternalType('int', $listId, "Creation of list $listId failed.");
    $this->assertCallcount();
    return array($listId, $listName);
  }

  /**
   * @covers Mailin\MailinAPI::findLists
   * @depends testAddList
   */
  public function testFindList(array $list) {
    list($listId, $listName) = $list;
    $list = $this->mailinAPI->findList($listId);

    $this->assertInternalType('array', $list, 'Find list operation failed.');
    $this->assertEquals($listId, $list['id'], 'Returned list has not the expected ID.');
    $this->assertEquals($listName, $list['name'], 'Returned list has not the expected name.');
    $this->assertCallcount();
  }

  /**
   * @covers Mailin\MailinAPI::deleteList
   * @depends testAddList
   * @depends testFindList
   */
  public function testDeleteList(array $list) {
    list($listId, $listName) = $list;
    $this->assertTrue($this->mailinAPI->deleteList($listId), "List $listName with ID $listId was not deleted.");
    $this->assertCallcount();

    // Verify that the list has really been removed from the Mailin server.
    $this->assertFalse($this->mailinAPI->findList($listId), "List $listName with ID $listId was not removed.");
    self::$apiCallsCount++;

    // Try now to delete a non-existing list.
    $this->markTestIncomplete('Delete a non-existing list...');
  }

  /**
   * @covers Mailin\MailinAPI::deleteList
   * @depends testAddFolder
   * @depends testFindFolder
   */
  public function testDeleteFolder(array $folder) {
    list($folderId, $folderName) = $folder;
    $this->assertTrue($this->mailinAPI->deleteFolder($folderId), "Folder $folderName with ID $folderId was not deleted.");
    $this->assertCallcount();

    // Verify that the folder has really been removed from the Mailin server.
    $this->assertFalse($this->mailinAPI->findFolder($folderId), "Folder $folderName with ID $folderId was not removed.");
    self::$apiCallsCount++;

    // Try now to delete a non-existing folder.
    $this->markTestIncomplete('Delete a non-existing folder...');

    // Try now to delete a folder which contains several lists.
    $this->markTestIncomplete('Delete a non-empty folder...');
  }

}
