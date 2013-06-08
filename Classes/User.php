<?php

/**
 * @file
 * Define a Mailin user.
 */

namespace Mailin;

use Mailin\Attribute\Filter;

/**
 * The Mailin User class.
 */
class User {

  /**
   * The User ID.
   *
   * @var int
   */
  protected $id = NULL;

  /**
   * The Email address.
   *
   * @var string
   */
  protected $email;

  /**
   * The email domain.
   *
   * @var string
   */
  protected $email_tag;

  /**
   * Whether the user is blacklisted or not.
   *
   * @var boolean
   */
  protected $blacklisted;

  /**
   * The user registration date.
   *
   * @var \DateTime
   */
  protected $created;

  /**
   * The list IDs where the user is registered.
   *
   * @var array
   */
  protected $lists;

  /**
   * The user attributes.
   *
   * @var Filter
   */
  protected $attributes;

  /**
   * Class constructor.
   */
  public function __construct($id = NULL, $email = '', $blacklisted = FALSE, $entered = NULL, array $listid = array(), array $attributes = array()) {
    $this->id = $id;
    $this->email = $email;
    $this->blacklisted = (bool) $blacklisted;
    $this->created = $entered ? new \DateTime($entered) : NULL;
    $this->attributes = new Filter($attributes);
    $this->lists = array();

    foreach ($listid as $list) {
      $match = array();

      if (preg_match('/^(\d+):(.+)$/i', $list, $match)) {
        $this->lists[$match[1]] = trim($match[2]);
      }
    }//end foreach
  }

  /**
   * Get the user ID.
   */
  public function getId() {
    return $this->id;
  }

  /**
   * Get the user email address.
   */
  public function getEmail() {
    return $this->email;
  }

  /**
   * Whether the user is blacklisted or not.
   */
  public function isBlacklisted() {
    return $this->blacklisted;
  }

  /**
   * Get the user created date.
   *
   * @param $format
   *   If specified, the returned date is a string in the given format. Otherwise
   *   the \DateTime object is returned.
   *
   * @return mixed
   *   A string or a \DateTime okject.
   */
  public function getCreated($format = NULL) {
    return isset($format) ? $this->created->format($format) : $this->created;
  }

  /**
   * Get the user list IDs.
   */
  public function getLists() {
    return $this->lists;
  }

  /**
   * Get all user attributes.
   */
  public function getAttributes() {
    return $this->attributes->setFilters();
  }

  /**
   * Get a user attribute.
   */
  public function getAttribute($type, $name) {
    $filters = array(
      'type' => array(
        'value' => $type,
        'arguments' => array('user'),
      ),
      'name' => $name,
    );
    return $this->attributes->setFilters($filters)->getOne();
  }

  /**
   * Check whether the current user is registered to the given list ID.
   *
   * @param $listId
   *   The list ID.
   *
   * @return boolean
   */
  public function isRegistered($listId) {
    return array_key_exists($listId, $this->lists);
  }

}
