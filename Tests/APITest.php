<?php

/**
 * @file
 * Provide PHPUnit tests for Mailin API class.
 */

namespace Mailin\Tests;

use Mailin\API as Mailin;
use Mailin\Attribute as MA;
use Mailin\Log;
use Mailin\User;

/**
 * Test class for the Mailin API class.
 *
 * @backupGlobals disabled
 * @backupStaticAttributes disabled
 */
class APITest extends \PHPUnit_Framework_TestCase {

  /**
   * The API key for the test account.
   */
  const API_KEY = 'sdfg545nh';

  /**
   * The Mailin API instance.
   *
   * @var Mailin
   */
  protected $mailin;

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
    $this->mailin = new Mailin(self::API_KEY);
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

    $this->assertEquals(self::$apiCallsCount, Log::countApiCalls(), 'API calls count does not match.');
  }

  /**
   * Create a random folder name that does not exist in Mailin server.
   */
  protected function createFolderName() {
    $folders = array_map(function ($folder) { return $folder['name']; }, $this->mailin->getFolders());
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
    $lists = array_map(function ($list) { return $list['name']; }, $this->mailin->getListsFromFolder($parentId));
    self::$apiCallsCount++;

    do {
      $name = \MailinTestHelper::randomName();
    }
    while ($lists && in_array($name, $lists));

    return $name;
  }

  /**
   * @covers Mailin\API::getFolders
   */
  public function testGetFolders() {
    $folders = $this->mailin->getFolders();
    $this->assertInternalType('array', $folders, 'Folder fetch operation failed.');
    $this->assertCallcount();
  }

  /**
   * @covers Mailin\API::addFolder
   * @depends testGetFolders
   */
  public function testAddFolder() {
    $folderName = $this->createFolderName();

    $folderId = $this->mailin->addFolder($folderName);
    $this->assertInternalType('int', $folderId, "Creation of folder $folderName failed.");
    $this->assertCallcount(2); // 2 API calls.

    // Try to create a folder with the same name.
    $this->assertFalse($this->mailin->addFolder($folderName), "Creation of a folder with the same name $folderName should fail.");
    $this->assertCallcount(); // Only 1 API call, since the first call to findFoldersByName() stops the process.

    // Force the creation of a new folder even if a folder with the same name exists.
    $folderId2 = $this->mailin->addFolder($folderName, TRUE);
    $this->assertInternalType('int', $folderId, "Forcing the creation of folder with the same name $folderName failed.");
    $this->assertNotEquals($folderId, $folderId2, 'The two folders should not have the same ID.');
    $this->assertCallcount();

    return array($folderId => $folderName, $folderId2 => $folderName);
  }

  /**
   * @covers Mailin\API::findFolder
   * @depends testAddFolder
   */
  public function testFindFolder($folders) {
    list($folderId, $folderName) = each($folders);
    $folder = $this->mailin->findFolder($folderId);

    $this->assertInternalType('array', $folder, 'Find folder operation failed.');
    $this->assertEquals($folderId, $folder['id'], 'Returned folder has not the expected ID.');
    $this->assertEquals($folderName, $folder['name'], 'Returned folder has not the expected name.');
    $this->assertCallcount();
  }

  /**
   * @covers Mailin\API::findFoldersByName
   * @depends testAddFolder
   */
  public function testFindFoldersByName($folders) {
    // Search an unexisting folder.
    $this->assertEquals(array(), $this->mailin->findFoldersByName($this->createFolderName()), 'Unexisting folder search should return FALSE.');
    $this->assertCallcount();

    list($folderId, $folderName) = each($folders);
    $results = array_fill_keys(array_keys($this->mailin->findFoldersByName($folderName)), $folderName);
    ksort($results);
    ksort($folders);
    $this->assertEquals($results, $folders, 'The search should return the two newly created folders and only that.');
    $this->assertCallcount();
  }

  /**
   * @covers Mailin\API::getLists
   */
  public function testGetLists() {
    $lists = $this->mailin->getLists();
    $this->assertInternalType('array', $lists, 'List fetch operation failed.');
    $this->assertCallcount();
  }

  /**
   * @covers Mailin\API::addList
   * @depends testAddFolder
   */
  public function testAddList(array $folders) {
    list($folderId, $folderId2) = array_keys($folders);

    $listName = $this->createListName($folderId);
    $listId = $this->mailin->addList($listName, $folderId);

    $this->assertInternalType('int', $listId, "Creation of list $listId failed.");
    $this->assertCallcount(2); // 2 API calls.

    // Create a second list in the same folder.
    $listName2 = $this->createListName($folderId);
    $listId2 = $this->mailin->addList($listName2, $folderId);

    $this->assertInternalType('int', $listId2, "Creation of list $listId2 failed.");
    $this->assertCallcount(2); // 2 API calls.

    // Create a list with the same name, in the same folder.
    $this->assertFalse($this->mailin->addList($listName, $folderId), "Creation of a list with the same name $listName in the same folder should fail.");
    $this->assertCallcount(); // Only 1 API call, since the first call to findListsByName() stops the process.

    // Force the creation of a new list even if a list with the same name exists in the same folder.
    $listId3 = $this->mailin->addList($listName, $folderId, TRUE);
    $this->assertInternalType('int', $listId3, "Forcing the creation of list with the same name $listName in the same folder failed.");
    $this->assertFalse(in_array($listId3, array($listId, $listId2)), 'The lists should not have the same ID.');
    $this->assertCallcount();

    // Create a list with the same name, in a different folder, without forcing.
    $listId4 = $this->mailin->addList($listName, $folderId2);
    $this->assertInternalType('int', $listId4, "Creation of list with the same name $listName in a different folder failed.");
    $this->assertFalse(in_array($listId4, array($listId, $listId2, $listId3)), 'The three lists should not have the same ID.');
    $this->assertCallcount(2); // 2 API calls.

    // Check now that the created lists are present on the Mailin server.
    $lists = array_fill_keys(array($listId, $listId2, $listId3, $listId4), TRUE);
    $this->assertTrue(count(array_intersect_key($lists, $this->mailin->getLists())) === 4, 'All the expected lists are not present on the Mailin server.');
    $this->assertCallcount();

    return array(
      $folderId => array($listId => $listName, $listId2 => $listName2, $listId3 => $listName),
      $folderId2 => array($listId4 => $listName),
    );
  }

  /**
   * @covers Mailin\API::findLists
   * @depends testAddList
   */
  public function testFindList(array $foldersLists) {
    list($folderId, $lists) = each($foldersLists);
    list($listId, $listName) = each($lists);
    $list = $this->mailin->findList($listId);

    $this->assertInternalType('array', $list, 'Find list operation failed.');
    $this->assertEquals($listId, $list['id'], 'Returned list has not the expected ID.');
    $this->assertEquals($listName, $list['name'], 'Returned list has not the expected name.');
    $this->assertEquals($folderId, $list['folder_id'], 'Returned list has not the expected parent folder.');
    $this->assertCallcount();

    // Try wrong ID.
    $wrongId = rand(10000, 20000);
    $this->assertFalse($this->mailin->findList($wrongId), 'A unexisting ID should not return anything.');
    $this->assertCallcount();

    // Try existing folder ID.
    $this->assertFalse($this->mailin->findList($folderId), 'An existing folder ID should not return any list.');
    $this->assertCallcount();
  }

  /**
   * @covers Mailin\API::getListsFromFolder
   * @depends testAddList
   */
  function testGetListsFromFolder(array $foldersLists) {
    foreach ($foldersLists as $folderId => $lists) {
      $expectedIds = array_keys($lists);
      $count = sizeof($lists);
      $results = $this->mailin->getListsFromFolder($folderId);
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
   * @covers Mailin\API::findListsByName
   * @depends testAddList
   */
  public function testFindListsByName(array $foldersLists) {
    foreach ($foldersLists as $folderId => $lists) {
      foreach (array_count_values($lists) as $listName => $count) {
        $expectedIds = array_keys($lists, $listName);
        $results = $this->mailin->findListsByName($listName, $folderId);
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
   * @covers Mailin\API::getAttributes
   */
  public function testGetAttributes() {
    $result = $this->mailin->getAttributes();
    $this->assertInternalType('array', $result);

    // There is at least some locked attributes, so the result should not
    // be empty.
    $this->assertNotEmpty($result, 'Getting attribute list failed.');
    $this->assertCallcount(1);

    $this->assertEquals(array(), $this->mailin->getAttributes(array('unexisting-type')));
    $this->assertCallcount(0);
  }

  /**
   * @covers Mailin\API::addAttributes
   */
  public function testAddAttributes() {
    $callback = array('\MailinTestHelper', 'randomName');
    $args = array(8, 'TEST-', 1, FALSE, '_-');

    $attributes = array(
      MA\AttributeInterface::ATTRIBUTE_LIST_NORMAL => array(
        new MA\Normal(array(
          'name' => call_user_func_array($callback, $args),
          'type' => MA\AttributeInterface::ATTRIBUTE_DATA_TYPE_TEXT,
        )),
        new MA\Normal(array(
          'name' => call_user_func_array($callback, $args),
          'type' => MA\AttributeInterface::ATTRIBUTE_DATA_TYPE_NUMBER,
        )),
        new MA\Normal(array(
          'name' => call_user_func_array($callback, $args),
          'type' => MA\AttributeInterface::ATTRIBUTE_DATA_TYPE_DATE,
        )),
      ),
      MA\AttributeInterface::ATTRIBUTE_LIST_CATEGORY => array(
        new MA\Category(array(
          'name' => call_user_func_array($callback, $args),
          'enumeration' => array(
            \MailinTestHelper::randomName(),
            \MailinTestHelper::randomName(),
            \MailinTestHelper::randomName(),
          ),
        )),
      ),
    );

    $this->assertTrue($this->mailin->addAttributes($attributes), 'Attributes creation failed.');
    $this->assertCallcount();

    $results = $this->mailin->getAttributes(array(MA\AttributeInterface::ATTRIBUTE_LIST_NORMAL, MA\AttributeInterface::ATTRIBUTE_LIST_CATEGORY));
    $this->assertTrue(sizeof($results) === 2, 'Getting attribute list failed.');
    $this->assertCallcount(1);

    $missing = $attributes;
    $filter = new MA\Filter($results);

    // Check attributes correspondence.
    foreach ($attributes as $attributeType => $attributeInstances) {
      foreach ($attributeInstances as $key => $attribute) {
        $filter->setFilter('type', $attributeType)->setFilter('name', $attribute->getName());

        if ($found = $filter->getOne()) {
          switch ($attributeType) {
            case MA\AttributeInterface::ATTRIBUTE_LIST_NORMAL:
              if ($found->getDataType() === $attribute->getDataType()) {
                unset($missing[$attributeType][$key]);
              }
              break;

            case MA\AttributeInterface::ATTRIBUTE_LIST_CATEGORY:
              if (!array_diff($attribute->getEnumeration(TRUE), $found->getEnumeration(TRUE))) {
                unset($missing[$attributeType][$key]);
              }
              break;
          }//end switch
        }
      }//end foreach
    }//end foreach

    $this->assertEmpty(array_filter($missing), 'Some attributes have not been created.');

    // Try to save invalid attributes.
    $validAttributeName = \MailinTestHelper::randomName(8, 'TEST-', 1, FALSE, '_-');
    $invalid = array(
      array(
        'attribute' => array(),
        'message' => 'Attribute creation using an invalid attribute type should fail.',
      ),
      array(
        'attribute' => array(MA\AttributeInterface::ATTRIBUTE_LIST_NORMAL => 'not-array'),
        'message' => 'Attribute creation using an invalid structure should fail.',
      ),
      array(
        'attribute' => array(MA\AttributeInterface::ATTRIBUTE_LIST_NORMAL => array(
          new MA\Normal(array('name' => 'INVALID NAME', 'type' => MA\AttributeInterface::ATTRIBUTE_DATA_TYPE_TEXT)),
        )),
        'message' => 'Attribute creation using an invalid name should fail.',
      ),
      array(
        'attribute' => array(MA\AttributeInterface::ATTRIBUTE_LIST_NORMAL => array(
          new MA\Normal(array('name' => $validAttributeName, 'type' => 'INVALID_DATA_TYPE')),
        )),
        'message' => 'Attribute creation using an unexisting data type should fail.',
      ),
      array(
        'attribute' => array(MA\AttributeInterface::ATTRIBUTE_LIST_NORMAL => array(
          new MA\Normal(array('name' => $validAttributeName, 'type' => MA\AttributeInterface::ATTRIBUTE_DATA_TYPE_ID)),
        )),
        'message' => 'Attribute creation using an unsupported data type should fail.',
      ),
      array(
        'attribute' => array(MA\AttributeInterface::ATTRIBUTE_LIST_CATEGORY => array(
          new MA\Category(array('name' => $validAttributeName)),
        )),
        'message' => 'Category attribute creation using no enumeration should fail.',
      ),
    );

    foreach ($invalid as $test) {
      $test += array('increment' => 0);
      $this->assertFalse($this->mailin->addAttributes($test['attribute']), $test['message']);
      $this->assertCallcount($test['increment']);
    }//end foreach

    // @todo test adding a transactional attribute
    // @todo test adding a calculated attribute
    // @todo test adding a computed attribute

    return $attributes;
  }

  /**
   * @covers Mailin\API::deleteAttributes
   * @depends testAddAttributes
   */
  public function testDeleteAttributes(array $attributes) {
    $this->assertTrue($this->mailin->deleteAttributes($attributes), 'Attributes deletion failed.');
    $this->assertCallcount();

    // Check if the attributes were really deleted.
    $results = $this->mailin->getAttributes();
    $this->assertCallcount();

    $filter = new MA\Filter($results);

    foreach ($attributes as $attributeType => $instances) {
      foreach ($instances as $attribute) {
        $filter->setFilters(array('type' => $attributeType, 'name' => $attribute->getName()));

        if ($found = $filter->getOne()) {
          $this->fail('Atribute with name ' . $found->getName() . ' still exists while it should have been deleted.');
        }
      }
    }//end foreach
  }

  /**
   * @covers Mailin\API::saveUser
   * @depends testAddList
   */
  public function testSaveUser(array $foldersLists) {
    $listId = key(reset($foldersLists));

    $userEmail = \MailinTestHelper::randomEmail();
    $userId = $this->mailin->saveUser($userEmail, array($listId));
    $this->assertTrue(is_numeric($userId), "Creation of user $userEmail failed.");
    $result = $this->mailin->getUserStatus(array($userEmail));
    $this->assertInternalType('array', $result, "Impossible to retrieve user $userEmail related data.");
    $this->assertEquals(reset($result), 0, "User $userEmail status is incorrect.");
    $this->assertCallcount(2);

    // Create now a user who is blacklisted.
    $userEmail2 = \MailinTestHelper::randomEmail();
    $userId2 = $this->mailin->saveUser($userEmail2, array($listId), array(), TRUE);
    $this->assertTrue(is_numeric($userId2), "Creation of user $userEmail2 failed.");
    $result = $this->mailin->getUserStatus(array($userEmail2));
    $this->assertInternalType('array', $result, "Impossible to retrieve user $userEmail2 related data.");
    $this->assertEquals(reset($result), 1, "User $userEmail2 status is incorrect.");
    $this->assertCallcount(2);

    // Create a user who is registered on an unexisting list.
    do {
      $unexistingListId = rand(1, 9999);
      self::$apiCallsCount++;
    }
    while (FALSE !== $this->mailin->getList($unexistingListId));

    $this->assertCallcount(0);

    $userEmail3 = \MailinTestHelper::randomEmail();
    $userId3 = $this->mailin->saveUser($userEmail3, array($unexistingListId));
    $this->assertTrue(is_numeric($userId3), "Creation of user $userEmail3 failed.");
    $user = $this->mailin->getUser($userEmail3);
    $this->assertTrue(is_object($user) && $user instanceof User, "Impossible to retrieve user $userEmail3 related data.");
    $this->assertFalse($user->isRegistered($unexistingListId), "User $userEmail3 should not be registred on a unexisting list.");
    $this->assertCallcount(2);

    // Create a user without spécifying any list ID.
    $userEmail4 = \MailinTestHelper::randomEmail();
    $this->assertFalse($this->mailin->saveUser($userEmail4, array()), "Creation of user $userEmail4 without list ID should fail.");
    $this->assertCallcount(0);
    $this->assertFalse($this->mailin->saveUser($userEmail4, array(0)), "Creation of user $userEmail4 with 0 as list ID should fail.");
    $this->assertCallcount(0);
    $this->assertFalse($this->mailin->saveUser($userEmail4, array('')), "Creation of user $userEmail4 with empty string as list ID should fail.");
    $this->assertCallcount(0);

    // @todo save a user registered on multiple lists.

    return array(
      $userEmail => array('id' => $userId, 'listId' => $listId, 'status' => 0),
      $userEmail2 => array('id' => $userId2, 'listId' => $listId, 'status' => 1),
    );
  }

  /**
   * @covers Mailin\API::saveUser
   * @covers Mailin\API::getUser
   * @depends testAddList
   * @depends testAddAttributes
   */
  public function testSaveUserWithAttributes(array $foldersLists, array $attributes) {
    $listId = key(reset($foldersLists));
    $userEmail = \MailinTestHelper::randomEmail();
    $userAttributes = array();

    foreach ($attributes as $attributeType => $attributeInstances) {
      foreach ($attributeInstances as $attribute) {
        switch ($attributeType) {

          case MA\AttributeInterface::ATTRIBUTE_LIST_NORMAL:
            switch ($attribute->getDataType()) {

              case MA\AttributeInterface::ATTRIBUTE_DATA_TYPE_TEXT:
                $userAttributes[$attribute->getName()] = \MailinTestHelper::randomName();
                break;

              case MA\AttributeInterface::ATTRIBUTE_DATA_TYPE_NUMBER:
                $userAttributes[$attribute->getName()] = rand(1, 1000) . '.' . rand(1, 9);
                break;

              case MA\AttributeInterface::ATTRIBUTE_DATA_TYPE_DATE:
                $timestamp = rand(0, 1451602799); // january 1st, 1970 to december 31, 2015
                $userAttributes[$attribute->getName()] = date('Y-m-d', $timestamp);
                break;
            }//end switch
            break;

          case MA\AttributeInterface::ATTRIBUTE_LIST_CATEGORY:
            $enumeration = $attribute->getEnumeration();
            $key = array_rand($enumeration);
            $userAttributes[$attribute->getName()] = $enumeration[$key]['value'];
            break;

        }//end switch

      }//end foreach
    }//end foreach

    // Tests on user creation.
    $userId = $this->mailin->saveUser($userEmail, array($listId), $userAttributes);
    $this->assertTrue(is_numeric($userId), "Creation of user $userEmail failed.");
    $user = $this->mailin->getUser($userEmail);
    $this->assertTrue(is_object($user) && $user instanceof User, "Impossible to retrieve user $userEmail related data.");
    $this->assertCallcount(2);

    // Tests on user attributes.
    foreach ($attributes as $attributeType => $attributeInstances) {
      foreach ($attributeInstances as $attribute) {
        if (array_key_exists($attribute->getName(), $userAttributes)) {
          // Do not forget that User attribute types are not always the same as
          // Attribute types.
          $map = call_user_func(array(get_class($attribute), 'attributeEntityMap'));

          if (isset($map['user'])) {
            $handler = $user->getAttribute($map['user'], $attribute->getName());

            if (!isset($handler)) {
              $this->fail("The attribute {$attribute->getName()} was not saved.");
            }
            else {
              $this->assertEquals($handler->getValue(), $userAttributes[$attribute->getName()], "Saved data for {$attribute->getName()} attribute does not match the expected one: {$userAttributes[$attribute->getName()]}.");
            }
          }
        }
      }//end foreach
    }//end foreach
  }

  /**
   * @covers Mailin\API::saveUsers
   */
  public function testSaveUsers() {
    $this->markTestIncomplete();
  }

  /**
   * @covers Mailin\API::getUserStatus
   * @depends testSaveUser
   */
  public function testGetUserStatus(array $users) {
    $expected = array_map(function ($user) { return $user['status']; }, $users);
    $results = $this->mailin->getUserStatus(array_keys($users));
    ksort($expected);
    ksort($results);
    $this->assertInternalType('array', $results, "Users status retrieve operation failed.");
    $this->assertEquals($results, $expected, 'Retrieved results do not match expected values.');
    $this->assertCallcount();
  }

  /**
   * @covers Mailin\API::blockUser
   * @depends testSaveUser
   */
  public function testBlockUser(array $users) {
    list($userEmail, ) = each($users);
    $this->assertTrue($this->mailin->blockUser($userEmail), "The user $userEmail was blacklisted.");
    $this->assertEquals($this->mailin->getUserStatus(array($userEmail)), array($userEmail => 1), 'The user status is wrong.');
    $this->assertCallcount(2);
  }

  /**
   * @covers Mailin\API::unblockUser
   * @depends testSaveUser
   * @depends testBlockUser
   */
  public function testUnblockUser(array $users) {
    list($userEmail, ) = each($users);
    $this->assertTrue($this->mailin->unblockUser($userEmail), "The user $userEmail was removed from blacklist.");
    $this->assertEquals($this->mailin->getUserStatus(array($userEmail)), array($userEmail => 0), 'The user status is wrong.');
    $this->assertCallcount(2);
  }

  /**
   * @covers Mailin\API::deleteList
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
      $this->assertTrue($this->mailin->deleteList($listId), "List $listName with ID $listId was not deleted.");
      $this->assertCallcount();

      // Verify that the list has really been removed from the Mailin server.
      $this->assertFalse($this->mailin->findList($listId), "List $listName with ID $listId was not removed.");
      $this->assertCallcount();

      // Try now to delete a non-existing list.
      $this->assertFalse($this->mailin->deleteList($listId), "List with unexisting ID $listId could not be deleted.");
      $this->assertCallcount();
    }//end foreach

    $foldersLists[$folderId] = array();
    return $foldersLists;
  }

  /**
   * @covers Mailin\API::testDeleteFolder
   * @depends testAddFolder
   * @depends testDeleteList
   * @depends testFindFolder
   */
  public function testDeleteFolder(array $folders, array $foldersLists) {
    foreach ($folders as $folderId => $folderName) {
      $this->assertTrue($this->mailin->deleteFolder($folderId), "Folder $folderName with ID $folderId was not deleted.");
      $this->assertCallcount();

      // Verify that the folder has really been removed from the Mailin server.
      $this->assertFalse($this->mailin->findFolder($folderId), "Folder $folderName with ID $folderId was not removed.");
      $this->assertCallcount();

      // Try now to delete a non-existing folder.
      $this->assertFalse($this->mailin->deleteFolder($folderId), "Folder with unexisting ID $folderId could not deleted.");
      $this->assertCallcount();

      // Ensure that child lists have also been deleted.
      if (!empty($foldersLists[$folderId])) {
        foreach ($foldersLists[$folderId] as $listId => $listName) {
          $this->assertFalse($this->mailin->findList($listId), "List $listName with ID $listId was not removed while deleting its parent folder.");
          $this->assertCallcount();
        }//end foreach
      }
    }//end foreach
  }

}
