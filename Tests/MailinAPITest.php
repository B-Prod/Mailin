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
  protected static $apiCallsCount;

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
   */
  protected function createFolderName() {
    $folders = array_map(function ($folder) { return $folder['name']; }, $this->mailinAPI->getFolders());
    self::$apiCallsCount++;

    do {
      $name = \MailinTestHelper::randomName();
    }
    while ($folders && in_array($name, $folders));

    return $name;
  }

  /**
   * Create a random list name that does not exist in Mailin server.
   *
   * @param $parentId
   *   The parent folder ID.
   */
  protected function createListName($parentId) {
    $lists = array_map(function ($list) { return $list['name']; }, $this->mailinAPI->getListsFromFolder($parentId));
    self::$apiCallsCount++;

    do {
      $name = \MailinTestHelper::randomName();
    }
    while ($lists && in_array($name, $lists));

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
   * @covers Mailin\MailinAPI::addFolder
   * @depends testGetFolders
   */
  public function testAddFolder() {
    $folderName = $this->createFolderName();

    $folderId = $this->mailinAPI->addFolder($folderName);
    $this->assertInternalType('int', $folderId, "Creation of folder $folderName failed.");
    $this->assertCallcount(2); // 2 API calls.

    // Try to create a folder with the same name.
    $this->assertFalse($this->mailinAPI->addFolder($folderName), "Creation of a folder with the same name $folderName should fail.");
    $this->assertCallcount(); // Only 1 API call, since the first call to findFoldersByName() stops the process.

    // Force the creation of a new folder even if a folder with the same name exists.
    $folderId2 = $this->mailinAPI->addFolder($folderName, TRUE);
    $this->assertInternalType('int', $folderId, "Forcing the creation of folder with the same name $folderName failed.");
    $this->assertNotEquals($folderId, $folderId2, 'The two folders should not have the same ID.');
    $this->assertCallcount();

    return array($folderId => $folderName, $folderId2 => $folderName);
  }

  /**
   * @covers Mailin\MailinAPI::findFolder
   * @depends testAddFolder
   */
  public function testFindFolder($folders) {
    list($folderId, $folderName) = each($folders);
    $folder = $this->mailinAPI->findFolder($folderId);

    $this->assertInternalType('array', $folder, 'Find folder operation failed.');
    $this->assertEquals($folderId, $folder['id'], 'Returned folder has not the expected ID.');
    $this->assertEquals($folderName, $folder['name'], 'Returned folder has not the expected name.');
    $this->assertCallcount();
  }

  /**
   * @covers Mailin\MailinAPI::findFoldersByName
   * @depends testAddFolder
   */
  public function testFindFoldersByName($folders) {
    // Search an unexisting folder.
    $this->assertEquals(array(), $this->mailinAPI->findFoldersByName($this->createFolderName()), 'Unexisting folder search should return FALSE.');
    $this->assertCallcount();

    list($folderId, $folderName) = each($folders);
    $results = array_fill_keys(array_keys($this->mailinAPI->findFoldersByName($folderName)), $folderName);
    ksort($results);
    ksort($folders);
    $this->assertEquals($results, $folders, 'The search should return the two newly created folders and only that.');
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
   * @covers Mailin\MailinAPI::addList
   * @depends testAddFolder
   */
  public function testAddList(array $folders) {
    list($folderId, $folderId2) = array_keys($folders);

    $listName = $this->createListName($folderId);
    $listId = $this->mailinAPI->addList($listName, $folderId);

    $this->assertInternalType('int', $listId, "Creation of list $listId failed.");
    $this->assertCallcount(2); // 2 API calls.

    // Create a second list in the same folder.
    $listName2 = $this->createListName($folderId);
    $listId2 = $this->mailinAPI->addList($listName2, $folderId);

    $this->assertInternalType('int', $listId2, "Creation of list $listId2 failed.");
    $this->assertCallcount(2); // 2 API calls.

    // Create a list with the same name, in the same folder.
    $this->assertFalse($this->mailinAPI->addList($listName, $folderId), "Creation of a list with the same name $listName in the same folder should fail.");
    $this->assertCallcount(); // Only 1 API call, since the first call to findListsByName() stops the process.

    // Force the creation of a new list even if a list with the same name exists in the same folder.
    $listId3 = $this->mailinAPI->addList($listName, $folderId, TRUE);
    $this->assertInternalType('int', $listId3, "Forcing the creation of list with the same name $listName in the same folder failed.");
    $this->assertFalse(in_array($listId3, array($listId, $listId2)), 'The lists should not have the same ID.');
    $this->assertCallcount();

    // Create a list with the same name, in a different folder, without forcing.
    $listId4 = $this->mailinAPI->addList($listName, $folderId2);
    $this->assertInternalType('int', $listId4, "Creation of list with the same name $listName in a different folder failed.");
    $this->assertFalse(in_array($listId4, array($listId, $listId2, $listId3)), 'The three lists should not have the same ID.');
    $this->assertCallcount(2); // 2 API calls.

    return array(
      $folderId => array($listId => $listName, $listId2 => $listName2, $listId3 => $listName),
      $folderId2 => array($listId4 => $listName),
    );
  }

  /**
   * @covers Mailin\MailinAPI::findLists
   * @depends testAddList
   */
  public function testFindList(array $foldersLists) {
    list($folderId, $lists) = each($foldersLists);
    list($listId, $listName) = each($lists);
    $list = $this->mailinAPI->findList($listId);

    $this->assertInternalType('array', $list, 'Find list operation failed.');
    $this->assertEquals($listId, $list['id'], 'Returned list has not the expected ID.');
    $this->assertEquals($listName, $list['name'], 'Returned list has not the expected name.');
    $this->assertEquals($folderId, $list['folder_id'], 'Returned list has not the expected parent folder.');
    $this->assertCallcount();

    // Try wrong ID.
    $wrongId = rand(10000, 20000);
    $this->assertFalse($this->mailinAPI->findList($wrongId), 'A unexisting ID should not return anything.');
    $this->assertCallcount();

    // Try existing folder ID.
    $this->assertFalse($this->mailinAPI->findList($folderId), 'An existing folder ID should not return any list.');
    $this->assertCallcount();
  }

  /**
   * @covers Mailin\MailinAPI::getListsFromFolder
   * @depends testAddList
   */
  function testGetListsFromFolder(array $foldersLists) {
    foreach ($foldersLists as $folderId => $lists) {
      $expectedIds = array_keys($lists);
      $count = sizeof($lists);
      $results = $this->mailinAPI->getListsFromFolder($folderId);
      $resultIds = array_keys($results);
      sort($expectedIds);
      sort($resultIds);

      $this->assertEquals($resultIds, $expectedIds, "The search should return the $count list(s) and only that.");
      $this->assertCallcount();

      // Check the list properties.
      foreach ($results as $id => $list) {
        $this->assertEquals($lists[$id], $list['name'], 'Returned list has not the expected name.');
        $this->assertEquals($folderId, $list['folder_id'], 'Returned list has not the expected parent folder.');
      }//end foreach
    }//end foreach
  }

  /**
   * @covers Mailin\MailinAPI::findListsByName
   * @depends testAddList
   */
  public function testFindListsByName(array $foldersLists) {
    foreach ($foldersLists as $folderId => $lists) {
      foreach (array_count_values($lists) as $listName => $count) {
        $expectedIds = array_keys($lists, $listName);
        $results = $this->mailinAPI->findListsByName($listName, $folderId);
        $resultIds = array_keys($results);
        sort($expectedIds);
        sort($resultIds);

        $this->assertEquals($resultIds, $expectedIds, "The search should return the $count list(s) and only that.");
        $this->assertCallcount();

        // Check the list properties.
        foreach ($results as $list) {
          $this->assertEquals($listName, $list['name'], 'Returned list has not the expected name.');
          $this->assertEquals($folderId, $list['folder_id'], 'Returned list has not the expected parent folder.');
        }//end foreach
      }//end foreach
    }//end foreach
  }

  /**
   * @covers Mailin\MailinAPI::addAttributes
   */
  public function testAddAttributes() {
    $this->markTestIncomplete();
  }

  /**
   * @covers Mailin\MailinAPI::deleteAttributes
   */
  public function testDeleteAttributes() {
    $this->markTestIncomplete();
  }

  /**
   * @covers Mailin\MailinAPI::saveUser
   * @depends testAddList
   */
  public function testSaveUser(array $foldersLists) {
    $listId = key(reset($foldersLists));

    $userEmail = \MailinTestHelper::randomEmail();
    $userId = $this->mailinAPI->saveUser($userEmail, array(), array($listId));
    $this->assertTrue(is_numeric($userId), "Creation of user $userEmail failed.");
    $result = $this->mailinAPI->getUserStatus(array($userEmail));
    $this->assertInternalType('array', $result, "Impossible to retrieve user $userEmail related data.");
    $this->assertEquals(reset($result), 0, "User $userEmail status is incorrect.");
    $this->assertCallcount(2);

    // Create now a user who is blacklisted.
    $userEmail2 = \MailinTestHelper::randomEmail();
    $userId2 = $this->mailinAPI->saveUser($userEmail2, array(), array($listId), TRUE);
    $this->assertTrue(is_numeric($userId2), "Creation of user $userEmail2 failed.");
    $result = $this->mailinAPI->getUserStatus(array($userEmail2));
    $this->assertInternalType('array', $result, "Impossible to retrieve user $userEmail2 related data.");
    $this->assertEquals(reset($result), 1, "User $userEmail2 status is incorrect.");
    $this->assertCallcount(2);

    //$this->markTestIncomplete('We should test user creation using attributes, but there is no implemented method that allows to retrieve those data from Mailin server yet.');

    return array(
      $userEmail => array('id' => $userId, 'listId' => $listId, 'status' => 0),
      $userEmail2 => array('id' => $userId2, 'listId' => $listId, 'status' => 1),
    );
  }

  /**
   * @covers Mailin\MailinAPI::saveUsers
   */
  public function testSaveUsers() {
    $this->markTestIncomplete();
  }

  /**
   * @covers Mailin\MailinAPI::getUserStatus
   * @depends testSaveUser
   */
  public function testGetUserStatus(array $users) {
    $expected = array_map(function ($user) { return $user['status']; }, $users);
    $results = $this->mailinAPI->getUserStatus(array_keys($users));
    ksort($expected);
    ksort($results);
    $this->assertInternalType('array', $results, "Users status retrieve operation failed.");
    $this->assertEquals($results, $expected, 'Retrieved results do not match expected values.');
    $this->assertCallcount();
  }

  /**
   * @covers Mailin\MailinAPI::blockUser
   * @depends testSaveUser
   */
  public function testBlockUser(array $users) {
    list($userEmail, ) = each($users);
    $this->assertTrue($this->mailinAPI->blockUser($userEmail), "The user $userEmail was blacklisted.");
    $this->assertEquals($this->mailinAPI->getUserStatus(array($userEmail)), array($userEmail => 1), 'The user status is wrong.');
    $this->assertCallcount(2);
  }

  /**
   * @covers Mailin\MailinAPI::unblockUser
   * @depends testSaveUser
   * @depends testBlockUser
   */
  public function testUnblockUser(array $users) {
    list($userEmail, ) = each($users);
    $this->assertTrue($this->mailinAPI->unblockUser($userEmail), "The user $userEmail was removed from blacklist.");
    $this->assertEquals($this->mailinAPI->getUserStatus(array($userEmail)), array($userEmail => 0), 'The user status is wrong.');
    $this->assertCallcount(2);
  }

  /**
   * @covers Mailin\MailinAPI::deleteList
   * @depends testAddList
   * @depends testFindList
   *
   * Here we try to delete the lists that belongs to the first created folder,
   * to leave the testDeleteFolder method the possibility to delete the second
   * folder with its lists.
   */
  public function testDeleteList(array $foldersLists) {
    list($folderId, ) = array_keys($foldersLists);

    foreach ($foldersLists[$folderId] as $listId => $listName) {
      $this->assertTrue($this->mailinAPI->deleteList($listId), "List $listName with ID $listId was not deleted.");
      $this->assertCallcount();

      // Verify that the list has really been removed from the Mailin server.
      $this->assertFalse($this->mailinAPI->findList($listId), "List $listName with ID $listId was not removed.");
      $this->assertCallcount();

      // Try now to delete a non-existing list.
      $this->assertFalse($this->mailinAPI->deleteList($listId), "List with unexisting ID $listId could not be deleted.");
      $this->assertCallcount();
    }//end foreach

    $foldersLists[$folderId] = array();
    return $foldersLists;
  }

  /**
   * @covers Mailin\MailinAPI::testDeleteFolder
   * @depends testAddFolder
   * @depends testDeleteList
   * @depends testFindFolder
   */
  public function testDeleteFolder(array $folders, array $foldersLists) {
    foreach ($folders as $folderId => $folderName) {
      $this->assertTrue($this->mailinAPI->deleteFolder($folderId), "Folder $folderName with ID $folderId was not deleted.");
      $this->assertCallcount();

      // Verify that the folder has really been removed from the Mailin server.
      $this->assertFalse($this->mailinAPI->findFolder($folderId), "Folder $folderName with ID $folderId was not removed.");
      $this->assertCallcount();

      // Try now to delete a non-existing folder.
      $this->assertFalse($this->mailinAPI->deleteFolder($folderId), "Folder with unexisting ID $folderId could not deleted.");
      $this->assertCallcount();

      // Ensure that child lists have also been deleted.
      if (!empty($foldersLists[$folderId])) {
        foreach ($foldersLists[$folderId] as $listId => $listName) {
          $this->assertFalse($this->mailinAPI->findList($listId), "List $listName with ID $listId was not removed while deleting its parent folder.");
          $this->assertCallcount();
        }//end foreach
      }
    }//end foreach
  }

}
