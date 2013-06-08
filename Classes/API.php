<?php

/**
 * @file
 * Mailin class that handles the API calls to the mailin server.
 */

namespace Mailin;

use Mailin\Attribute as MA;
use Mailin\Request AS MR;
use Mailin\Response\Response;

/**
 * Mailin API class.
 */
class API {

  /**
   * @defgroup API actions
   * @{
   */

  /**
   * Action that creates a new folder.
   */
  const ACTION_FOLDER_ADD = 'ADDFOLDER';

  /**
   * Action that deletes a mailing list or an entire folder.
   */
  const ACTION_FOLDER_DELETE = 'DELETE-FOLDER';

  /**
   * Action that retrieves the folder list and their related mailin lists.
   */
  const ACTION_FOLDER_GET = 'DISPLAY-FOLDERS-LISTS';

  /**
   * Action that creates a new mailing list.
   */
  const ACTION_LIST_ADD = 'NEWLIST';

  /**
   * Action that retrieves the attribute list.
   */
  const ACTION_ATTRIBUTE_DISPLAY = 'DISPLAY-ATTRIBUTES';

  /**
   * Action that creates a set of attributes.
   */
  const ACTION_ATTRIBUTE_ADD = 'CREATE-ATTRIBUTES';

  /**
   * Action that delete a set of attributes.
   */
  const ACTION_ATTRIBUTE_DELETE = 'DELETE-ATTRIBUTES';

  /**
   * Action that retrieves data about one or more users.
   */
  const ACTION_USER_DETAILS = 'SUBSCRIBER-DETAILS';

  /**
   * Action that exposes the user statuses.
   */
  const ACTION_USER_STATUS = 'USERS-STATUS';

  /**
   * Action that retrieves a list of users.
   */
  const ACTION_USER_EXPORT = 'EXPORTFIND';

  /**
   * Action that imports a set of users to a mailing list.
   */
  const ACTION_USER_IMPORT = 'IMPORTUSERS';

  /**
   * Action that creates or updates a user.
   */
  const ACTION_USER_SAVE = 'USERCREADIT';

  /**
   * Action that creates several users at once.
   */
  const ACTION_USER_ADD_MULTIPLE = 'MULTI-USERCREADIT';

  /**
   * Action that set the user as blacklisted.
   */
  const ACTION_USER_BLOCK = 'EMAILBLACKLIST';

  /**
   * Action that remove the user from the blacklist.
   */
  const ACTION_USER_UNBLOCK = 'EMAILUNBLACKLIST';

  /**
   * Action that adds or updates a campaign.
   */
  const ACTION_CAMPAIGN_SAVE = 'CAMPCREADIT';

  /**
   * Action that retrieve a specific campaign statistics.
   */
  const ACTION_CAMPAIGN_STATS = 'CAMPAIGNDETAIL';

  /**
   * Action that retrieve user statistics related to a specific campaign.
   */
  const ACTION_CAMPAIGN_USER_STATS = 'CAMPUSERDETAIL';

  /**
   * @} End of "defgroup API actions".
   */

  /**
   * The folder type name.
   */
  const TYPE_FOLDER = 'FOLDER';

  /**
   * The list type name.
   */
  const TYPE_LIST = 'LIST';

  /**
   * Target all the campaign recipients.
   */
  const RECIPIENT_TYPE_ALL = 'all';

  /**
   * Target the campaign recipients who didn't click on the email.
   */
  const RECIPIENT_TYPE_NON_CLICKER = 'non_clicker';

  /**
   * Target the campaign recipients who didn't open the email.
   */
  const RECIPIENT_TYPE_NON_OPENER = 'non_opener';

  /**
   * The Mailin API key.
   *
   * @var string
   */
  protected $apiKey;

  /**
   * The request instance that is used to handle requests against Mailin server.
   *
   * @var Mailin\Request\RequestInterface
   */
  protected $requestHandler;

  /**
   * Class constructor.
   *
   * @param $apiKey
   * @param $requestHandler
   */
  public function __construct($apiKey, MR\RequestInterface $requestHandler = NULL) {
    $this->apiKey = $apiKey;
    $this->requestHandler = isset($requestHandler) ? $requestHandler : new MR\Curl();
  }

