<?php

namespace Drupal\oauth_userinfo;

use Drupal\user\UserInterface;

/**
 * Interface for the class OauthUserManager.
 */
interface OauthUserManagerInterface {

  /**
   * Creates an array with user's personal information.
   *
   * @param \Drupal\user\UserInterface $user
   *   The user to get its personal information.
   *
   * @return array<mixed>
   *   The array with personal information of the user.
   */
  public function generatePersonalInfo(UserInterface $user): array;

  /**
   * Fetches all the memberships of the user and compiles all its group roles.
   *
   * @param \Drupal\user\UserInterface $user
   *   The user to get its membership and group roles.
   *
   * @return array<mixed>
   *   An indexated array containing both detailed and simplified group roles.
   *
   *   The first index ('full') contains each group membership and roles; while
   *   the other index ('simplified') just contains the roles without any other
   *   detail.
   */
  public function generateUserGroupRoles(UserInterface $user): array;

}
