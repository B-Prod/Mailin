<?php

/**
 * @file
 * Mailin class that handles the API calls to the mailin server.
 */

namespace Mailin;

use Mailin\Request\MailinRequestCurl;
use Mailin\Response\MailinResponse;
use Mailin\MailinLog;

/**
 * Mailin API class.
 */
class MailinAPI {

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
   * Action that retrieves the folder list and their lists.
   */
  const ACTION_FOLDER_GET = 'DISPLAY-FOLDERS-LISTS';

  /**
   * Action that creates a new mailing list.
   */
  const ACTION_LIST_ADD = 'NEWLIST';

  /**
   * Action that creates a set of attributes.
   */
  const ACTION_ATTRIBUTE_ADD = 'ATTRIBUTES_CREATION';

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
   * @var MailinRequestInterface
   */
  protected $requestHandler;

  /**
   * Class constructor.
   *
   * @param mixed $apiKey
   * @param MailinRequestInterface $requestHandler
   */
  public function __construct($apiKey, MailinRequestInterface $requestHandler = NULL) {
    $this->apiKey = $apiKey;
    $this->requestHandler = isset($requestHandler) ? $requestHandler : new MailinRequestCurl();
  }

  /**
   * Set the object instance that handles requests against Mailin server.
   *
   * @param $requesthandler
   *   The new request handler object instance.
   *
   * @return MailinAPI
   *   The current instance.
   */
  public function setRequesthandler(MailinRequestInterface $requesthandler) {
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
   * @return MailinResponseInterface
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

    MailinLog::startApiCall($query);
    $response = $this->requestHandler->request($query);
    MailinLog::endApiCall($response);

    return $response;
  }

  /**
   * Create a new folder on the Mailin server.
   *
   * @param $name
   *   The folder name.
   *
   * @return mixed
   *   The newly created folder ID or FALSE on failure.
   */
  public function addFolder($name) {
    $response = $this->query(self::ACTION_FOLDER_ADD, array('foldername' => $name));
    return $response->isSuccessful() && !empty($response['folder_id']) ? $response['folder_id'] : FALSE;
  }

  /**
   * Create a new list on the Mailin server.
   *
   * @param $name
   *   The list name.
   * @param $parentId
   *   The parent list ID.
   *
   * @return mixed
   *   The newly created list ID or FALSE on failure.
   */
  public function addList($name, $parentId = 1) {
    return $this->query(self::ACTION_LIST_ADD, array('listname' => $name, 'list_parent' => $parentId))->getResult();
  }

  /**
   * Delete a list from the Mailin server.
   *
   * @param $id
   *   The list ID.
   *
   * @return boolean
   *   TRUE on success, otherwise FALSE.
   */
  public function deleteList($id) {
    $response = $this->query(self::ACTION_FOLDER_DELETE, array('list_id' => $id));
    return $response->getResult() === 'success';
  }

  /**
   * Delete a folder from the Mailin server.
   *
   * This is only a convenient wrapper against the deleteList() method,
   * because the API call is exactly the same.
   *
   * @param $id
   *   The folder ID.
   *
   * @return boolean
   *   TRUE on success, otherwise FALSE.
   */
  public function deleteFolder($id) {
    return $this->deleteList($id);
  }

  /**
   * Get the existing folders with their lists.
   *
   * @param $ids
   *   An array of folder and/or list IDs for which retrieve information.
   *
   * @return mixed
   *   An array of the existing folders and lists on success or FALSE.
   */
  public function getFolders(array $ids = array()) {
    return $this->query(self::ACTION_FOLDER_GET, array('ids' => $ids))->getData();
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
   * Get the existing mailing lists.
   *
   * @param $ids
   *   An array of list IDs for which retrieve information.
   *
   * @return mixed
   *   An array of the existing lists on success or FALSE.
   */
  public function getLists(array $ids = array()) {
    $lists = array();

    if ($result = $this->getFolders($ids)) {
      foreach ($result as $fid => $folder) {
        if (!empty($folder['lists'])) {
          $lists = array_merge($lists, $folder['lists']);
        }
      }//end foreach
    }

    return $lists;
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
  public function getUsersStatus(array $emails = array()) {
    $emails['_separator_'] = ',';
    $response = $this->request(self::ACTION_USER_STATUS, array('email' => $emails));

    if ($response->success() && isset($response['result'])) {
      return $response['result'] + array_fill_keys($emails, -1);
    }

    return FALSE;
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
  public function exportUsers(array $filter, $notifyUrl = NULL) {
    // @todo how are built the filters?

    $response = $this->request(self::ACTION_USER_EXPORT, array('filter' => $filter, 'notify_url' => $notifyUrl));

    // @todo process response.
  }

  /**
   * Add or update a user.
   *
   * @param $email
   *   The user e-mail address.
   * @param $attributes
   *   An array of attributes to store into Mailin server, as part of the
   *   user defined fields.
   * @param $lists
   *   An array of the list IDs the user should be subscribed to.
   * @param $blacklisted
   *   Flag indicating whether the user is blacklisted or not.
   *
   * @see http://ressources.mailin.fr/ajout-dun-utilisateur/?lang=en
   */
  public function saveUser($email, array $attributes = array(), array $lists = array(), $blacklisted = FALSE) {
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

    $response = $this->query(self::ACTION_USER_SAVE, $query)->getResult();
    return ($response->success() && isset($response['id'])) ? $response['id'] : FALSE;
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
    return $this->query(self::ACTION_USER_ADD_MULTIPLE, array('attributes' => json_encode($users), 'listid' => $lists))->getResult();
  }

  public function getCampaignRecipients($campaignId, $recipientType = self::RECIPIENT_TYPE_ALL) {
    $data = array(
      'id' => $campaignId,
      'webaction' => 'ACTION_CAMPAIGN_USER_STATS',
      'type' => $recipientType,
    );

    $this->request($data);
  }

}