  /**
   * Set the object instance that handles requests against Mailin server.
   *
   * @param $requesthandler
   *   The new request handler object instance.
   *
   * @return API
   *   The current instance.
   */
  public function setRequesthandler(MR\RequestInterface $requesthandler) {
    $this->requestHandler = $requesthandler;
  }

  /**
   * Perform a request against Mailin server.
   *
   * @param $action
   *   The type of the action to perform.
   * @param $query
   *   The data to use as post for the request.
   *
   * @return Mailin\Response\ResponseInterface
   *   The Mailin response object.
   */
  protected function query($action, array $query = array()) {
    foreach ($query as $key => $value) {
      $separator = '|';

      if (is_array($value) && isset($value['_separator_'])) {
        $separator = $value['_separator_'];
        unset($value['_separator_']);
      }

      if (is_null($value) || (is_string($value) && !strlen($value)) || (is_array($value) && !$value)) {
        unset($query[$key]);
      }
      elseif (is_array($value)) {
        $query[$key] = implode($separator, $value);
      }
    }//end foreach

    $query += array('key' => $this->apiKey, 'webaction' => $action);

    Log::startApiCall($query);
    $response = $this->requestHandler->request($query);
    Log::endApiCall($response);

    return $response;
  }

  /**
   * Get the existing folders with their lists.
   *
   * @param $ids
   *   An array of folder and/or list IDs for which retrieve information.
   *
   * @return array
   *   An array of the existing folders and lists that match the criteria.
   */
  public function getFolders(array $ids = array()) {
    return $this->query(self::ACTION_FOLDER_GET, array('ids' => $ids))->getDataOnSuccess();
  }

  /**
   * Get a folder from its ID.
   *
   * @param $id
   *   The folder ID.
   *
   * @return mixed
   *   The folder data or FALSE on failure.
   */
  public function findFolder($id) {
    if ($folders = $this->getFolders(array($id))) {
      if (isset($folders[$id]) && $folders[$id]['type'] === self::TYPE_FOLDER) {
        $folders[$id]['id'] = $id;
        return $folders[$id];
      }
    }

    return FALSE;
  }

  /**
   * Get a folder from its name.
   *
   * @param $$name
   *   The folder $name.
   *
   * @return array
   *   A list of folders.
   */
  public function findFoldersByName($name) {
    $match = array();

    if ($folders = $this->getFolders()) {
      foreach ($folders as $id => $folder) {
        if ($folder['type'] === self::TYPE_FOLDER && $folder['name'] === $name) {
          $match[$id] = $folder;
        }
      }//end foreach
    }

    return $match;
  }

  /**
   * Create a new folder on the Mailin server.
   *
   * @param $name
   *   The folder name.
   * @param $force
   *   A flag indicating if a folder should be created even if a folder
   *   with the same name already exists.
   *
   * @return mixed
   *   The newly created folder ID or FALSE on failure.
   *
   * @see http://ressources.mailin.fr/ajouter-un-dossier/?lang=en
   */
  public function addFolder($name, $force = FALSE) {
    if (FALSE != $force || !$this->findFoldersByName($name)) {
      $result = $this->query(self::ACTION_FOLDER_ADD, array('foldername' => $name))->getResult('folder_id');
      return !empty($result) ? $result : FALSE;
    }

    return FALSE;
  }

  /**
   * Delete a folder from the Mailin server.
   *
   * @todo should we add a parameter to define the type of object to delete (list
   * or folder) and ensure the given ID corresponds to this type?
   * The main issue here is about performance, since an extra API call is required.
   *
   * @param $id
   *   The folder ID.
   *
   * @return boolean
   *   TRUE on success, otherwise FALSE.
   *
   * @see http://ressources.mailin.fr/supprimer-le-dossier-liste/?lang=en
   */
  public function deleteFolder($id) {
    return $this->query(self::ACTION_FOLDER_DELETE, array('list_id' => $id))->getResult() === 'success';
  }

  /**
   * Get the existing mailing lists.
   *
   * @param $ids
   *   An array of list IDs for which retrieve information.
   *
   * @return array
   *   An array of the existing lists.
   */
  public function getLists(array $ids = array()) {
    $lists = array();

    if ($result = $this->getFolders($ids)) {
      foreach ($result as $fid => $folder) {
        if (!empty($folder['lists'])) {
          // @todo use a specific interface for handling list and folders.
          // Moreover, the ID parameter is not included in the array describing
          // the list information. It is only available as array key.
          $lists += $folder['lists'];
        }
      }//end foreach
    }

    return $lists;
  }

