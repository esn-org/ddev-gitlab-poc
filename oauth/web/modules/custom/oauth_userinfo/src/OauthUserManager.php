<?php

namespace Drupal\oauth_userinfo;

use Drupal\user\UserInterface;

/**
 * Class that provides a service for manage the user data for the API endpoints.
 */
class OauthUserManager implements OauthUserManagerInterface {

  /**
   * {@inheritdoc}
   */
  public function generatePersonalInfo(UserInterface $user): array {
    $data = [];

    /** @var \Drupal\file\FileInterface $user_picture */
    $user_picture = $user->user_picture->entity;
    // We have an user, so we can proceed.
    $data = [
      'sub' => $user->uuid(),
      'name' => 'Name',
      'email' => $user->getEmail(),
      'email_verified' => TRUE,
      'picture' => '',
      'nickname' => $user->label(),
      'preferred_username' => $user->label(),
      'gender' => '',
      'birthdate' => '0000',
      'address' => [],
    ];

    // And return the data.
    return $data;
  }

  /**
   * {@inheritdoc}
   */
  public function generateUserGroupRoles(UserInterface $user): array {

    $data = [
      'full' => [],
      'simplified' => [],
    ];

    return $data;
  }

}