  /**
   * Get an existing mailing list.
   *
   * @param $id
   *   The list ID to look for.
   *
   * @return array
   *   Information about the the list if exists or FALSE.
   */
  public function getList($id) {
    return ($list = $this->getLists(array($id))) ? reset($list) : FALSE;
  }

  /**
   * Get all lists that belong to a given folder.
   *
   * @param $parentId
   *   The parent folder ID.
   *
   * @return array
   *   A list of mailin lists.
   */
  public function getListsFromFolder($parentId) {
    if ($folder = $this->findFolder($parentId)) {
      if (!empty($folder['lists'])) {
        return $folder['lists'];
      }
    }

    return array();
  }

  /**
   * Get a list from its name.
   *
   * @param $name
   *   The folder $name.
   * @param $parentId
   *   The parent folder ID.
   *
   * @return array
   *   A list of mailin lists.
   */
  public function findListsByName($name, $parentId) {
    $match = array();

    foreach ($this->getListsFromFolder($parentId) as $id => $list) {
      if ($list['type'] === self::TYPE_LIST && $list['name'] === $name) {
        $match[$id] = $list;
      }
    }//end foreach

    return $match;
  }

  /**
   * Create a new list on the Mailin server.
   *
   * @param $name
   *   The list name.
   * @param $parentId
   *   The parent folder ID.
   * @param $force
   *   A flag indicating if a list should be created even if a list
   *   with the same name already exists in the parent folder.
   *
   * @return mixed
   *   The newly created list ID or FALSE on failure.
   */
  public function addList($name, $parentId, $force = FALSE) {
    if (FALSE != $force || !$this->findListsByName($name, $parentId)) {
      return $this->query(self::ACTION_LIST_ADD, array('listname' => $name, 'list_parent' => $parentId))->getResult();
    }

    return FALSE;
  }

  /**
   * Delete a list from the Mailin server.
   *
   * This is only a convenient wrapper against the deleteFolder() method,
   * because the API call is exactly the same.
   *
   * @todo should we ensure that the given ID corresponds to a list and not a
   * folder?
   *
   * @param $id
   *   The list ID.
   *
   * @return boolean
   *   TRUE on success, otherwise FALSE.
   */
  public function deleteList($id) {
    return $this->deleteFolder($id);
  }

  /**
   * Get a mailin list from its ID.
   *
   * @param $id
   *   The list ID.
   *
   * @return mixed
   *   The list data or FALSE on failure.
   */
  public function findList($id) {
    if ($lists = $this->getFolders(array($id))) {
      if (isset($lists[$id]) && $lists[$id]['type'] === self::TYPE_LIST) {
        $lists[$id]['id'] = $id;
        return $lists[$id];
      }
    }

    return FALSE;
  }

  /**
   * Get the existing attributes.
   *
   * @param $types
   *   An array of types that are used to restrict the results. If nothing is
   *   specified there, the full list of attributes is returned.
   *
   * @return array
   *   An array of the existing attributes keyed by type.
   *
   * @see http://ressources.mailin.fr/affichage-attributs/?lang=en
   */
  public function getAttributes(array $types = array()) {
    if (! $types) {
      $types = MA\Attribute::getAllTypes();
    }
    else {
      $types = array_intersect($types, MA\Attribute::getAllTypes());
    }

    // If the $types array was filled with invalid data, it can be empty. In
    // such case, no need to perform a request.
    if ($types) {
      $attributes = $this->query(self::ACTION_ATTRIBUTE_DISPLAY, array_fill_keys($types, 1))->getDataOnSuccess();

      // An extra process is currently necessary because the structure of the array returned
      // by the Mailin server is not the expected one.
      array_walk($attributes, array($this, 'processRawAttributes'));

      return $attributes;
    }

    return array();
  }

  /**
   * Temporary method that is used through array_walk() to
   * clean the attribute structure
   */
  protected function processRawAttributes(&$attributes, $attributeType) {
    $class = array_search($attributeType, MA\Attribute::getAllTypes());

    // An issue in the Mailin DISPLAY-ATTRIBUTES service forces us to use array_map()
    // to make the returned sata match the structure defined in the documentation.
    $attributes = array_map('reset', $attributes);

    foreach ($attributes as &$attribute) {
      // Extra process is required for category attributes, due to the same issue as above.
      if ($attributeType === MA\Attribute::ATTRIBUTE_LIST_CATEGORY) {
        if (!empty($attribute['enumeration'])) {
          $attribute['enumeration'] = array_map('reset', $attribute['enumeration']);
        }
      }

      $attribute = new $class($attribute);
    }//end foreach
  }

  /**
   * Add a set of attributes.
   *
   * @param $attributes
   *   An array of attributes, keyed by attribute type. Each attribute is an
   *   object whose class implements the Mailin\Attribute\AttributeInterface interface.
   *
   * @return boolean
   *   TRUE on success, otherwise FALSE.
   *
   * @see http://ressources.mailin.fr/creer-un-attribut/?lang=en
   */
  public function addAttributes(array $attributes) {
    if ($attributes = array_intersect_key($attributes, array_flip(MA\Attribute::getAllTypes()))) {
      $args = array();

      foreach ($attributes as $attributeType => $definitions) {
        if (is_array($definitions)) {
          foreach ($definitions as $attribute) {
            if ($attribute instanceof MA\AttributeInterface && $definition = (string) $attribute) {
              $args[$attributeType][] = $definition;
            }
          }//end foreach
        }

        if (!empty($args[$attributeType])) {
          $args[$attributeType]['_separator_'] = ' | ';
        }
      }//end foreach

      if ($args) {
        return $this->query(self::ACTION_ATTRIBUTE_ADD, $args)->getResult() === 'OK';
      }
    }

    return FALSE;
  }

  /**
   * Delete a set of attributes.
   *
   * @param $attributes
   *   An array of attribute names, keyed by attribute type. It is also
   *   possible to use an instance of Mailin\Attibute\AttributeInterface
   *   instead of the attribute name.
   *
   * @return boolean
   *   TRUE on success, otherwise FALSE.
   *
   * @see http://ressources.mailin.fr/supprimer-les-attributs/?lang=en
   */
  public function deleteAttributes(array $attributes) {
    if ($args = array_intersect_key($attributes, array_flip(MA\Attribute::getAllTypes()))) {
      $callback = function($attribute) { return ($attribute instanceof MA\AttributeInterface) ? $attribute->getName() : $attribute; };

      foreach ($args as &$arg) {
        $arg = array_map($callback, $arg);
        $arg['_separator_'] = ', ';
      }//end foreach

      return $this->query(self::ACTION_ATTRIBUTE_DELETE, $args)->getResult() === 'Attributes deleted successfully';
    }

    return FALSE;
  }

  /**
   * Get users details.
   *
   * @param $emails
   *   A list of email addresses.
   *
   * @return mixed
   *   An array of user objects on success or FALSE.
   *
   * @see http://ressources.mailin.fr/subscriber-details/?lang=en
   */
  public function getUsers(array $emails) {
    $emails['_separator_'] = ',';
    $users = array();

    if ($data = $this->query(self::ACTION_USER_DETAILS, array('email' => $emails))->getDataOnSuccess()) {
      $rc = new \ReflectionClass('Mailin\\User');
      $rm = new \ReflectionMethod('Mailin\\User', '__construct');

      foreach ($data as $user) {
        $user = reset($user);
        $args = array();

        // Build the attributes array.
        $user['attributes'] = array();

        foreach (MA\Attribute::getAllTypes('user') as $class => $attributeType) {
          if (!empty($user[$attributeType])) {
            foreach ($user[$attributeType] as $name => $value) {
              $user['attributes'][$attributeType][] = new $class(array('name' => $name), $value);
            }//end foreach
          }
        }//end foreach

        foreach ($rm->getParameters() as $parameter) {
          $name = $parameter->getName();

          if (isset($user[$name])) {
            $args[] = $user[$name];
          }
          elseif ($parameter->isOptional()) {
            $args[] = $parameter->getDefaultValue();
          }
          // We should never go there. Doing so means that the User constructor
          // needs to be modified to have a default value for the missing parameter.
          else {
            continue 2;
          }
        }

        $users[] = $rc->newInstanceArgs($args);
      }//end foreach
    }

    return $users;
  }

  /**
   * Get a specific user.
   *
   * @param $email
   *   The email address.
   *
   * @return mixed
   *   A user object on success or FALSE.
   *
   * @see API::getUsers()
   */
  public function getUser($email) {
    return ($result = $this->getUsers(array($email))) ? reset($result) : FALSE;
  }

  /**
   * Get the blacklisted status of the given users.
   *
   * @param $emails
   *   An array of e-mail addresses that identify the users.
   *
   * @return mixed
   *   An array whose keys are users e-mail addresses and whose values are
   *   their status (0 for an active user, 1 for a blocked user and -1 if
   *   there is no information about this user). If an error occurs, FALSE
   *   is returned.
   */
  public function getUserStatus(array $emails = array()) {
    $emails['_separator_'] = ',';

    if ($users = $this->query(self::ACTION_USER_STATUS, array('email' => $emails))->getResult()) {
      unset($emails['_separator_']);
      return $users + array_fill_keys($emails, -1);
    }

    return FALSE;
  }

  /**
   * Remove a user from the blacklist.
   *
   * @param $email
   *   The user e-mail address.
   *
   * @return boolean
   *   TRUE on success, otherwise FALSE.
   *
   * @see http://ressources.mailin.fr/utilisateur-unblacklist/?lang=en
   */
  public function unblockUser($email) {
    return $this->query(self::ACTION_USER_UNBLOCK, array('email' => $email))->getResult() === 'OK';
  }

  /**
   * Add a user to the blacklist.
   *
   * @param $email
   *   The user e-mail address.
   *
   * @return boolean
   *   TRUE on success, otherwise FALSE.
   *
   * @see http://ressources.mailin.fr/utilisateur-liste-noire/?lang=en
   */
  public function blockUser($email) {
    return $this->query(self::ACTION_USER_BLOCK, array('email' => $email))->getResult() === 'OK';
  }

  /**
   * Get a list of users who match the filter criteria.
   *
   * @param $filter
   *   An array of filter criterium.
   * @param $notifyUrl
   *   A callback URL that is called when the export file is ready.
   *
   * @see http://ressources.mailin.fr/exporter-des-utilisateurs/?lang=en
   */
  //public function exportUsers(array $filter, $notifyUrl = NULL) {
    // Not implemented yet.
  //}

  /**
   * Add or update a user.
   *
   * @param $email
   *   The user e-mail address.
   * @param $lists
   *   An array of the list IDs the user should be subscribed to. At least one list
   *   is required.
   * @param $attributes
   *   An array of attributes to store into Mailin server, as part of the
   *   user defined fields.
   * @param $blacklisted
   *   Flag indicating whether the user is blacklisted or not.
   *
   * @see http://ressources.mailin.fr/ajout-dun-utilisateur/?lang=en
   */
  public function saveUser($email, array $lists, array $attributes = array(), $blacklisted = FALSE) {
    $lists = array_filter($lists);

    if (!$lists) {
      return FALSE;
    }

    $query = array(
      'email' => $email,
      'blacklisted' => (int) $blacklisted,
      'listid' => $lists,
    );

    if ($attributes) {
      $query['attributes_name'] = implode('|', array_keys($attributes));

      // @todo how to escape the pipe character?
      $query['attributes_value'] = implode('|', $attributes);
    }//end if

    $result = $this->query(self::ACTION_USER_SAVE, $query)->getResult();
    return (is_array($result) && isset($result['id'])) ? $result['id'] : FALSE;
  }

  /**
   * Add several users at once.
   *
   * @param $users
   *   An array of users. Each user array should at least contain an "email" key
   *   containing his e-mail address.
   * @param $lists
   *   An array of the list IDs the user should be subscribed to.
   *
   * @see http://ressources.mailin.fr/ajout-de-plusieurs-utilisateurs/?lang=en
   */
  public function saveUsers(array $users, array $lists = array()) {
    return $this->query(self::ACTION_USER_ADD_MULTIPLE, array('attributes' => json_encode($users), 'listid' => $lists))->getResult() === 'OK';
  }

}
